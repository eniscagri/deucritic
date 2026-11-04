<?php
require_once 'admin_init.php';
require_admin();

$stmt = $pdo->prepare("SELECT l.*, u.username FROM user_ip_logs l LEFT JOIN users u ON l.user_id = u.id WHERE l.is_vpn = 1 ORDER BY l.date DESC LIMIT 200");
$stmt->execute();
$vpn_logs = $stmt->fetchAll();

$stmt2 = $pdo->prepare("
    SELECT user_id, COUNT(DISTINCT city) AS city_count
    FROM user_ip_logs
    WHERE date >= (NOW() - INTERVAL 1 DAY)
    GROUP BY user_id
    HAVING city_count >= 3
    ORDER BY city_count DESC
");
$stmt2->execute();
$multi_city = $stmt2->fetchAll();

// 3) devices marked suspicious
$deviceStmt = $pdo->query("SELECT d.*, u.username FROM login_devices d LEFT JOIN users u ON d.user_id = u.id WHERE d.is_suspicious = 1 ORDER BY d.last_seen DESC LIMIT 200");
$devices = $deviceStmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><script src="https://cdn.tailwindcss.com"></script><title>Suspicious</title></head>
<body class="bg-slate-50">
<header class="bg-white p-4 flex justify-between">
    <div class="font-bold">Şüpheli Girişler</div>
    <div><a href="admin_ip_logs.php" class="px-3 py-1 bg-slate-100 rounded">Back</a></div>
</header>
<main class="max-w-6xl mx-auto p-6">
    <div class="bg-white p-4 rounded shadow mb-4">
        <h3 class="font-semibold">VPN / Proxy Detections</h3>
        <?php foreach($vpn_logs as $v): ?>
            <div class="border p-2 mt-2">
                <div><strong><?=htmlspecialchars($v['username'] ?? 'User #'.$v['user_id'])?></strong> — <?=htmlspecialchars($v['ip'])?> — <?=htmlspecialchars($v['date'])?></div>
                <div class="text-xs"><?=htmlspecialchars($v['country'].' / '.$v['city'].' — '.$v['isp'])?></div>
            </div>
        <?php endforeach;?>
    </div>

    <div class="bg-white p-4 rounded shadow mb-4">
        <h3 class="font-semibold">Multiple Cities in 24h</h3>
        <?php foreach($multi_city as $m): ?>
            <div class="border p-2 mt-2">
                <div>User #<?=htmlspecialchars($m['user_id'])?> — Cities: <?=htmlspecialchars($m['city_count'])?></div>
                <div><a href="admin_user.php?id=<?=urlencode($m['user_id'])?>" class="text-sky-600">View user</a></div>
            </div>
        <?php endforeach;?>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold">Suspicious Devices</h3>
        <?php foreach($devices as $d): ?>
            <div class="border p-2 mt-2">
                <div><strong><?=htmlspecialchars($d['username'] ?? 'User #'.$d['user_id'])?></strong> — <?=htmlspecialchars($d['ip'])?> — Last seen <?=htmlspecialchars($d['last_seen'])?></div>
                <div class="text-xs"><?=htmlspecialchars(substr($d['user_agent'] ?? '', 0, 200))?></div>
            </div>
        <?php endforeach;?>
    </div>
</main>
</body>
</html>
