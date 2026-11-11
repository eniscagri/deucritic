<?php
require 'config.php';

$search = trim((string) ($_GET['q'] ?? ''));
$search = function_exists('mb_substr') ? mb_substr($search, 0, 100, 'UTF-8') : substr($search, 0, 100);
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;

$cols = array_values(array_filter(
    ['id', 'username', 'email', 'school', 'grade_level', 'created_at', 'last_ip', 'last_login', 'warnings', 'is_banned'],
    static function ($column) use ($pdo) {
        return admin_column_exists($pdo, 'users', $column);
    }
));
$searchColumns = array_values(array_filter(
    ['username', 'email', 'school'],
    static function ($column) use ($pdo) {
        return admin_column_exists($pdo, 'users', $column);
    }
));
$orderColumn = admin_column_exists($pdo, 'users', 'created_at') ? 'created_at' : 'id';
$where = '';
$params = [];
if ($search !== '') {
    $like = "%$search%";
    if ($searchColumns !== []) {
        $where = ' WHERE (' . implode(' OR ', array_map(static function ($column) {
            return '`' . $column . '` LIKE ?';
        }, $searchColumns)) . ')';
        $params = array_fill(0, count($searchColumns), $like);
    }
}
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM users' . $where);
$countStmt->execute($params);
$totalUsers = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare('SELECT `' . implode('`, `', $cols) . '` FROM users' . $where . ' ORDER BY `' . $orderColumn . '` DESC LIMIT ?, ?');
foreach ($params as $index => $value) {
    $stmt->bindValue($index + 1, $value, PDO::PARAM_STR);
}
$stmt->bindValue(count($params) + 1, $offset, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $perPage, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Users Panel</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
<?php include 'header.php'; ?>


<main class="max-w-7xl mx-auto p-6">
    <div class="overflow-x-auto bg-white shadow rounded">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-100">
                <tr>
                    <?php foreach($cols as $c): ?>
                        <th class="p-2 border-b"><?=htmlspecialchars($c)?></th>
                    <?php endforeach; ?>
                    <th class="p-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <?php foreach($cols as $c): ?>
                            <td class="p-2"><?php if ($c === 'username'): ?><a class="text-blue-700 font-semibold hover:underline" href="user_detail.php?id=<?= (int) $u['id'] ?>"><?=htmlspecialchars($u[$c] ?? '')?></a><?php else: ?><?=htmlspecialchars($u[$c] ?? '')?><?php endif; ?></td>
                        <?php endforeach; ?>
                        <td class="p-2 flex gap-1 flex-wrap">
                            <!-- Ban / Unban -->
                            <?php if (in_array('is_banned', $cols, true)): ?>
                            <form method="post" action="admin_users_action.php" onsubmit="return confirm('Hesap durumunu değiştirmek istiyor musun?');">
                                <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                                <input type="hidden" name="user_id" value="<?=htmlspecialchars($u['id'])?>">
                                <input type="hidden" name="action" value="<?=empty($u['is_banned']) ? 'ban' : 'unban'?>">
                                <button class="px-2 py-1 <?=empty($u['is_banned']) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'?> rounded text-sm">
                                    <?=empty($u['is_banned']) ? 'Engelle' : 'Engeli kaldır'?>
                                </button>
                            </form>
                            <?php endif; ?>
                            <button onclick="openWarn(<?=json_encode($u['id'])?>, <?=json_encode($u['username'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)?>)" class="px-2 py-1 bg-amber-400 rounded text-sm">Uyar</button>
                            <button onclick="openBadges(<?=json_encode($u['id'])?>)" class="px-2 py-1 bg-blue-200 rounded text-sm">Rozetler</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($users === []): ?><tr><td class="p-4 text-center text-slate-500" colspan="<?= count($cols) + 1 ?>">Kullanıcı bulunamadı.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-center gap-2">
        <?php for($i=max(1, $page-2);$i<=min($totalPages, $page+2);$i++): ?>
            <a href="?p=<?=$i?>&q=<?=urlencode($search)?>" class="px-3 py-1 rounded <?= $i==$page ? 'bg-blue-600 text-white' : 'bg-slate-100' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</main>

<div id="warnModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4">
    <div class="bg-white w-full max-w-lg p-6 rounded">
        <h4 class="text-lg font-semibold mb-2">Warn User <span id="warnUserLabel" class="text-sm text-slate-500"></span></h4>
        <form method="post" action="admin_users_action.php">
            <input type="hidden" name="csrf" value="<?=csrf_token()?>">
            <input type="hidden" id="warn_user_id" name="user_id">
            <input type="hidden" name="action" value="warn">
            <label class="block text-sm font-medium">Reason</label>
            <textarea name="reason" required class="w-full p-2 border rounded mb-3"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeWarn()" class="px-3 py-1 border rounded">Cancel</button>
                <button class="px-3 py-1 bg-amber-400 rounded">Warn</button>
            </div>
        </form>
    </div>
</div>

<div id="badgesModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 p-4">
    <div class="bg-white w-full max-w-lg p-6 rounded">
        <h4 class="text-lg font-semibold mb-2">User Badges</h4>
        <div id="badgeList" class="text-sm"></div>
        <div class="flex justify-end mt-4">
            <button onclick="closeBadges()" class="px-3 py-1 border rounded">Close</button>
        </div>
    </div>
</div>

<script>
function openWarn(id, username){
    document.getElementById('warn_user_id').value = id;
    document.getElementById('warnUserLabel').textContent = username ? ' / '+username : '';
    document.getElementById('warnModal').classList.remove('hidden');
    document.getElementById('warnModal').classList.add('flex');
}
function closeWarn(){
    document.getElementById('warnModal').classList.remove('flex');
    document.getElementById('warnModal').classList.add('hidden');
}

function openBadges(userId){
    fetch('admin_users_badges.php?user_id='+userId)
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('badgeList');
            list.replaceChildren();
            if(data.length === 0){
                const empty = document.createElement('p');
                empty.textContent = 'Rozet bulunamadı.';
                list.appendChild(empty);
            } else {
                const ul = document.createElement('ul');
                data.forEach(badge => {
                    const item = document.createElement('li');
                    item.textContent = 'Rozet #' + badge.badge_id + ' · Verilme: ' + (badge.awarded_at || '—') + ' · Açılma: ' + (badge.unlocked_at || '—');
                    ul.appendChild(item);
                });
                list.appendChild(ul);
            }
        })
        .catch(() => {
            document.getElementById('badgeList').textContent = 'Rozetler yüklenemedi.';
        });
    document.getElementById('badgesModal').classList.remove('hidden');
    document.getElementById('badgesModal').classList.add('flex');
}
function closeBadges(){
    document.getElementById('badgesModal').classList.remove('flex');
    document.getElementById('badgesModal').classList.add('hidden');
}
</script>

</body>
</html>
