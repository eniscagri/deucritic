<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$me = (int) $_SESSION['user_id'];
$other = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT);
$conversationId = filter_input(INPUT_GET, 'conversation_id', FILTER_VALIDATE_INT);
$last = filter_input(INPUT_GET, 'last_id', FILTER_VALIDATE_INT);
if (!$other || $other === $me || !$conversationId || $last === false || ($last !== null && $last < 0)) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

$blocked = $pdo->prepare('SELECT 1 FROM user_blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?) LIMIT 1');
$blocked->execute([$me, $other, $other, $me]);
if ($blocked->fetchColumn()) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

// Restrict message access to the exact conversation shared by this user pair.
$conversation = $pdo->prepare('SELECT COUNT(DISTINCT user_id) FROM conversation_users WHERE conversation_id = ? AND user_id IN (?, ?)');
$conversation->execute([$conversationId, $me, $other]);
if ((int) $conversation->fetchColumn() !== 2) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

// Polling an open chat also acknowledges incoming messages as read.
$markRead = $pdo->prepare('UPDATE messages SET viewed_on = 1 WHERE conversation_id = ? AND sender_id = ? AND receiver_id = ? AND viewed_on = 0');
$markRead->execute([$conversationId, $other, $me]);

$stmt = $pdo->prepare("SELECT id, conversation_id, sender_id, receiver_id, message, created_at, viewed_on FROM messages WHERE conversation_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY id ASC LIMIT 100");
$stmt->execute([$conversationId, $me, $other, $other, $me, $last ?? 0]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
