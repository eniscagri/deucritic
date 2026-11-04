<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$topic_id = $_GET['id'] ?? null;
$csrf = $_GET['csrf_token'] ?? '';

if (!$topic_id || $csrf !== $_SESSION['csrf_token']) {
    die('Geçersiz istek!');
}

// Topic kontrolü
$stmt = $pdo->prepare("SELECT created_by FROM topics WHERE id = ?");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topic) {
    die('Gönderi bulunamadı.');
}

if ($topic['created_by'] != $user_id) {
    die('Bu gönderiyi silme yetkin yok.');
}

try {
    // Artık sadece topic'i silmek yeterli
    $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ?");
    $stmt->execute([$topic_id]);

    header("Location: /profile.php?msg=deleted");
    exit;

} catch (PDOException $e) {
    die("Bir hata oluştu: " . $e->getMessage());
}
