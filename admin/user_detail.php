<?php
require_once __DIR__ . '/admin_init.php';
require_admin();

function admin_user_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$userId || !admin_table_exists($pdo, 'users')) {
    http_response_code(400);
    exit('Geçersiz kullanıcı.');
}

$accountColumns = ['id', 'username', 'email', 'bio', 'school', 'grade_level', 'created_at', 'last_ip', 'last_login', 'warnings', 'is_banned'];
$selectColumns = [];
foreach ($accountColumns as $column) {
    if (admin_column_exists($pdo, 'users', $column)) {
        $selectColumns[] = '`' . $column . '`';
    }
}
$stmt = $pdo->prepare('SELECT ' . implode(', ', $selectColumns) . ' FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$account) {
    http_response_code(404);
    exit('Kullanıcı bulunamadı.');
}

$topics = [];
if (admin_table_exists($pdo, 'topics') && admin_column_exists($pdo, 'topics', 'created_by')) {
    $description = admin_column_exists($pdo, 'topics', 'description') ? 'description' : "''";
    $createdAt = admin_column_exists($pdo, 'topics', 'created_at') ? 'created_at' : 'NULL';
    $stmt = $pdo->prepare("SELECT id, title, $description AS description, $createdAt AS created_at FROM topics WHERE created_by = ? ORDER BY id DESC LIMIT 100");
    $stmt->execute([$userId]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$entries = [];
if (admin_table_exists($pdo, 'entries') && admin_column_exists($pdo, 'entries', 'user_id')) {
    $topicJoin = admin_column_exists($pdo, 'entries', 'topic_id') && admin_table_exists($pdo, 'topics')
        ? 'LEFT JOIN topics t ON t.id = e.topic_id'
        : 'LEFT JOIN topics t ON 1 = 0';
    $topicTitle = admin_table_exists($pdo, 'topics') && admin_column_exists($pdo, 'topics', 'title') ? 't.title' : "''";
    $createdAt = admin_column_exists($pdo, 'entries', 'created_at') ? 'e.created_at' : 'NULL';
    $stmt = $pdo->prepare(
        "SELECT e.id, e.content, $createdAt AS created_at, $topicTitle AS topic_title
         FROM entries e $topicJoin WHERE e.user_id = ? ORDER BY e.id DESC LIMIT 100"
    );
    $stmt->execute([$userId]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$loginRows = [];
if (admin_table_exists($pdo, 'user_ip_logs') && admin_column_exists($pdo, 'user_ip_logs', 'user_id')) {
    $dateColumn = admin_column_exists($pdo, 'user_ip_logs', 'date') ? 'date' : (admin_column_exists($pdo, 'user_ip_logs', 'created_at') ? 'created_at' : (admin_column_exists($pdo, 'user_ip_logs', 'logged_at') ? 'logged_at' : 'id'));
    $ip = admin_column_exists($pdo, 'user_ip_logs', 'ip') ? '`ip`' : "''";
    $country = admin_column_exists($pdo, 'user_ip_logs', 'country') ? '`country`' : "''";
    $city = admin_column_exists($pdo, 'user_ip_logs', 'city') ? '`city`' : "''";
    $isp = admin_column_exists($pdo, 'user_ip_logs', 'isp') ? '`isp`' : "''";
    $vpn = admin_column_exists($pdo, 'user_ip_logs', 'is_vpn') ? '`is_vpn`' : '0';
    $userAgent = admin_column_exists($pdo, 'user_ip_logs', 'user_agent') ? '`user_agent`' : "''";
    $stmt = $pdo->prepare(
        "SELECT $ip AS ip, $country AS country, $city AS city, $isp AS isp, $vpn AS is_vpn,
                $userAgent AS user_agent, `$dateColumn` AS logged_at
         FROM user_ip_logs WHERE user_id = ? ORDER BY `$dateColumn` DESC LIMIT 100"
    );
    $stmt->execute([$userId]);
    $loginRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$moderationRows = [];
if (admin_table_exists($pdo, 'moderation_logs') && admin_column_exists($pdo, 'moderation_logs', 'user_id')) {
    $reason = admin_column_exists($pdo, 'moderation_logs', 'reason') ? 'reason' : "''";
    $createdAt = admin_column_exists($pdo, 'moderation_logs', 'created_at') ? 'created_at' : 'NULL';
    $stmt = $pdo->prepare("SELECT $reason AS reason, $createdAt AS created_at FROM moderation_logs WHERE user_id = ? ORDER BY id DESC LIMIT 50");
    $stmt->execute([$userId]);
    $moderationRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function admin_user_date($value): string
{
    $timestamp = $value ? strtotime((string) $value) : false;
    return $timestamp === false ? '—' : date('d.m.Y H:i', $timestamp);
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Kullanıcı #<?= (int) $userId ?> | Deucritic Admin</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f6fa;color:#172033;font:14px/1.5 system-ui,sans-serif}
        header{padding:16px 22px;background:#111a2b;color:white;display:flex;justify-content:space-between;align-items:center}header a{color:#dbe5f8;text-decoration:none}
        main{max-width:1250px;margin:0 auto;padding:24px 16px}.back{color:#476fe0}.head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:18px}
        h1{margin:0;font-size:24px}.sub{color:#6b7280}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
        .card{min-width:0;background:#fff;border:1px solid #e6eaf0;border-radius:14px;padding:17px}.card h2{margin:0 0 12px;font-size:15px}
        .fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.field{padding:10px;border-radius:9px;background:#f7f8fb;overflow-wrap:anywhere}
        .label{display:block;color:#798395;font-size:10px;text-transform:uppercase;font-weight:800;letter-spacing:.07em}.value{margin-top:3px;font-weight:650}
        .item{padding:12px 0;border-top:1px solid #edf0f4}.item:first-of-type{border-top:0}.meta{color:#717b8b;font-size:11px}.preview{margin-top:6px;white-space:pre-wrap;overflow-wrap:anywhere;color:#374151}
        .mono{font-family:ui-monospace,Consolas,monospace}.empty{color:#758094;font-size:12px}.badge{display:inline-block;padding:3px 8px;border-radius:99px;background:#fff0f1;color:#b83847;font-size:11px;font-weight:800}
        @media(max-width:760px){main{padding:17px 12px}.grid{grid-template-columns:1fr}.fields{grid-template-columns:1fr 1fr}.head{display:block}}
    </style>
</head>
<body>
<header><strong>Deucritic / Kullanıcı incelemesi</strong><a href="index.php">Yönetim merkezine dön</a></header>
<main>
    <div class="head"><div><a class="back" href="users.php">← Kullanıcılar</a><h1><?= admin_user_h($account['username'] ?? 'Kullanıcı') ?></h1><div class="sub">Hesap #<?= (int) $userId ?> · Oluşturulma: <?= admin_user_h(admin_user_date($account['created_at'] ?? null)) ?></div></div>
        <?php if (!empty($account['is_banned'])): ?><span class="badge">Hesap engelli</span><?php endif; ?>
    </div>
    <div class="grid">
        <section class="card">
            <h2>Hesap bilgileri</h2>
            <div class="fields">
                <?php foreach (['email' => 'E-posta', 'school' => 'Okul', 'grade_level' => 'Sınıf', 'bio' => 'Biyografi', 'last_login' => 'Son giriş', 'last_ip' => 'Son IP', 'warnings' => 'Uyarı sayısı'] as $key => $label): ?>
                    <?php if (array_key_exists($key, $account)): ?><div class="field"><span class="label"><?= admin_user_h($label) ?></span><div class="value"><?= admin_user_h($key === 'last_login' ? admin_user_date($account[$key]) : ($account[$key] ?? '—')) ?></div></div><?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card">
            <h2>Giriş geçmişi</h2>
            <?php if ($loginRows === []): ?><p class="empty">Ayrıntılı IP giriş tablosu yok veya bu kullanıcı için kayıt bulunmuyor.</p>
            <?php else: foreach ($loginRows as $login): ?>
                <article class="item"><strong class="mono"><?= admin_user_h($login['ip'] ?? 'IP yok') ?></strong> · <?= admin_user_h(admin_user_date($login['logged_at'] ?? null)) ?>
                    <div class="meta"><?= admin_user_h(implode(', ', array_filter([$login['city'] ?? '', $login['country'] ?? '']))) ?><?= !empty($login['isp']) ? ' · ' . admin_user_h($login['isp']) : '' ?><?= !empty($login['is_vpn']) ? ' · Proxy/VPN işareti' : '' ?><?= !empty($login['user_agent']) ? ' · ' . admin_user_h(substr($login['user_agent'], 0, 180)) : '' ?></div>
                </article>
            <?php endforeach; endif; ?>
        </section>

        <section class="card">
            <h2>Onep başlıkları <span class="meta">(<?= count($topics) ?> son kayıt)</span></h2>
            <?php if ($topics === []): ?><p class="empty">Başlık bulunmuyor.</p>
            <?php else: foreach ($topics as $topic): ?><article class="item"><strong><?= admin_user_h($topic['title'] ?? '') ?></strong><div class="meta">#<?= (int) $topic['id'] ?> · <?= admin_user_h(admin_user_date($topic['created_at'] ?? null)) ?></div><?php if (!empty($topic['description'])): ?><div class="preview"><?= admin_user_h($topic['description']) ?></div><?php endif; ?></article><?php endforeach; endif; ?>
        </section>

        <section class="card">
            <h2>Yanıtlar / paylaşımlar <span class="meta">(<?= count($entries) ?> son kayıt)</span></h2>
            <?php if ($entries === []): ?><p class="empty">Yanıt bulunmuyor.</p>
            <?php else: foreach ($entries as $entry): ?><article class="item"><strong><?= admin_user_h($entry['topic_title'] ?? 'Konu') ?></strong><div class="meta">#<?= (int) $entry['id'] ?> · <?= admin_user_h(admin_user_date($entry['created_at'] ?? null)) ?></div><div class="preview"><?= admin_user_h($entry['content'] ?? '') ?></div></article><?php endforeach; endif; ?>
        </section>

        <?php if (admin_table_exists($pdo, 'moderation_logs')): ?>
            <section class="card">
                <h2>Moderasyon kayıtları</h2>
                <?php if ($moderationRows === []): ?><p class="empty">Kayıt bulunmuyor.</p>
                <?php else: foreach ($moderationRows as $log): ?><article class="item"><?= admin_user_h($log['reason'] ?? '') ?><div class="meta"><?= admin_user_h(admin_user_date($log['created_at'] ?? null)) ?></div></article><?php endforeach; endif; ?>
            </section>
        <?php endif; ?>
    </div>
    <p class="meta">Parola özeti, parola sıfırlama anahtarları ve özel DM içerikleri bu görünümde gösterilmez.</p>
</main>
</body>
</html>
