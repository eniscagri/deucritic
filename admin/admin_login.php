<?php
require_once __DIR__ . '/admin_init.php';

if (!admin_request_ip_allowed()) {
    http_response_code(403);
    exit('Bu ağ adresinden yönetici paneline erişilemiyor.');
}

if (!empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

$passwordHash = admin_password_hash();
if ($passwordHash === '') {
    http_response_code(503);
}

$loginError = '';
$now = time();
$lockedUntil = (int) ($_SESSION['admin_login_locked_until'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $passwordHash !== '') {
    if ($lockedUntil > $now) {
        $loginError = 'Çok fazla hatalı deneme yapıldı. Lütfen daha sonra tekrar deneyin.';
    } elseif (!admin_csrf_ok($_POST['csrf'] ?? null)) {
        http_response_code(403);
        $loginError = 'İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.';
    } elseif (password_verify((string) ($_POST['password'] ?? ''), $passwordHash)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_last_activity'] = $now;
        $_SESSION['admin_login_attempts'] = 0;
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
        header('Location: index.php');
        exit;
    } else {
        $attempts = (int) ($_SESSION['admin_login_attempts'] ?? 0) + 1;
        $_SESSION['admin_login_attempts'] = $attempts;
        if ($attempts >= 5) {
            $_SESSION['admin_login_locked_until'] = $now + 900;
            $_SESSION['admin_login_attempts'] = 0;
        }
        $loginError = 'Yönetici parolası hatalı.';
    }
}

function admin_login_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Yönetici girişi | Deucritic</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#0b1220;color:#e5e7eb;font:16px/1.5 system-ui,sans-serif}
        .card{width:min(100%,420px);padding:36px;border:1px solid #263248;border-radius:22px;background:#111b2e;box-shadow:0 24px 80px #0005}
        .eyebrow{color:#93c5fd;font-size:12px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}h1{margin:10px 0;font-size:28px}
        p{color:#9aa8bd}.notice,.error{margin:18px 0;padding:13px 15px;border-radius:12px;font-size:14px}
        .notice{background:#332916;color:#fde68a}.error{background:#3b1b25;color:#fecdd3}
        label{display:block;margin:22px 0 8px;font-size:14px;font-weight:700}input{width:100%;padding:14px 15px;border:1px solid #334155;border-radius:12px;background:#0b1220;color:#fff;font-size:16px}
        button{width:100%;margin-top:16px;padding:14px;border:0;border-radius:12px;background:#60a5fa;color:#07111f;font-size:15px;font-weight:800;cursor:pointer}
        button:focus-visible,input:focus-visible{outline:3px solid #93c5fd;outline-offset:2px}
    </style>
</head>
<body>
    <main class="card">
        <div class="eyebrow">Deucritic / Private</div>
        <h1>Yönetici paneli</h1>
        <p>Bu alan yalnızca yetkili yönetici içindir. Oturumlar 30 dakika işlem yapılmadığında kapanır.</p>
        <?php if ($passwordHash === ''): ?>
            <div class="notice">Panel henüz etkinleştirilmedi. Sunucuda <code>DEUCRITIC_ADMIN_PASSWORD_HASH</code> ayarlanmalıdır.</div>
        <?php elseif ($loginError !== ''): ?>
            <div class="error" role="alert"><?= admin_login_escape($loginError) ?></div>
        <?php endif; ?>
        <?php if ($passwordHash !== ''): ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= admin_login_escape(admin_csrf_token()) ?>">
                <label for="password">Yönetici parolası</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" autofocus>
                <button type="submit">Güvenli giriş yap</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
