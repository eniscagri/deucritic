<?php
require 'config.php'; 

header('Content-Type: application/json');

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user_badges WHERE user_id = ?");
$stmt->execute([$user_id]);
$badges = $stmt->fetchAll();

echo json_encode($badges);
