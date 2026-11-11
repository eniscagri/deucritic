<?php
require_once __DIR__ . '/admin_init.php';
require_admin();
header('Content-Type: application/json; charset=utf-8');

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz kullanıcı.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!admin_table_exists($pdo, 'user_badges')) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user_badges WHERE user_id = ?");
$stmt->execute([$user_id]);
$badges = $stmt->fetchAll();

echo json_encode($badges);
