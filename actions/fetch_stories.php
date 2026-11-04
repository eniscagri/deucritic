<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id || empty($_SESSION['user_id'])) {
  http_response_code(empty($_SESSION['user_id']) ? 401 : 400);
  echo json_encode(['success' => false, 'stories' => []]);
  exit;
}

$viewer_id = (int) $_SESSION['user_id'];
$access = $pdo->prepare('SELECT 1 FROM users WHERE id = ? AND id = ? UNION SELECT 1 FROM followers WHERE followed_id = ? AND follower_id = ? LIMIT 1');
$access->execute([$user_id, $viewer_id, $user_id, $viewer_id]);
if (!$access->fetchColumn()) {
  http_response_code(403);
  echo json_encode(['success' => false, 'stories' => []]);
  exit;
}

$stmt = $pdo->prepare("SELECT s.id, s.user_id, s.media_path, s.media_type, s.text_content, s.created_at, u.username, u.profile_photo, (SELECT COUNT(*) FROM story_views sv WHERE sv.story_id = s.id) AS view_count FROM stories s JOIN users u ON s.user_id=u.id WHERE s.user_id = ? AND s.expires_at > NOW() ORDER BY s.created_at ASC, s.id ASC LIMIT 50");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as &$r) {
  // tam yol haline getir
  if ($r['media_path']) $r['media_path'] = e($r['media_path']);
  $r['profile_photo'] = !empty($r['profile_photo']) ? basename($r['profile_photo']) : null;
  if ((int) $r['user_id'] === (int) $_SESSION['user_id']) {
    $viewers = $pdo->prepare('SELECT u.username, u.profile_photo FROM story_views sv JOIN users u ON u.id = sv.viewer_id WHERE sv.story_id = ? ORDER BY u.username LIMIT 100');
    $viewers->execute([$r['id']]);
    $r['viewers'] = $viewers->fetchAll(PDO::FETCH_ASSOC);
    foreach ($r['viewers'] as &$viewer) {
      $viewer['profile_photo'] = !empty($viewer['profile_photo']) ? basename($viewer['profile_photo']) : null;
    }
    unset($viewer);
  } else {
    $r['viewers'] = [];
  }
}
echo json_encode(['success'=>true,'stories'=>$rows]);
exit;
