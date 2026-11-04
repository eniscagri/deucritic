<?php
require 'includes/config.php'; 

$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT id, username, last_ip, last_login
    FROM users
    WHERE DATE(last_login) = ?
");
$stmt->execute([$today]);
$users = $stmt->fetchAll();

$logDir = __DIR__ . "/logs";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$logFile = $logDir . "/iplog_$today.txt";

$logContent = "=== $today Giriş Yapan Kullanıcı IP Log ===\n\n";

if (empty($users)) {
    $logContent .= "Bugün giriş yapan kullanıcı yok.\n";
} else {
    foreach ($users as $u) {
        $logContent .= "User ID: {$u['id']}\n";
        $logContent .= "Username: {$u['username']}\n";
        $logContent .= "IP: {$u['last_ip']}\n";
        $logContent .= "Login Time: {$u['last_login']}\n";
        $logContent .= "---------------------------------------\n";
    }
}

file_put_contents($logFile, $logContent);

echo "IP log oluşturuldu: $logFile\n";
