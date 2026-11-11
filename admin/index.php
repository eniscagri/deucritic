<?php
require_once __DIR__ . '/admin_init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    if (!admin_csrf_ok($_POST['csrf'] ?? null)) {
        http_response_code(403);
        exit('İstek doğrulanamadı.');
    }

    unset($_SESSION['admin_logged'], $_SESSION['admin_last_activity']);
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    header('Location: admin_login.php');
    exit;
}

function admin_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_value_expression(PDO $pdo, string $table, string $column, string $alias): string
{
    if (admin_column_exists($pdo, $table, $column)) {
        return '`' . $column . '` AS `' . $alias . '`';
    }
    return 'NULL AS `' . $alias . '`';
}

function admin_date_label($value): string
{
    if (!$value) {
        return '—';
    }
    $timestamp = strtotime((string) $value);
    return $timestamp === false ? '—' : date('d.m.Y H:i', $timestamp);
}

$search = trim((string) ($_GET['q'] ?? ''));
if (function_exists('mb_substr')) {
    $search = mb_substr($search, 0, 100, 'UTF-8');
} else {
    $search = substr($search, 0, 100);
}
$hasUsers = admin_table_exists($pdo, 'users');
$hasTopics = admin_table_exists($pdo, 'topics');
$hasEntries = admin_table_exists($pdo, 'entries');
$hasReports = admin_table_exists($pdo, 'reports');
$hasIpLogs = admin_table_exists($pdo, 'user_ip_logs');
$hasModerationLogs = admin_table_exists($pdo, 'moderation_logs');
$hasDevices = admin_table_exists($pdo, 'login_devices');

$stats = [
    ['label' => 'Kullanıcı', 'value' => admin_table_count($pdo, 'users'), 'icon' => 'U'],
    ['label' => 'Onep başlığı', 'value' => admin_table_count($pdo, 'topics'), 'icon' => 'O'],
    ['label' => 'Yanıt / paylaşım', 'value' => admin_table_count($pdo, 'entries'), 'icon' => 'Y'],
    ['label' => 'Şikâyet kaydı', 'value' => admin_table_count($pdo, 'reports'), 'icon' => 'Ş'],
];
if (admin_column_exists($pdo, 'users', 'is_banned')) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE is_banned = 1');
    $stats[] = ['label' => 'Engelli hesap', 'value' => (int) $stmt->fetchColumn(), 'icon' => 'E'];
}
if (admin_column_exists($pdo, 'users', 'created_at')) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE created_at >= CURRENT_DATE()');
    $stats[] = ['label' => 'Bugün kayıt', 'value' => (int) $stmt->fetchColumn(), 'icon' => 'B'];
}
if (admin_table_exists($pdo, 'messages')) {
    $stats[] = ['label' => 'DM mesajı', 'value' => admin_table_count($pdo, 'messages'), 'icon' => 'M'];
}

