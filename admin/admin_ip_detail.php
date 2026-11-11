<?php
require_once 'admin_init.php';
require_admin();

$ip = $_GET['ip'] ?? '';
if (!is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) { header('Location: admin_ip_logs.php'); exit; }
if (!admin_table_exists($pdo, 'user_ip_logs')) {
    http_response_code(404);
    exit('IP oturum kayıtları bu veritabanında bulunmuyor.');
}

$dateColumn = admin_column_exists($pdo, 'user_ip_logs', 'date') ? 'date' : (admin_column_exists($pdo, 'user_ip_logs', 'created_at') ? 'created_at' : (admin_column_exists($pdo, 'user_ip_logs', 'logged_at') ? 'logged_at' : 'id'));
$stmt = $pdo->prepare("SELECT l.*, l.`$dateColumn` AS date, u.username, u.email FROM user_ip_logs l LEFT JOIN users u ON l.user_id = u.id WHERE l.ip = :ip ORDER BY l.`$dateColumn` DESC LIMIT 200");
$stmt->execute([':ip'=>$ip]);
$logs = $stmt->fetchAll();
$info = $logs[0] ?? [];

$devices = [];
if (admin_table_exists($pdo, 'login_devices') && admin_column_exists($pdo, 'login_devices', 'ip')) {
    $deviceOrder = admin_column_exists($pdo, 'login_devices', 'last_seen') ? 'last_seen' : 'id';
    $devStmt = $pdo->prepare("SELECT * FROM login_devices WHERE ip = :ip ORDER BY `$deviceOrder` DESC");
    $devStmt->execute([':ip'=>$ip]);
    $devices = $devStmt->fetchAll();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>IP Detail <?=htmlspecialchars($ip)?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
<header class="bg-white p-4 shadow flex justify-between">
    <div class="font-bold">IP Details — <?=htmlspecialchars($ip)?></div>
    <div>
        <a href="admin_ip_logs.php" class="px-3 py-1 bg-slate-100 rounded">Back</a>
    </div>
</header>
<main class="max-w-6xl mx-auto p-6">
    <div class="bg-white p-4 rounded shadow mb-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="font-semibold">IP</h3>
                <div class="font-mono"><?=htmlspecialchars($ip)?></div>
                <div class="mt-2"><strong>Ülke:</strong> <?=htmlspecialchars($info['country'] ?? 'Kayıt yok')?></div>
                <div><strong>Şehir:</strong> <?=htmlspecialchars($info['city'] ?? 'Kayıt yok')?></div>
                <div><strong>Servis sağlayıcı:</strong> <?=htmlspecialchars($info['isp'] ?? 'Kayıt yok')?></div>
                <div><strong>VPN/Proxy işareti:</strong> <?=!empty($info['is_vpn']) ? '<span class="text-red-600">Evet</span>' : 'Hayır / bilinmiyor'?></div>
            </div>
            <div>
                <div class="rounded bg-slate-100 p-6 text-sm text-slate-500">Konum yalnızca giriş sırasında saklanan yaklaşık ülke/şehir bilgisiyle gösterilir. Canlı konum sorgusu yapılmaz.</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded shadow">
            <h4 class="font-semibold mb-3">Recent Logs (this IP)</h4>
            <div class="space-y-2 text-sm">
                <?php foreach($logs as $l): ?>
                    <div class="p-2 border rounded">
                        <div><strong><?=htmlspecialchars($l['username'] ?? 'Kullanıcı #'.$l['user_id'])?></strong> — <span class="text-xs text-slate-500"><?=htmlspecialchars($l['date'] ?? '')?></span></div>
                        <div class="text-xs"><?=htmlspecialchars(($l['country'] ?? '').' / '.($l['city'] ?? '').' — '.($l['isp'] ?? ''))?></div>
                        <div class="text-xs"><?=htmlspecialchars(substr($l['user_agent'] ?? '', 0, 200))?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h4 class="font-semibold mb-3">Devices seen on this IP</h4>
            <div class="space-y-2 text-sm">
                <?php foreach($devices as $d): ?>
                    <div class="p-2 border rounded">
                        <div><strong>Device ID:</strong> <?=htmlspecialchars($d['id'])?> — <span class="text-xs"><?=htmlspecialchars($d['last_seen'])?></span></div>
                        <div class="text-xs"><?=htmlspecialchars(substr($d['user_agent'] ?? '',0,200))?></div>
                        <div class="text-xs">Şüpheli işareti: <?=(!empty($d['is_suspicious']) ? 'Evet' : 'Hayır')?></div>
                    </div>
                <?php endforeach; ?>
                <?php if ($devices === []): ?><p class="text-slate-500">Bu IP için cihaz kaydı bulunamadı.</p><?php endif; ?>
            </div>
        </div>
    </div>
</main>

</body>
</html>
