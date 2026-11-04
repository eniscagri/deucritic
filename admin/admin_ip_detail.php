<?php
require_once 'admin_init.php';
require_admin();

$ip = $_GET['ip'] ?? '';
if (!$ip) { header('Location: admin_ip_logs.php'); exit; }

$info = ip_lookup_cached($ip);

// fetch all logs for this IP
$stmt = $pdo->prepare("SELECT l.*, u.username, u.email FROM user_ip_logs l LEFT JOIN users u ON l.user_id = u.id WHERE l.ip = :ip ORDER BY l.date DESC LIMIT 200");
$stmt->execute([':ip'=>$ip]);
$logs = $stmt->fetchAll();

// devices that used this IP
$devStmt = $pdo->prepare("SELECT * FROM login_devices WHERE ip = :ip ORDER BY last_seen DESC");
$devStmt->execute([':ip'=>$ip]);
$devices = $devStmt->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>IP Detail <?=htmlspecialchars($ip)?></title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                <div class="mt-2"><strong>Country:</strong> <?=htmlspecialchars($info['country'] ?? '-')?></div>
                <div><strong>City:</strong> <?=htmlspecialchars($info['city'] ?? '-')?></div>
                <div><strong>ISP:</strong> <?=htmlspecialchars($info['isp'] ?? '-')?></div>
                <div><strong>VPN/Proxy:</strong> <?=( (!empty($info['proxy'])||!empty($info['hosting'])) ? '<span class="text-red-600">Yes</span>' : 'No' )?></div>
            </div>
            <div>
                <div id="map" style="height:260px" class="rounded"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded shadow">
            <h4 class="font-semibold mb-3">Recent Logs (this IP)</h4>
            <div class="space-y-2 text-sm">
                <?php foreach($logs as $l): ?>
                    <div class="p-2 border rounded">
                        <div><strong><?=htmlspecialchars($l['username'] ?? 'User #'.$l['user_id'])?></strong> — <span class="text-xs text-slate-500"><?=htmlspecialchars($l['date'])?></span></div>
                        <div class="text-xs"><?=htmlspecialchars($l['country'].' / '.$l['city'].' — '.$l['isp'])?></div>
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
                        <div class="text-xs">Suspicious: <?=($d['is_suspicious'] ? 'Yes' : 'No')?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
const lat = <?=json_encode($info['lat'] ?? null)?>;
const lon = <?=json_encode($info['lon'] ?? null)?>;
if (lat && lon) {
    const map = L.map('map').setView([lat, lon], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lon]).addTo(map).bindPopup('<?=htmlspecialchars($info['country'].' / '.$info['city'])?>');
} else {
    document.getElementById('map').innerHTML = '<div class="text-center text-sm text-gray-500 p-6">No coordinates available</div>';
}
</script>
</body>
</html>
