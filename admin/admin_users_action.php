<?php

require_once 'admin_init.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!admin_csrf_ok($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit('İstek doğrulanamadı.');
}

$action = $_POST['action'] ?? '';
$user_id = (int)($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    exit('Geçersiz kullanıcı.');
}

switch($action){
    case 'ban':
        if (!admin_column_exists($pdo, 'users', 'is_banned')) {
            http_response_code(503);
            exit('Hesap engelleme bu veritabanında desteklenmiyor.');
        }
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        $msg = "User #$user_id banned.";
        break;

    case 'unban':
        if (!admin_column_exists($pdo, 'users', 'is_banned')) {
            http_response_code(503);
            exit('Hesap engelleme bu veritabanında desteklenmiyor.');
        }
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        $msg = "User #$user_id unbanned.";
        break;

    case 'warn':
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '' || !admin_table_exists($pdo, 'moderation_logs') || !admin_column_exists($pdo, 'users', 'warnings')) {
            http_response_code(400);
            exit('Uyarı kaydı için geçerli bir neden ve moderasyon tablosu gerekir.');
        }

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
header('Location: users.php?msg='.urlencode($msg));
exit;
