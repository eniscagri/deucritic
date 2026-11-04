<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

$db_host = 'localhost';
$db_name = 'deucditiccom_deucritic';
$db_user = 'deucditiccom_eniscagri';
$db_pass = 'Enis07654607';

define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("❌ Veritabanı Bağlantı Hatası: " . $e->getMessage());
}

if (!isset($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
}

function csrf_token() {
    return $_SESSION['admin_csrf'] ?? '';
}

function csrf_check($token) {
    return isset($token) &&
           isset($_SESSION['admin_csrf']) &&
           hash_equals($_SESSION['admin_csrf'], $token);
}

function require_admin() {
    if (empty($_SESSION['admin_logged'])) {
        header("Location: admin_login.php");
        exit;
    }
}


function csrf_ok($token) {
    return isset($token, $_SESSION['admin_csrf']) && hash_equals($_SESSION['admin_csrf'], $token);
}

$loggedIn = !empty($_SESSION['admin_logged']);
$loginError = '';
if (!$loggedIn && $_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='login') {
    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $loginError = 'CSRF token error.';
    } else {
        $pw = $_POST['password'] ?? '';
        if (password_verify($pw, ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['admin_logged'] = true;
            $loggedIn = true;
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } else {
            $loginError = 'Incorrect password.';
        }
    }
}

if ($loggedIn && ($_GET['do'] ?? '')==='logout') {
    session_unset();
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$actionMsg = '';
if ($loggedIn && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $actionMsg = 'CSRF token mismatch.';
    } else {
        $act = $_POST['action'];
        switch ($act) {
            case 'ban_user':
            case 'unban_user':
                if (!empty($_POST['user_id'])) {
                    $uid = (int)$_POST['user_id'];
                    $stmt = $pdo->prepare("UPDATE users SET is_banned=:status WHERE id=:id");
                    $stmt->execute([':status'=>$act==='ban_user'?1:0, ':id'=>$uid]);
                    $actionMsg = "User #$uid ".($act==='ban_user'?'banned':'unbanned');
                }
                break;
            case 'delete_entry':
                if (!empty($_POST['entry_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM entries WHERE id=:id");
                    $stmt->execute([':id'=>(int)$_POST['entry_id']]);
                    $actionMsg = "Entry #".$_POST['entry_id']." deleted.";
                }
                break;
            case 'warn_user':
                if (!empty($_POST['user_id']) && !empty($_POST['reason'])) {
                    $stmt = $pdo->prepare("INSERT INTO moderation_logs (user_id, reason) VALUES (:uid,:reason)");
                    $stmt->execute([':uid'=>(int)$_POST['user_id'], ':reason'=>trim($_POST['reason'])]);
                    $stmt2 = $pdo->prepare("UPDATE users SET warnings=COALESCE(warnings,0)+1 WHERE id=:uid");
                    $stmt2->execute([':uid'=>(int)$_POST['user_id']]);
                    $actionMsg = "User #".$_POST['user_id']." warned.";
                }
                break;
            case 'delete_request':
                if (!empty($_POST['request_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM request WHERE id=:id");
                    $stmt->execute([':id'=>(int)$_POST['request_id']]);
                    $actionMsg = "Request #".$_POST['request_id']." removed.";
                }
                break;
            case 'delete_school_request':
                if (!empty($_POST['sr_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM school_requests WHERE id=:id");
                    $stmt->execute([':id'=>(int)$_POST['sr_id']]);
                    $actionMsg = "School request #".$_POST['sr_id']." removed.";
                }
                break;
            default:
                $actionMsg = 'Unknown action or missing parameters.';
        }
    }
}

function count_table($pdo, $table){
    $table=preg_replace('/[^a-z0-9_]/i','',$table);
    return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$totalUsers = count_table($pdo,'users');
$totalEntries = count_table($pdo,'entries');
$totalTopics = count_table($pdo,'topics');
$totalRequests = count_table($pdo,'request');
$totalSchoolReq = count_table($pdo,'school_requests');

$todayStr = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM logins WHERE DATE(login_time)=:today");
$stmt->execute([':today'=>$todayStr]);
$todayLogins = (int)$stmt->fetchColumn();

$search = trim($_GET['q'] ?? '');
$page = max(1,(int)($_GET['p']??1));
$perPage = 15;
$offset = ($page-1)*$perPage;

if($search!==''){
    $like="%$search%";
    $stmt=$pdo->prepare("SELECT * FROM users WHERE username LIKE :like OR email LIKE :like OR school LIKE :like ORDER BY created_at DESC LIMIT :offset,:perpage");
    $stmt->bindValue(':like',$like,PDO::PARAM_STR);
    $stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
    $stmt->bindValue(':perpage',$perPage,PDO::PARAM_INT);
    $stmt->execute();
    $usersRes = $stmt->fetchAll();

    $countStmt=$pdo->prepare("SELECT COUNT(*) FROM users WHERE username LIKE :like OR email LIKE :like OR school LIKE :like");
    $countStmt->execute([':like'=>$like]);
    $totalFound=(int)$countStmt->fetchColumn();
}else{
    $stmt=$pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT :offset,:perpage");
    $stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
    $stmt->bindValue(':perpage',$perPage,PDO::PARAM_INT);
    $stmt->execute();
    $usersRes=$stmt->fetchAll();
    $totalFound=$totalUsers;
}
$totalPages=(int)ceil($totalFound/$perPage);

$reqRes=$pdo->query("SELECT r.*,u.username FROM request r LEFT JOIN users u ON r.user_id=u.id ORDER BY r.date DESC LIMIT 50")->fetchAll();
$schoolReqRes=$pdo->query("SELECT s.*,u.username FROM school_requests s LEFT JOIN users u ON s.user_id=u.id ORDER BY s.date DESC LIMIT 50")->fetchAll();
$modLogsRes=$pdo->query("SELECT m.*,u.username FROM moderation_logs m LEFT JOIN users u ON m.user_id=u.id ORDER BY m.created_at DESC LIMIT 50")->fetchAll();


?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DeuCritic Admin Panel</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<?php include 'header.php'; ?>

<body class="bg-slate-50 text-slate-800">
<?php if(!$loggedIn): ?>
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-4 text-center">Admin Login</h1>
        <?php if($loginError): ?>
            <div class="mb-4 text-red-600 bg-red-100 p-2 rounded"><?=htmlspecialchars($loginError)?></div>
        <?php endif;?>
        <form method="post" class="space-y-4">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['admin_csrf'])?>">
            <input type="password" name="password" required class="w-full p-3 rounded border" placeholder="Password">
            <div class="flex justify-between items-center">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Sign in</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="max-w-7xl mx-auto px-6 py-6">
   
    <div class="grid grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-4 rounded shadow"><div>Total Users</div><div class="text-2xl font-bold"><?=$totalUsers?></div></div>
        <div class="bg-white p-4 rounded shadow"><div>Total Entries</div><div class="text-2xl font-bold"><?=$totalEntries?></div></div>
        <div class="bg-white p-4 rounded shadow"><div>Topics</div><div class="text-2xl font-bold"><?=$totalTopics?></div></div>
        <div class="bg-white p-4 rounded shadow"><div>Today Logins</div><div class="text-2xl font-bold"><?=$todayLogins?></div></div>
    </div>

  <div class="bg-white p-4 rounded shadow mb-6 overflow-x-auto">
<table class="w-full text-left border">
    <thead class="bg-slate-100">
    <tr>
        <th class="p-2 border">ID</th>
        <th class="p-2 border">Username</th>
        <th class="p-2 border">Email</th>
        <th class="p-2 border">School</th>
        <th class="p-2 border">Joined</th>
        <th class="p-2 border">Warnings</th>
        <th class="p-2 border">IP</th>
        <th class="p-2 border">Verified</th>
        <th class="p-2 border">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($usersRes as $u): ?>
        <tr class="border-t hover:bg-slate-50">
            <td class="p-2 border"><?=$u['id']?></td>
            <td class="p-2 border"><?=$u['username']?> <?=($u['is_banned']?'🔴':'')?></td>
            <td class="p-2 border"><?=$u['email']?></td>
            <td class="p-2 border"><?=$u['school']?></td>
            <td class="p-2 border"><?=$u['created_at']?></td>
            <td class="p-2 border"><?=$u['warnings']?></td>
            <td class="p-2 border"><?=$u['last_ip']?></td>
            <td class="p-2 border"><?=($u['is_verified']?'✅':'❌')?></td>
            <td class="p-2 border flex gap-1">
                <form method="post" class="inline">
                    <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                    <input type="hidden" name="user_id" value="<?=$u['id']?>">
                    <input type="hidden" name="action" value="<?=($u['is_banned']?'unban_user':'ban_user')?>">
                    <button class="<?=($u['is_banned']?'bg-green-500':'bg-red-500')?> text-white px-2 py-1 rounded text-xs">
                        <?=($u['is_banned']?'Unban':'Ban')?>
                    </button>
                </form>
                <form method="post" class="inline">
                    <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                    <input type="hidden" name="user_id" value="<?=$u['id']?>">
                    <input type="hidden" name="action" value="warn_user">
                    <button class="bg-amber-400 text-slate-800 px-2 py-1 rounded text-xs">Warn</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-4 flex justify-between items-center">
    <div>Total: <?=$totalFound?></div>
    <div class="flex gap-2">
        <?php if($page>1): ?>
            <a href="?p=<?=$page-1?>&q=<?=urlencode($search)?>" class="px-2 py-1 bg-slate-200 rounded">Prev</a>
        <?php endif;?>
        <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?p=<?=$i?>&q=<?=urlencode($search)?>" class="px-2 py-1 <?=($i==$page?'bg-blue-500 text-white':'bg-slate-200')?> rounded"><?=$i?></a>
        <?php endfor; ?>
        <?php if($page<$totalPages): ?>
            <a href="?p=<?=$page+1?>&q=<?=urlencode($search)?>" class="px-2 py-1 bg-slate-200 rounded">Next</a>
        <?php endif;?>
    </div>
</div>
</div>

</div>
<?php endif;?>
</body>
</html>
