<?php
session_start();
require_once '../includes/config.php';

// Sadece giriş yapmış kullanıcılar yorum yazabilir
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$topic_id = (int)($_POST['topic_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($topic_id <= 0 || $content === '') {
    // Eksik veya hatalı veri
    header("Location: ../topic.php?id=$topic_id");
    exit;
}

// Yorum ekle
$stmt = $pdo->prepare("INSERT INTO entries (topic_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$topic_id, $user_id, $content]);

// Yorum eklendikten sonra başlık sayfasına geri dön
header("Location: ../topic.php?id=$topic_id");
exit;
