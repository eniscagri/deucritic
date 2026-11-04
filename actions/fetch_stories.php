<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id) { echo json_encode(['success'=>false,'stories'=>[]]); exit; }

$stmt = $pdo->prepare("SELECT s.*, u.username FROM stories s JOIN users u ON s.user_id=u.id WHERE s.user_id = ? AND s.expires_at > NOW() ORDER BY s.created_at ASC");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as &$r) {
  // tam yol haline getir
  if ($r['media_path']) {
    $r['media_path'] = e($r['media_path']);
  }
}
echo json_encode(['success'=>true,'stories'=>$rows]);
exit;
