<?php
require_once '../includes/config.php';

// Sadece giriş yapmış kullanıcılar başlık oluşturabilir
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');

if ($title === '') {
    // Başlık boşsa anasayfaya döner
    header('Location: ../index.php');
    exit;
}

// Başlık ekle
$stmt = $pdo->prepare("INSERT INTO topics (title, created_by) VALUES (?, ?)");
$stmt->execute([$title, $_SESSION['user_id']]);

// Yeni başlık sayfasına yönlendir
$topic_id = $pdo->lastInsertId();
header("Location: ../topic.php?id=$topic_id");
exit;
