<?php
require_once __DIR__ . '/admin_init.php';
require_admin();

function suspicious_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$vpnLogs = [];
$multiCity = [];
$devices = [];
$hasIpLogs = admin_table_exists($pdo, 'user_ip_logs');
$hasDevices = admin_table_exists($pdo, 'login_devices');

if ($hasIpLogs && admin_column_exists($pdo, 'user_ip_logs', 'is_vpn')) {
    $dateColumn = admin_column_exists($pdo, 'user_ip_logs', 'date') ? 'date' : (admin_column_exists($pdo, 'user_ip_logs', 'created_at') ? 'created_at' : (admin_column_exists($pdo, 'user_ip_logs', 'logged_at') ? 'logged_at' : 'id'));
    $country = admin_column_exists($pdo, 'user_ip_logs', 'country') ? 'l.country' : "''";
    $city = admin_column_exists($pdo, 'user_ip_logs', 'city') ? 'l.city' : "''";
    $isp = admin_column_exists($pdo, 'user_ip_logs', 'isp') ? 'l.isp' : "''";
    $userJoin = admin_table_exists($pdo, 'users') && admin_column_exists($pdo, 'user_ip_logs', 'user_id')
        ? 'LEFT JOIN users u ON u.id = l.user_id'
        : 'LEFT JOIN users u ON 1 = 0';
    $username = admin_table_exists($pdo, 'users') ? 'u.username' : "''";
    $stmt = $pdo->query(
        "SELECT l.ip, l.`$dateColumn` AS logged_at, $country AS country, $city AS city, $isp AS isp, $username AS username
         FROM user_ip_logs l $userJoin
         WHERE l.is_vpn = 1 ORDER BY l.`$dateColumn` DESC LIMIT 100"
    );
    $vpnLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (admin_column_exists($pdo, 'user_ip_logs', 'city') && admin_column_exists($pdo, 'user_ip_logs', 'user_id') && $dateColumn !== 'id') {
        $stmt = $pdo->query(
            "SELECT user_id, COUNT(DISTINCT city) AS city_count
             FROM user_ip_logs
             WHERE `$dateColumn` >= (NOW() - INTERVAL 1 DAY)
             GROUP BY user_id HAVING city_count >= 3
             ORDER BY city_count DESC LIMIT 100"
        );
        $multiCity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($hasDevices && admin_column_exists($pdo, 'login_devices', 'is_suspicious')) {
    $deviceIp = admin_column_exists($pdo, 'login_devices', 'ip') ? 'd.ip' : "''";
    $lastSeen = admin_column_exists($pdo, 'login_devices', 'last_seen') ? 'd.last_seen' : 'NULL';
    $userAgent = admin_column_exists($pdo, 'login_devices', 'user_agent') ? 'd.user_agent' : "''";
    $deviceUser = admin_table_exists($pdo, 'users') && admin_column_exists($pdo, 'login_devices', 'user_id')
        ? 'LEFT JOIN users u ON u.id = d.user_id'
        : 'LEFT JOIN users u ON 1 = 0';
    $username = admin_table_exists($pdo, 'users') ? 'u.username' : "''";
    $order = admin_column_exists($pdo, 'login_devices', 'last_seen') ? 'd.last_seen' : 'd.id';
    $stmt = $pdo->query(
        "SELECT d.id, $deviceIp AS ip, $lastSeen AS last_seen, $userAgent AS user_agent,
                d.is_suspicious, $username AS username
         FROM login_devices d $deviceUser
         WHERE d.is_suspicious = 1 ORDER BY $order DESC LIMIT 100"
    );
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Güvenlik incelemesi | Deucritic Admin</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f6fa;color:#172033;font:14px/1.5 system-ui,sans-serif}
        header{padding:16px 22px;background:#111a2b;color:white;display:flex;justify-content:space-between;align-items:center}
        header a{color:#cbd5e1;text-decoration:none}main{max-width:1180px;margin:0 auto;padding:24px 16px}
        h1{font-size:23px;margin:0}h2{font-size:16px;margin:0 0 12px}.card{background:white;border:1px solid #e6eaf0;border-radius:14px;padding:18px;margin-bottom:16px}
        .item{padding:13px 0;border-top:1px solid #eef0f4}.item:first-of-type{border-top:0}.meta{color:#687386;font-size:12px}.mono{font-family:ui-monospace,Consolas,monospace}
        .notice{color:#805d1b;background:#fff9eb;border:1px solid #f1dfb8;padding:12px;border-radius:10px}
        @media(max-width:600px){header{padding:14px}main{padding:16px 12px}.card{padding:14px}}
    </style>
</head>
<body>
<header><strong>Deucritic / Güvenlik incelemesi</strong><a href="index.php">Yönetim merkezine dön</a></header>
<main>
    <h1>Şüpheli oturum göstergeleri</h1>
    <p class="meta">VPN/proxy işaretleri veya kısa sürede birden fazla şehir görülmesi tek başına kötüye kullanım kanıtı değildir.</p>

    <section class="card">
        <h2>VPN / proxy işaretli girişler</h2>
        <?php if (!$hasIpLogs): ?><p class="notice">IP giriş kayıt tablosu bulunamadı.</p>
        <?php elseif ($vpnLogs === []): ?><p class="meta">İşaretli kayıt bulunmuyor.</p>
        <?php else: foreach ($vpnLogs as $row): ?>
            <article class="item"><strong><?= suspicious_h($row['username'] ?? 'Kullanıcı') ?></strong>
                <div class="meta"><span class="mono"><?= suspicious_h($row['ip'] ?? '') ?></span> · <?= suspicious_h($row['country'] ?? '') ?> <?= suspicious_h($row['city'] ?? '') ?> · <?= suspicious_h($row['isp'] ?? '') ?> · <?= suspicious_h($row['logged_at'] ?? '') ?></div>
            </article>
        <?php endforeach; endif; ?>
    </section>

    <section class="card">
        <h2>24 saatte birden fazla şehir</h2>
        <?php if ($multiCity === []): ?><p class="meta">Yeterli konum kaydı yok veya eşik aşılmadı.</p>
        <?php else: foreach ($multiCity as $row): ?>
            <article class="item">Kullanıcı #<?= (int) $row['user_id'] ?> · <?= (int) $row['city_count'] ?> farklı şehir</article>
        <?php endforeach; endif; ?>
    </section>

    <section class="card">
        <h2>Şüpheli cihaz kayıtları</h2>
        <?php if (!$hasDevices): ?><p class="notice">Cihaz kayıt tablosu bulunamadı.</p>
        <?php elseif ($devices === []): ?><p class="meta">İşaretli cihaz bulunmuyor.</p>
        <?php else: foreach ($devices as $device): ?>
            <article class="item"><strong><?= suspicious_h($device['username'] ?? 'Kullanıcı') ?></strong>
                <div class="meta"><span class="mono"><?= suspicious_h($device['ip'] ?? '') ?></span> · <?= suspicious_h($device['last_seen'] ?? '') ?></div>
                <div class="meta"><?= suspicious_h(substr((string) ($device['user_agent'] ?? ''), 0, 200)) ?></div>
            </article>
        <?php endforeach; endif; ?>
    </section>
</main>
</body>
</html>
