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
$ip = trim($_POST['ip'] ?? '');
$reason = trim($_POST['reason'] ?? 'Yönetici tarafından engellendi');

if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
    http_response_code(400);
    exit('Geçerli bir IP adresi gerekli.');
}
if (!admin_table_exists($pdo, 'banned_ips')) {
    http_response_code(503);
    exit('IP engelleme tablosu bu veritabanında bulunmuyor.');
}

$stmt = $pdo->prepare("INSERT INTO banned_ips (ip, reason) VALUES (:ip, :reason) ON DUPLICATE KEY UPDATE reason = :reason, banned_at = NOW()");
$stmt->execute([':ip'=>$ip, ':reason'=>$reason]);

header('Location: admin_ip_logs.php?msg='.urlencode("IP $ip banned"));
exit;
