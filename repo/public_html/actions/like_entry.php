<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$entry_id = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;

if ($entry_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz entry.']);
    exit;
}

// Entry var mı kontrolü
$stmt = $pdo->prepare("SELECT id FROM entries WHERE id = ?");
$stmt->execute([$entry_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Entry bulunamadı.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM entry_likes WHERE entry_id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);
$like_row = $stmt->fetch();

if ($like_row) {
    $stmt = $pdo->prepare("DELETE FROM entry_likes WHERE entry_id = ? AND user_id = ?");
    $stmt->execute([$entry_id, $user_id]);
    $liked = false;
} else {
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO entry_likes (entry_id, user_id, liked_at, created_t) VALUES (?, ?, ?, ?)");
    $stmt->execute([$entry_id, $user_id, $now, $now]);
    $liked = true;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM entry_likes WHERE entry_id = ?");
$stmt->execute([$entry_id]);
$like_count = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'like_count' => $like_count
]);
