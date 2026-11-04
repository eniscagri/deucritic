<?php
require_once 'includes/config.php';
$user_id = $_GET['user_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.profile_photo
    FROM followers f
    JOIN users u ON f.followed_id = u.id
    WHERE f.follower_id = ?
");
$stmt->execute([$user_id]);
$following = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($following);
