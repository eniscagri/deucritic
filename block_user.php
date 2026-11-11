<?php
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user_id'])) {
    http_response_code(empty($_SESSION['user_id']) ? 401 : 405);
    exit('Geçersiz istek.');
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    exit('İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.');
}

$blockerId = (int) $_SESSION['user_id'];
$blockedId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$blockedId || $blockedId === $blockerId || !in_array($action, ['block', 'unblock'], true)) {
    http_response_code(400);
    exit('Geçersiz kullanıcı veya işlem.');
}

$userExists = $pdo->prepare('SELECT 1 FROM users WHERE id = ?');
$userExists->execute([$blockedId]);
if (!$userExists->fetchColumn()) {
    http_response_code(404);
    exit('Kullanıcı bulunamadı.');
}

if ($action === 'block') {
    $stmt = $pdo->prepare('INSERT IGNORE INTO user_blocks (blocker_id, blocked_id) VALUES (?, ?)');
    $stmt->execute([$blockerId, $blockedId]);
} else {
    $stmt = $pdo->prepare('DELETE FROM user_blocks WHERE blocker_id = ? AND blocked_id = ?');
    $stmt->execute([$blockerId, $blockedId]);
}

header('Location: dm.php');
exit;