$userRows = [];
if ($hasUsers && admin_column_exists($pdo, 'users', 'id') && admin_column_exists($pdo, 'users', 'username')) {
    $userColumns = [
        '`id`',
        '`username`',
        admin_value_expression($pdo, 'users', 'email', 'email'),
        admin_value_expression($pdo, 'users', 'created_at', 'created_at'),
        admin_value_expression($pdo, 'users', 'last_ip', 'last_ip'),
        admin_value_expression($pdo, 'users', 'last_login', 'last_login'),
        admin_value_expression($pdo, 'users', 'is_banned', 'is_banned'),
    ];
    $where = '';
    $params = [];
    if ($search !== '') {
        $searchable = ['username', 'email', 'school', 'last_ip'];
        $conditions = [];
        foreach ($searchable as $column) {
            if (admin_column_exists($pdo, 'users', $column)) {
                $conditions[] = '`' . $column . '` LIKE ?';
                $params[] = '%' . $search . '%';
            }
        }
        if ($conditions !== []) {
            $where = ' WHERE (' . implode(' OR ', $conditions) . ')';
        }
    }
    $orderColumn = admin_column_exists($pdo, 'users', 'created_at') ? 'created_at' : 'id';
    $stmt = $pdo->prepare(
        'SELECT ' . implode(', ', $userColumns) . ' FROM users' . $where . ' ORDER BY `' . $orderColumn . '` DESC LIMIT 10'
    );
    $stmt->execute($params);
    $userRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$contentRows = [];
if ($hasTopics && $hasEntries && $hasUsers) {
    $entrySearch = $search === '' ? '' : ' WHERE e.content LIKE ? OR t.title LIKE ?';
    $topicDescription = admin_column_exists($pdo, 'topics', 'description') ? 't.description' : "''";
    $topicSearchConditions = [];
    if ($search !== '') {
        $topicSearchConditions[] = 't.title LIKE ?';
        if (admin_column_exists($pdo, 'topics', 'description')) {
            $topicSearchConditions[] = 't.description LIKE ?';
        }
    }
    $topicSearch = $topicSearchConditions === [] ? '' : ' WHERE ' . implode(' OR ', $topicSearchConditions);
    $topicCreatedBy = admin_column_exists($pdo, 'topics', 'created_by') ? 't.created_by' : 'NULL';
    $entryUserId = admin_column_exists($pdo, 'entries', 'user_id') ? 'e.user_id' : 'NULL';
    $entryTopicId = admin_column_exists($pdo, 'entries', 'topic_id') ? 'e.topic_id' : 'NULL';
    $topicUserJoin = admin_column_exists($pdo, 'topics', 'created_by') ? 'LEFT JOIN users tu ON tu.id = t.created_by' : 'LEFT JOIN users tu ON 1 = 0';
    $entryUserJoin = admin_column_exists($pdo, 'entries', 'user_id') ? 'LEFT JOIN users eu ON eu.id = e.user_id' : 'LEFT JOIN users eu ON 1 = 0';
    $entryTopicJoin = admin_column_exists($pdo, 'entries', 'topic_id') ? 'LEFT JOIN topics t ON t.id = e.topic_id' : 'LEFT JOIN topics t ON 1 = 0';
    $stmt = $pdo->prepare(
        "SELECT * FROM (
            SELECT 'topic' AS kind, t.id, t.title AS headline, $topicDescription AS body,
                   t.created_at, tu.username, $topicCreatedBy AS user_id
            FROM topics t $topicUserJoin $topicSearch
            UNION ALL
            SELECT 'entry' AS kind, e.id, COALESCE(t.title, 'Başlıksız konu') AS headline,
                   e.content AS body, e.created_at, eu.username, $entryUserId AS user_id
            FROM entries e $entryTopicJoin $entryUserJoin $entrySearch
        ) AS content_feed
        ORDER BY created_at DESC
        LIMIT 12"
    );
    $contentParams = [];
    if ($search !== '') {
        $like = '%' . $search . '%';
        $contentParams[] = $like;
        if (admin_column_exists($pdo, 'topics', 'description')) {
            $contentParams[] = $like;
        }
        $contentParams[] = $like;
        $contentParams[] = $like;
    }
    $stmt->execute($contentParams);
    $contentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$reportRows = [];
if ($hasReports && $hasEntries && admin_column_exists($pdo, 'reports', 'entry_id')) {
    $reportedContent = admin_column_exists($pdo, 'entries', 'content') ? 'e.content' : "''";
    $reportReason = admin_column_exists($pdo, 'reports', 'reason') ? 'r.reason' : "''";
    $reportDate = admin_column_exists($pdo, 'reports', 'created_at') ? 'r.created_at' : 'NULL';
    $reporterJoin = admin_column_exists($pdo, 'reports', 'reported_by') ? 'LEFT JOIN users ru ON ru.id = r.reported_by' : 'LEFT JOIN users ru ON 1 = 0';
    $reportTopicJoin = admin_column_exists($pdo, 'entries', 'topic_id') ? 'LEFT JOIN topics rt ON rt.id = e.topic_id' : 'LEFT JOIN topics rt ON 1 = 0';
    $stmt = $pdo->query(
        "SELECT r.id, $reportDate AS created_at, $reportReason AS reason,
                $reportedContent AS content, rt.title AS topic_title, ru.username AS reporter
         FROM reports r
         LEFT JOIN entries e ON e.id = r.entry_id
         $reportTopicJoin
         $reporterJoin
         ORDER BY r.id DESC
         LIMIT 6"
    );
    $reportRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$ipRows = [];
if ($hasIpLogs && $hasUsers && admin_column_exists($pdo, 'user_ip_logs', 'user_id') && admin_column_exists($pdo, 'user_ip_logs', 'ip')) {
    $dateColumn = null;
    foreach (['date', 'created_at', 'logged_at'] as $candidate) {
        if (admin_column_exists($pdo, 'user_ip_logs', $candidate)) {
            $dateColumn = $candidate;
            break;
        }
    }
    $ipLogColumns = [
        'l.ip',
        admin_column_exists($pdo, 'user_ip_logs', 'country') ? 'l.country AS country' : 'NULL AS country',
        admin_column_exists($pdo, 'user_ip_logs', 'city') ? 'l.city AS city' : 'NULL AS city',
        admin_column_exists($pdo, 'user_ip_logs', 'isp') ? 'l.isp AS isp' : 'NULL AS isp',
        admin_column_exists($pdo, 'user_ip_logs', 'is_vpn') ? 'l.is_vpn AS is_vpn' : 'NULL AS is_vpn',
        $dateColumn === null ? 'NULL AS logged_at' : 'l.`' . $dateColumn . '` AS logged_at',
        'u.username',
    ];
    $orderColumn = $dateColumn === null ? 'l.user_id' : 'l.`' . $dateColumn . '`';
    $stmt = $pdo->query(
        'SELECT ' . implode(', ', $ipLogColumns) . ' FROM user_ip_logs l
         LEFT JOIN users u ON u.id = l.user_id
         ORDER BY ' . $orderColumn . ' DESC LIMIT 8'
    );
    $ipRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($hasUsers && admin_column_exists($pdo, 'users', 'last_ip')) {
    $lastLogin = admin_value_expression($pdo, 'users', 'last_login', 'logged_at');
    $loginOrder = admin_column_exists($pdo, 'users', 'last_login') ? 'last_login' : 'id';
    $stmt = $pdo->query(
        'SELECT username, last_ip AS ip, ' . $lastLogin . ', NULL AS country, NULL AS city, NULL AS isp
         FROM users WHERE last_ip IS NOT NULL AND last_ip <> ""
         ORDER BY `' . $loginOrder . '` DESC LIMIT 8'
    );
    $ipRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$moderationRows = [];
if ($hasModerationLogs && admin_column_exists($pdo, 'moderation_logs', 'user_id')) {
    $reason = admin_column_exists($pdo, 'moderation_logs', 'reason') ? 'm.reason' : "''";
    $createdAt = admin_column_exists($pdo, 'moderation_logs', 'created_at') ? 'm.created_at' : 'NULL';
    $stmt = $pdo->query(
        "SELECT m.id, $reason AS reason, $createdAt AS created_at, u.username
         FROM moderation_logs m LEFT JOIN users u ON u.id = m.user_id
         ORDER BY m.id DESC LIMIT 5"
    );
    $moderationRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Yönetim merkezi | Deucritic</title>
    <style>
        :root{color-scheme:light;--ink:#172033;--muted:#6b7280;--line:#e6eaf0;--surface:#fff;--bg:#f4f6fa;--nav:#111a2b;--blue:#4778ef;--blue-soft:#edf2ff;--green:#15856b;--amber:#a56512;--red:#c34352}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        a{color:inherit;text-decoration:none}button,input{font:inherit}.shell{min-height:100vh;display:grid;grid-template-columns:248px minmax(0,1fr)}
        aside{background:var(--nav);color:#fff;padding:26px 18px;display:flex;flex-direction:column;gap:26px}
        .brand{display:flex;align-items:center;gap:12px;padding:4px 8px;font-size:17px;font-weight:800;letter-spacing:-.03em}
        .brand-mark{width:36px;height:36px;display:grid;place-items:center;border-radius:12px;background:#5681f5;color:#fff}
        .nav-label{padding:0 10px;color:#8591a5;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        nav{display:grid;gap:6px}.nav-link{padding:11px 12px;border-radius:10px;color:#c5ccda;font-weight:650;display:flex;align-items:center;gap:11px}
        .nav-link.active,.nav-link:hover{background:#202c42;color:white}.nav-icon{width:22px;text-align:center;color:#98abcc;font-weight:800}
        .side-note{margin-top:auto;padding:14px;border:1px solid #29364d;border-radius:12px;color:#9daac0;font-size:12px}
        .side-note strong{display:block;color:#e5eaf2;margin-bottom:4px}.main{min-width:0}
        .topbar{height:74px;padding:0 34px;border-bottom:1px solid var(--line);background:#ffffffd9;display:flex;align-items:center;justify-content:space-between;gap:18px}
        .crumb{color:var(--muted);font-size:13px}.top-actions{display:flex;align-items:center;gap:14px}.secure{display:flex;align-items:center;gap:7px;color:var(--green);font-size:12px;font-weight:750}
        .logout{padding:9px 13px;border:1px solid var(--line);border-radius:9px;background:white;color:#364152;font-size:12px;font-weight:750;cursor:pointer}
        .content{max-width:1500px;margin:0 auto;padding:30px 34px 48px}.page-title{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:24px}
        h1{margin:0;font-size:27px;line-height:1.2;letter-spacing:-.04em}.subtitle{margin:7px 0 0;color:var(--muted)}
        .search{display:flex;width:min(100%,390px);background:#fff;border:1px solid var(--line);border-radius:11px;overflow:hidden}
        .search input{width:100%;min-width:0;padding:11px 13px;border:0;outline:0}.search button{padding:0 15px;border:0;background:var(--nav);color:#fff;font-weight:750;cursor:pointer}
        .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:18px}
        .stat,.panel{background:var(--surface);border:1px solid var(--line);border-radius:14px;box-shadow:0 2px 6px #12203a08}
        .stat{padding:17px 18px;display:flex;align-items:center;justify-content:space-between;min-height:102px}.stat-label{color:var(--muted);font-size:12px;font-weight:700}
        .stat-value{margin-top:7px;font-size:27px;font-weight:850;letter-spacing:-.04em}.stat-icon{width:40px;height:40px;display:grid;place-items:center;border-radius:12px;background:var(--blue-soft);color:var(--blue);font-weight:850}
        .grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,.85fr);gap:17px;align-items:start}
        .panel{min-width:0;overflow:hidden}.panel+.panel{margin-top:17px}.panel-head{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:12px}
        h2{margin:0;font-size:14px;letter-spacing:-.01em}.panel-hint{margin-top:3px;color:var(--muted);font-size:11px}.panel-body{padding:0 18px}
        .row{padding:14px 0;border-bottom:1px solid #eef0f4}.row:last-child{border-bottom:0}.row-top{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .row-title{font-weight:750;overflow-wrap:anywhere}.row-meta{margin-top:5px;color:var(--muted);font-size:11px;display:flex;flex-wrap:wrap;gap:9px}
        .preview{margin-top:8px;color:#4b5563;font-size:12px;white-space:pre-wrap;overflow-wrap:anywhere;max-height:72px;overflow:hidden}
        .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#f0f2f6;color:#5d6676;font-size:10px;font-weight:800}
        .badge.topic{background:#edf2ff;color:#436bd3}.badge.entry{background:#e8f7f1;color:#148261}.badge.danger{background:#fff0f1;color:#b83847}
        .table-wrap{overflow:auto}.table{width:100%;border-collapse:collapse;min-width:540px}.table th,.table td{padding:11px 13px;border-bottom:1px solid #eef0f4;text-align:left;vertical-align:top}
        .table th{color:#7b8493;font-size:10px;letter-spacing:.08em;text-transform:uppercase}.table td{font-size:12px}.table tr:last-child td{border-bottom:0}
        .mono{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:11px}.muted{color:var(--muted)}.empty{padding:22px 4px;color:var(--muted);font-size:12px}
        .notice{margin:0 0 17px;padding:12px 15px;border:1px solid #f1dfb8;border-radius:11px;background:#fff9eb;color:#805d1b;font-size:12px}
        .privacy{margin-top:17px;color:#7a8494;font-size:11px}
        @media(max-width:1100px){.shell{grid-template-columns:205px minmax(0,1fr)}.stats{grid-template-columns:repeat(3,minmax(0,1fr))}.grid{grid-template-columns:minmax(0,1fr)}}
        @media(max-width:700px){.shell{display:block}aside{padding:12px 14px;gap:12px}.brand{padding:0 4px}.nav-label,.side-note{display:none}nav{display:flex;overflow:auto;gap:4px}nav .nav-link{white-space:nowrap;padding:9px 10px;font-size:12px}.topbar{height:54px;padding:0 16px}.content{padding:21px 14px 32px}.page-title{display:block}.search{margin-top:16px;width:100%}.stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.stat{min-height:88px;padding:13px}.stat-value{font-size:23px}.grid{gap:12px}.panel+.panel{margin-top:12px}.secure{font-size:0}.secure:first-letter{font-size:12px}}
    </style>
</head>
<body>
<div class="shell">
    <aside>
        <a class="brand" href="index.php"><span class="brand-mark">D</span><span>Deucritic <span style="color:#9aa8bd">Admin</span></span></a>
        <div class="nav-label">Yönetim</div>
        <nav aria-label="Yönetim menüsü">
            <a class="nav-link active" href="index.php"><span class="nav-icon">01</span>Genel bakış</a>
            <a class="nav-link" href="users.php"><span class="nav-icon">02</span>Kullanıcılar</a>
            <?php if ($hasIpLogs): ?><a class="nav-link" href="admin_ip_logs.php"><span class="nav-icon">03</span>Giriş kayıtları</a><?php endif; ?>
            <?php if ($hasIpLogs || $hasDevices): ?><a class="nav-link" href="admin_suspicious.php"><span class="nav-icon">04</span>Güvenlik incelemesi</a><?php endif; ?>
            <?php if (admin_table_exists($pdo, 'request') || admin_table_exists($pdo, 'school_requests')): ?><a class="nav-link" href="req.php"><span class="nav-icon">05</span>Kullanıcı talepleri</a><?php endif; ?>
        </nav>
        <div class="side-note"><strong>Özel yönetim alanı</strong>İçerik, IP ve hesap verileri yalnızca yetkili yönetici oturumunda gösterilir.</div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="crumb">Yönetim merkezi <span aria-hidden="true">/</span> Genel bakış</div>
            <div class="top-actions">
                <span class="secure"><span aria-hidden="true">●</span> Güvenli oturum</span>
                <form method="post">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf" value="<?= admin_h(admin_csrf_token()) ?>">
                    <button class="logout" type="submit">Oturumu kapat</button>
                </form>
            </div>
        </header>

        <main class="content">
            <div class="page-title">
                <div>
                    <h1>Genel bakış</h1>
                    <p class="subtitle">Topluluk, içerik ve erişim kayıtlarının yönetim özeti.</p>
                </div>
                <form class="search" method="get" role="search">
                    <input type="search" name="q" value="<?= admin_h($search) ?>" placeholder="Kullanıcı, e-posta veya içerikte ara" aria-label="Yönetim verilerinde ara">
                    <button type="submit">Ara</button>
                </form>
            </div>

            <?php if (!$hasIpLogs): ?>
                <div class="notice">Ayrıntılı konum ve oturum geçmişi için veritabanında <code>user_ip_logs</code> tablosu bulunamadı. Mevcut kullanıcı kayıtlarında varsa son IP ve giriş bilgisi gösterilir.</div>
            <?php endif; ?>

            <section class="stats" aria-label="Platform istatistikleri">
                <?php foreach ($stats as $stat): ?>
                    <article class="stat">
                        <div><div class="stat-label"><?= admin_h($stat['label']) ?></div><div class="stat-value"><?= number_format((int) $stat['value']) ?></div></div>
                        <div class="stat-icon" aria-hidden="true"><?= admin_h($stat['icon']) ?></div>
                    </article>
                <?php endforeach; ?>
            </section>

            <?php if ($search !== ''): ?>
                <div class="notice">“<?= admin_h($search) ?>” araması kullanıcılar ve son içerik kayıtlarında uygulandı.</div>
            <?php endif; ?>

            <div class="grid">
                <div>
                    <section class="panel">
                        <div class="panel-head"><div><h2><?= $search !== '' ? 'Arama sonuçları / içerik' : 'Son paylaşımlar' ?></h2><div class="panel-hint">Onep başlıkları ve yanıtlar</div></div><span class="badge"><?= count($contentRows) ?> kayıt</span></div>
                        <div class="panel-body">
                            <?php if ($contentRows === []): ?>
                                <div class="empty">Gösterilecek içerik bulunamadı.</div>
                            <?php else: foreach ($contentRows as $item): ?>
                                <article class="row">
                                    <div class="row-top">
                                        <div class="row-title"><?= admin_h($item['headline'] ?? 'Başlıksız içerik') ?></div>
                                        <span class="badge <?= $item['kind'] === 'topic' ? 'topic' : 'entry' ?>"><?= $item['kind'] === 'topic' ? 'ONEP' : 'YANIT' ?></span>
                                    </div>
                                    <div class="row-meta">
                                        <span><?= admin_h($item['username'] ?? 'Kullanıcı #' . ($item['user_id'] ?? '?')) ?></span>
                                        <span><?= admin_h(admin_date_label($item['created_at'] ?? null)) ?></span>
                                        <span>#<?= (int) $item['id'] ?></span>
                                    </div>
                                    <?php if (trim((string) ($item['body'] ?? '')) !== ''): ?><div class="preview"><?= admin_h($item['body']) ?></div><?php endif; ?>
                                </article>
                            <?php endforeach; endif; ?>
                        </div>
                    </section>

                    <?php if ($hasReports): ?>
                        <section class="panel">
                            <div class="panel-head"><div><h2>Son şikâyetler</h2><div class="panel-hint">Kullanıcı bildirimleri ve ilgili içerikler</div></div><span class="badge danger"><?= count($reportRows) ?> kayıt</span></div>
                            <div class="panel-body">
                                <?php if ($reportRows === []): ?>
                                    <div class="empty">Şikâyet kaydı yok veya ilişkili tablo hazır değil.</div>
                                <?php else: foreach ($reportRows as $report): ?>
                                    <article class="row">
                                        <div class="row-top"><div class="row-title"><?= admin_h($report['topic_title'] ?? 'İçerik #' . $report['id']) ?></div><span class="badge danger">ŞİKÂYET</span></div>
                                        <div class="row-meta"><span>Bildiren: <?= admin_h($report['reporter'] ?? 'Bilinmiyor') ?></span><span><?= admin_h(admin_date_label($report['created_at'] ?? null)) ?></span></div>
                                        <?php if (!empty($report['reason'])): ?><div class="preview">Neden: <?= admin_h($report['reason']) ?></div><?php endif; ?>
                                        <?php if (!empty($report['content'])): ?><div class="preview"><?= admin_h($report['content']) ?></div><?php endif; ?>
                                    </article>
                                <?php endforeach; endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>

                <div>
                    <section class="panel">
                        <div class="panel-head"><div><h2><?= $search !== '' ? 'Kullanıcı sonuçları' : 'Son kullanıcılar' ?></h2><div class="panel-hint">Hesap ve son erişim bilgileri</div></div><a class="badge" href="users.php">Tüm kullanıcılar</a></div>
                        <?php if ($userRows === []): ?>
                            <div class="panel-body"><div class="empty">Kullanıcı bulunamadı.</div></div>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table class="table">
                                    <thead><tr><th>Hesap</th><th>Son erişim</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($userRows as $account): ?>
                                        <tr>
                                            <td><strong><a href="user_detail.php?id=<?= (int) $account['id'] ?>"><?= admin_h($account['username']) ?></a></strong><div class="muted"><?= admin_h($account['email'] ?? '') ?></div><div class="muted">#<?= (int) $account['id'] ?> · <?= admin_h(admin_date_label($account['created_at'] ?? null)) ?></div></td>
                                            <td><?php if (!empty($account['last_ip'])): ?><span class="mono"><?= admin_h($account['last_ip']) ?></span><?php else: ?><span class="muted">IP kaydı yok</span><?php endif; ?><div class="muted"><?= admin_h(admin_date_label($account['last_login'] ?? null)) ?></div><?php if (!empty($account['is_banned'])): ?><span class="badge danger">Engelli</span><?php endif; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="panel">
                        <div class="panel-head"><div><h2>Son girişler</h2><div class="panel-hint">Saklanan IP ve yaklaşık konum kayıtları</div></div><?php if ($hasIpLogs): ?><a class="badge" href="admin_ip_logs.php">Tüm kayıtlar</a><?php endif; ?></div>
                        <div class="panel-body">
                            <?php if ($ipRows === []): ?>
                                <div class="empty">Henüz görüntülenebilir giriş kaydı bulunmuyor.</div>
                            <?php else: foreach ($ipRows as $login): ?>
                                <article class="row">
                                    <div class="row-top"><div class="row-title"><?= admin_h($login['username'] ?? 'Kullanıcı') ?></div><?php if (!empty($login['is_vpn'])): ?><span class="badge danger">Proxy/VPN işareti</span><?php endif; ?></div>
                                    <div class="row-meta"><span class="mono"><?= admin_h($login['ip'] ?? 'IP yok') ?></span><span><?= admin_h(admin_date_label($login['logged_at'] ?? null)) ?></span></div>
                                    <?php $location = array_filter([$login['city'] ?? null, $login['country'] ?? null]); ?>
                                    <?php if ($location !== [] || !empty($login['isp'])): ?><div class="row-meta"><span><?= admin_h(implode(', ', $location)) ?></span><?php if (!empty($login['isp'])): ?><span><?= admin_h($login['isp']) ?></span><?php endif; ?></div><?php endif; ?>
                                </article>
                            <?php endforeach; endif; ?>
                        </div>
                    </section>

                    <?php if ($hasModerationLogs): ?>
                        <section class="panel">
                            <div class="panel-head"><div><h2>Moderasyon hareketleri</h2><div class="panel-hint">Son içerik denetimi kayıtları</div></div></div>
                            <div class="panel-body">
                                <?php if ($moderationRows === []): ?><div class="empty">Kayıt bulunmuyor.</div>
                                <?php else: foreach ($moderationRows as $log): ?>
                                    <article class="row"><div class="row-title"><?= admin_h($log['username'] ?? 'Kullanıcı') ?></div><div class="preview"><?= admin_h($log['reason'] ?? '') ?></div><div class="row-meta"><?= admin_h(admin_date_label($log['created_at'] ?? null)) ?></div></article>
                                <?php endforeach; endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
            <p class="privacy">IP adresleri ve yaklaşık konum bilgileri kişisel veridir. Konum doğruluğu IP sağlayıcısına bağlıdır; bu panel IP adreslerini harici konum servisine göndermeden yalnızca veritabanında kayıtlı bilgileri gösterir.</p>
            <p class="privacy">DM mesaj sayısı yalnızca toplu istatistik olarak gösterilir; özel mesaj içerikleri bu panelde açılmaz.</p>
        </main>
    </div>
</div>
</body>
</html>
