<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$entry_id = $data['entry_id'] ?? null;

if (!$entry_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM entry_likes WHERE entry_id = ? AND user_id = ?");
    $stmt->execute([$entry_id, $user_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare("DELETE FROM entry_likes WHERE id = ?")->execute([$existing['id']]);
        $action = 'unliked';
    } else {
        $pdo->prepare("INSERT INTO entry_likes (entry_id, user_id) VALUES (?, ?)")->execute([$entry_id, $user_id]);
        $action = 'liked';
    }

    $new_count = $pdo->prepare("SELECT COUNT(*) FROM entry_likes WHERE entry_id = ?");
    $new_count->execute([$entry_id]);
    
    echo json_encode(['status' => 'success', 'action' => $action, 'new_count' => $new_count->fetchColumn()]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
}
?>