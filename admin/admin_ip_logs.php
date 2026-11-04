<?php
require_once 'admin_init.php';
require_admin();

$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 30;
$offset = ($page-1)*$perPage;
$search = trim($_GET['q'] ?? '');

$params = [];
$sql = "SELECT l.*, u.username, u.email FROM user_ip_logs l LEFT JOIN users u ON l.user_id = u.id";
if ($search !== '') {
    $sql .= " WHERE u.username LIKE :s OR u.email LIKE :s OR l.ip LIKE :s";
    $params[':s'] = "%$search%";
}
$sql .= " ORDER BY l.date DESC LIMIT :off, :pp";
$stmt = $pdo->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->bindValue(':pp', $perPage, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

// count total (simple)
$countSql = "SELECT COUNT(*) FROM user_ip_logs l LEFT JOIN users u ON l.user_id = u.id";
if ($search !== '') $countSql .= " WHERE u.username LIKE :s OR u.email LIKE :s OR l.ip LIKE :s";
$countStmt = $pdo->prepare($countSql);
if ($search !== '') $countStmt->execute([':s'=>"%$search%"]); else $countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>IP Logs</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
<header class="bg-white shadow p-4 flex justify-between">
    <div class="text-xl font-bold">Admin — IP Logs</div>
    <div>
        <a href="index.php" class="px-3 py-1 bg-slate-100 rounded">Dashboard</a>
        <a href="admin_logout.php" class="px-3 py-1 bg-red-500 text-white rounded">Logout</a>
    </div>
</header>

<main class="max-w-7xl mx-auto p-6">
    <form method="get" class="mb-4 flex gap-2">
        <input name="q" value="<?=htmlspecialchars($search)?>" placeholder="search username/email/ip" class="border p-2 rounded-l w-80">
        <button class="bg-amber-500 text-white px-3 rounded-r">Search</button>
    </form>

    <div class="bg-white p-4 rounded shadow overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-2 text-left">#</th>
                    <th class="p-2 text-left">User</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">IP</th>
                    <th class="p-2 text-left">Country</th>
                    <th class="p-2 text-left">City</th>
                    <th class="p-2 text-left">ISP</th>
                    <th class="p-2 text-left">VPN?</th>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($logs as $l): ?>
                <tr class="border-t hover:bg-slate-50">
                    <td class="p-2"><?=htmlspecialchars($l['id'])?></td>
                    <td class="p-2"><?=htmlspecialchars($l['username'] ?? 'User #'.$l['user_id'])?></td>
                    <td class="p-2"><?=htmlspecialchars($l['email'] ?? '')?></td>
                    <td class="p-2 font-mono"><?=htmlspecialchars($l['ip'])?></td>
                    <td class="p-2"><?=htmlspecialchars($l['country'])?></td>
                    <td class="p-2"><?=htmlspecialchars($l['city'])?></td>
                    <td class="p-2"><?=htmlspecialchars($l['isp'])?></td>
                    <td class="p-2"><?=($l['is_vpn'] ? '<span class="text-red-600">Yes</span>' : 'No')?></td>
                    <td class="p-2"><?=htmlspecialchars($l['date'])?></td>
                    <td class="p-2 flex gap-1">
                        <a href="admin_ip_detail.php?ip=<?=urlencode($l['ip'])?>" class="px-2 py-1 bg-sky-600 text-white rounded text-xs">Details</a>
                        <form method="post" action="admin_ip_ban.php" onsubmit="return confirm('Ban this IP?');">
                            <input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>">
                            <input type="hidden" name="ip" value="<?=htmlspecialchars($l['ip'])?>">
                            <button class="px-2 py-1 bg-red-600 text-white rounded text-xs">Ban IP</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <tr><td colspan="10" class="p-4 text-center text-gray-500">No logs found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-between items-center">
        <div>Total: <?=$total?></div>
        <div class="flex gap-2">
            <?php if($page>1): ?><a href="?p=<?=$page-1?>&q=<?=urlencode($search)?>" class="px-3 py-1 bg-slate-200 rounded">Prev</a><?php endif;?>
            <?php for($i=1;$i<=$totalPages;$i++): ?>
                <a href="?p=<?=$i?>&q=<?=urlencode($search)?>" class="px-3 py-1 <?=($i==$page?'bg-blue-600 text-white':'bg-slate-100')?> rounded"><?=$i?></a>
            <?php endfor; ?>
            <?php if($page<$totalPages): ?><a href="?p=<?=$page+1?>&q=<?=urlencode($search)?>" class="px-3 py-1 bg-slate-200 rounded">Next</a><?php endif;?>
        </div>
    </div>
</main>
</body>
</html>
