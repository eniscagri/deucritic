<?php

require_once 'includes/config.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmanız gerekiyor.']);
    exit;
}

$notif_id = $_POST['notif_id'] ?? null;

if ($notif_id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND (user_id = :uid OR is_global = 1)");
    $stmt->execute([
        'id' => $notif_id,
        'uid' => $user_id
    ]);

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bildirim ID gönderilmedi.']);
}
?>
