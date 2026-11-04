<?php
require_once 'admin_init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
if (!csrf_ok($_POST['csrf'] ?? '')) {
    die('CSRF token mismatch.');
}
$ip = trim($_POST['ip'] ?? '');
$reason = trim($_POST['reason'] ?? 'Banned by admin');

if (!$ip) die('IP required');

$stmt = $pdo->prepare("INSERT INTO banned_ips (ip, reason) VALUES (:ip, :reason) ON DUPLICATE KEY UPDATE reason = :reason, banned_at = NOW()");
$stmt->execute([':ip'=>$ip, ':reason'=>$reason]);

header('Location: admin_ip_logs.php?msg='.urlencode("IP $ip banned"));
exit;
