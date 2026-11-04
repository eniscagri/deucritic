<?php

require 'config.php'; 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!csrf_ok($_POST['csrf'] ?? '')) {
    die('CSRF token mismatch.');
}

$action = $_POST['action'] ?? '';
$user_id = (int)($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    die('Invalid user ID.');
}

switch($action){
    case 'ban':
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        $msg = "User #$user_id banned.";
        break;

    case 'unban':
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        $msg = "User #$user_id unbanned.";
        break;

    case 'warn':
        $reason = trim($_POST['reason'] ?? '');
        if (!$reason) die('Reason required.');

        // Moderation log
        $stmt = $pdo->prepare("INSERT INTO moderation_logs (user_id, reason) VALUES (?, ?)");
        $stmt->execute([$user_id, $reason]);

        // Warnings count
        $stmt = $pdo->prepare("UPDATE users SET warnings = COALESCE(warnings,0)+1 WHERE id = ?");
        $stmt->execute([$user_id]);

        $msg = "User #$user_id warned.";
        break;

    default:
        die('Unknown action.');
}

// Redirect back to admin panel
header('Location: admin_users.php?msg='.urlencode($msg));
exit;
