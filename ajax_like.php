<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$topic_id = $data['topic_id'] ?? null;

if (!$topic_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM topic_likes WHERE topic_id = ? AND user_id = ?");
    $stmt->execute([$topic_id, $user_id]);
    $existing_like = $stmt->fetch();

    if ($existing_like) {
        $del = $pdo->prepare("DELETE FROM topic_likes WHERE id = ?");
        $del->execute([$existing_like['id']]);
        $action = 'unliked';
    } else {
        $ins = $pdo->prepare("INSERT INTO topic_likes (topic_id, user_id) VALUES (?, ?)");
        $ins->execute([$topic_id, $user_id]);
        $action = 'liked';
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM topic_likes WHERE topic_id = ?");
    $countStmt->execute([$topic_id]);
    $new_count = $countStmt->fetchColumn();

    echo json_encode(['status' => 'success', 'action' => $action, 'new_count' => $new_count]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
}
?>