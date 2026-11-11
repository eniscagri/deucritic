<?php
require_once __DIR__ . '/admin_init.php';
require_admin();

if (admin_table_exists($pdo, 'user_ip_logs')) {
    header('Location: admin_ip_logs.php');
    exit;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Giriş kayıtları | Deucritic Admin</title></head>
<body>
    <main>
        <h1>Giriş kayıtları</h1>
        <p>Bu veritabanında ayrıntılı IP giriş kaydı bulunmuyor. Kullanıcıların son IP bilgileri varsa <a href="index.php">yönetim özetinde</a> görüntülenebilir.</p>
    </main>
</body>
</html>
