<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$story_id = intval($data['story_id'] ?? 0);
if (!$story_id) { echo json_encode(['success'=>false]); exit; }

$viewer_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("INSERT INTO story_views (story_id, viewer_id) VALUES (?, ?)");
$stmt->execute([$story_id, $viewer_id]);

echo json_encode(['success'=>true]);
exit;
