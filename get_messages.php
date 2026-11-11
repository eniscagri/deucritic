<?php
include 'includes/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$me = (int) $_SESSION['user_id'];
$other = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT);
$last = filter_input(INPUT_GET, 'last_id', FILTER_VALIDATE_INT);
if (!$other || $other === $me || $last === false || ($last !== null && $last < 0)) {
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

// Messages are only returned for a user pair that shares a conversation.
$conversation = $pdo->prepare('SELECT COUNT(*) FROM conversation_users cu1 JOIN conversation_users cu2 ON cu1.conversation_id = cu2.conversation_id WHERE cu1.user_id = ? AND cu2.user_id = ?');
$conversation->execute([$me, $other]);
if ((int) $conversation->fetchColumn() === 0) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, conversation_id, sender_id, receiver_id, message, created_at, viewed_on FROM messages WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY id ASC LIMIT 100");
$stmt->execute([$me, $other, $other, $me, $last ?? 0]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
