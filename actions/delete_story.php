<?php
session_start();
include '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'msg' => ''];

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $response['msg'] = 'Lütfen giriş yapın.';
    exit(json_encode($response));
}

$story_id = filter_input(INPUT_POST, 'story_id', FILTER_VALIDATE_INT);
if (!$story_id) {
    $response['msg'] = 'Geçersiz hikaye ID.';
    exit(json_encode($response));
}

try {
    $stmt = $pdo->prepare("SELECT id FROM stories WHERE id = ? AND user_id = ?");
    $stmt->execute([$story_id, $user_id]);
    $story = $stmt->fetch();

    if (!$story) {
        $response['msg'] = 'Hikaye bulunamadı veya yetkiniz yok.';
        exit(json_encode($response));
    }

    $stmt = $pdo->prepare("DELETE FROM stories WHERE id = ? AND user_id = ?");
    $deleted = $stmt->execute([$story_id, $user_id]);

    if ($deleted) {
        $response['success'] = true;
        $response['msg'] = 'Hikaye başarıyla silindi.';
    } else {
        $response['msg'] = 'Hikaye silinirken bir hata oluştu.';
    }

} catch (PDOException $e) {
    $response['msg'] = 'Veritabanı hatası: ' . $e->getMessage();
}

echo json_encode($response);
