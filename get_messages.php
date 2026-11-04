<?php
include 'includes/config.php';
$me = $_SESSION['user_id'];
$other = $_GET['user'];
$last = $_GET['last_id'];

$stmt = $pdo->prepare("SELECT * FROM messages WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY id ASC");
$stmt->execute([$me, $other, $other, $me, $last]);
echo json_encode($stmt->fetchAll());