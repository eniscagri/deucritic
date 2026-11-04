<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Giriş yapmalısınız']);
    exit;
}

$user_id = $_SESSION['user_id'];
$entry_id = (int)($_POST['entry_id'] ?? 0);
$reaction = $_POST['reaction'] ?? 'like';

if ($entry_id <= 0) {
    echo json_encode(['error' => 'Geçersiz yorum']);
    exit;
}

// Önce var mı kontrol et
$stmt = $pdo->prepare("SELECT id FROM entry_likes WHERE entry_id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);
if ($stmt->rowCount() > 0) {
    // Güncelle
    $stmt = $pdo->prepare("UPDATE entry_likes SET reaction = ? WHERE entry_id = ? AND user_id = ?");
    $stmt->execute([$reaction, $entry_id, $user_id]);
} else {
    // Ekle
    $stmt = $pdo->prepare("INSERT INTO entry_likes (entry_id, user_id, reaction) VALUES (?, ?, ?)");
    $stmt->execute([$entry_id, $user_id, $reaction]);
}

echo json_encode(['success' => true]);
