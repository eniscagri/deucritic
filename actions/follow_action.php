<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$follower_id = $_SESSION['user_id'];
$followed_id = (int)($_POST['user_id'] ?? 0);

if ($followed_id <= 0 || $followed_id == $follower_id) {
    header('Location: ../profile.php?id=' . $followed_id);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND followed_id = ?");
$stmt->execute([$follower_id, $followed_id]);

if ($stmt->rowCount() > 0) {
    $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$follower_id, $followed_id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
    $stmt->execute([$follower_id, $followed_id]);
}

header('Location: ../profile.php?id=' . $followed_id);
exit;
