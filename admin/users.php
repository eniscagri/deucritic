<?php
require 'config.php'; 


$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

if ($search !== '') {
    $like = "%$search%";
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE username LIKE ? OR email LIKE ? OR school LIKE ? 
        ORDER BY created_at DESC 
        LIMIT ?, ?
    ");
    $stmt->bindValue(1, $like, PDO::PARAM_STR);
    $stmt->bindValue(2, $like, PDO::PARAM_STR);
    $stmt->bindValue(3, $like, PDO::PARAM_STR);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->bindValue(5, $perPage, PDO::PARAM_INT);
} else {
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ?, ?");
    $stmt->bindValue(1, $offset, PDO::PARAM_INT);
    $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
}
$stmt->execute();
$users = $stmt->fetchAll();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

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
                    <?php
                    
                    $cols = [
                        'id','username','email','password_hash','bio','school','grade_level',
                        'created_at','profile_photo','twitter','last_login','last_active',
                        'password_reset_token','password_reset_expiry','isverified','warnings',
                        'is_banned','last_ip','is_verified','verify_code','verify_expiry'
                    ];
                    foreach($cols as $c): ?>
                        <th class="p-2 border-b"><?=htmlspecialchars($c)?></th>
                    <?php endforeach; ?>
                    <th class="p-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <?php foreach($cols as $c): ?>
                            <td class="p-2"><?=htmlspecialchars($u[$c] ?? '')?></td>
                        <?php endforeach; ?>
                        <td class="p-2 flex gap-1 flex-wrap">
                            <!-- Ban / Unban -->
                            <form method="post" action="admin_users_action.php" onsubmit="return confirm('Ban / Unban user?');">
                                <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                                <input type="hidden" name="user_id" value="<?=htmlspecialchars($u['id'])?>">
                                <input type="hidden" name="action" value="<?=empty($u['is_banned']) ? 'ban' : 'unban'?>">
                                <button class="px-2 py-1 <?=empty($u['is_banned']) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'?> rounded text-sm">
                                    <?=empty($u['is_banned']) ? 'Ban' : 'Unban'?>
                                </button>
                            </form>
                            <!-- Warn -->
                            <button onclick="openWarn(<?=json_encode($u['id'])?>, <?=json_encode($u['username'])?>)" class="px-2 py-1 bg-amber-400 rounded text-sm">Warn</button>
                            <!-- Badges -->
                            <button onclick="openBadges(<?=json_encode($u['id'])?>)" class="px-2 py-1 bg-blue-200 rounded text-sm">Badges</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-center gap-2">
        <?php for($i=1;$i<=$totalPages;$i++): ?>
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
            if(data.length === 0){
                list.innerHTML = '<p>No badges.</p>';
            } else {
                list.innerHTML = '<ul>'+data.map(b=>'• '+b.badge_id+' (Awarded: '+b.awarded_at+', Unlocked: '+b.unlocked_at+')').join('<br>')+'</ul>';
            }
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
