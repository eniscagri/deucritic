<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$entry_id = (int)($_POST['entry_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($entry_id <= 0 || $reason === '') {
    // Eksik veri varsa anasayfaya dön
    header('Location: ../index.php');
    exit;
}


$stmt = $pdo->prepare("INSERT INTO reports (entry_id, reported_by, reason) VALUES (?, ?, ?)");
$stmt->execute([$entry_id, $_SESSION['user_id'], $reason]);

header('Location: ../index.php');
exit;
