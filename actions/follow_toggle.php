<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz güvenlik tokeni (CSRF).']);
    exit;
}

$follower_id = $_SESSION['user_id'];
$followed_id = $data['target_id'] ?? 0;

if (!$followed_id || $follower_id == $followed_id) {
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
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası.']);
}