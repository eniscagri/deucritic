<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

$data = json_decode(file_get_contents('php://input'), true);
$story_id = filter_var($data['story_id'] ?? null, FILTER_VALIDATE_INT);
$viewer_id = (int) ($_SESSION['user_id'] ?? 0);
$csrf = (string) ($data['csrf_token'] ?? '');
if (!$viewer_id || !$story_id || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
  http_response_code($viewer_id ? 403 : 401);
  echo json_encode(['success' => false]);
  exit;
}

$story = $pdo->prepare('SELECT user_id FROM stories WHERE id = ? AND expires_at > NOW()');
$story->execute([$story_id]);
$owner_id = $story->fetchColumn();
if (!$owner_id || (int) $owner_id === $viewer_id) {
  http_response_code(404);
  echo json_encode(['success' => false]);
  exit;
}

$existing = $pdo->prepare('SELECT 1 FROM story_views WHERE story_id = ? AND viewer_id = ? LIMIT 1');
$existing->execute([$story_id, $viewer_id]);
if (!$existing->fetchColumn()) {
  $stmt = $pdo->prepare('INSERT INTO story_views (story_id, viewer_id) VALUES (?, ?)');
  $stmt->execute([$story_id, $viewer_id]);
}

echo json_encode(['success' => true]);
exit;
