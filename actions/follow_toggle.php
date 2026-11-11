<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data) || !isset($data['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals((string) $_SESSION['csrf_token'], (string) $data['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Geçersiz güvenlik tokeni (CSRF).']);
    exit;
}

$follower_id = $_SESSION['user_id'];
$followed_id = filter_var($data['target_id'] ?? null, FILTER_VALIDATE_INT);

if (!$followed_id || $follower_id == $followed_id) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Geçersiz işlem.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id = ? AND followed_id = ? LIMIT 1");
    $stmt->execute([$follower_id, $followed_id]);
    $is_already_following = $stmt->fetchColumn() !== false;

    if ($is_already_following) {
        $stmt = $pdo->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$follower_id, $followed_id]);
        echo json_encode(['success' => true, 'is_following' => false]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$follower_id, $followed_id]);
        echo json_encode(['success' => true, 'is_following' => true]);
    }
} catch (PDOException $e) {
    error_log('Profile follow toggle failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası.']);
}