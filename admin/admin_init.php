<?php
// admin_init.php
// Ortak başlatıcı: oturum, PDO, CSRF, yetki ve IP-lookup helper'ları.

session_start();
header('Content-Type: text/html; charset=utf-8');

// === CONFIG: burayı kendi değerlerinle değiştir ===
$db_host = 'localhost';
$db_name = 'deucditiccom_deucritic';
$db_user = 'deucditiccom_eniscagri';
$db_pass = 'Enis07654607';

// Admin şifresi (örnek). Production: bunu sabit yazma; env/deployment ile yönet.
if (!defined('ADMIN_PASSWORD_HASH')) {
    define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));
}

// === PDO ---
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    // Prod: kullanıcıya genel mesaj ver, logla
    die("DB Connection failed: " . $e->getMessage());
}

// === CSRF ===
if (!isset($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
}
function csrf_token() { return $_SESSION['admin_csrf']; }
function csrf_ok($token) { return isset($token, $_SESSION['admin_csrf']) && hash_equals($_SESSION['admin_csrf'], $token); }

// === require admin ===
function require_admin() {
    if (empty($_SESSION['admin_logged'])) {
        header('Location: admin_login.php');
        exit;
    }
}

// === simple file cache for IP lookups ===
// cache dir (ensure writable)
$IP_CACHE_DIR = __DIR__ . '/cache_ip';
if (!is_dir($IP_CACHE_DIR)) mkdir($IP_CACHE_DIR, 0755, true);

// get ip info with cache. returns associative array: country, city, isp, lat, lon, proxy/hosting flags
function ip_lookup_cached(string $ip, int $ttl_seconds = 86400) {
    global $IP_CACHE_DIR;
    $safe = preg_replace('/[^a-z0-9\._-]/i','', $ip);
    $file = "$IP_CACHE_DIR/$safe.json";

    if (file_exists($file)) {
        $age = time() - filemtime($file);
        if ($age < $ttl_seconds) {
            $json = @file_get_contents($file);
            if ($json) {
                $data = json_decode($json, true);
                if ($data) return $data;
            }
        }
    }

    // ip-api.com free endpoint
    $url = "http://ip-api.com/json/".urlencode($ip)."?fields=status,country,city,isp,lat,lon,proxy,hosting,query,message";
    $opts = ['http' => ['timeout' => 5]];
    $ctx = stream_context_create($opts);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) {
        $data = ['status'=>'fail','message'=>'no-response'];
    } else {
        $data = json_decode($json, true);
        if (!$data) $data = ['status'=>'fail','message'=>'json-error'];
    }
    // Cache the raw response
    @file_put_contents($file, json_encode($data));
    return $data;
}

// === check if ip is banned ===
function ip_is_banned($pdo, $ip) {
    $stmt = $pdo->prepare("SELECT 1 FROM banned_ips WHERE ip = :ip LIMIT 1");
    $stmt->execute([':ip'=>$ip]);
    return (bool)$stmt->fetchColumn();
}

// === log IP on successful login ===
function log_user_ip($pdo, $user_id, $ip, $user_agent = '') {
    // lookup
    $info = ip_lookup_cached($ip);
    $country = $info['country'] ?? null;
    $city = $info['city'] ?? null;
    $isp = $info['isp'] ?? null;
    $lat = isset($info['lat']) ? $info['lat'] : null;
    $lon = isset($info['lon']) ? $info['lon'] : null;
    $is_vpn = (!empty($info['proxy']) || !empty($info['hosting'])) ? 1 : 0;

    $stmt = $pdo->prepare("
        INSERT INTO user_ip_logs (user_id, ip, country, city, isp, lat, lon, is_vpn)
        VALUES (:uid,:ip,:country,:city,:isp,:lat,:lon,:is_vpn)
    ");
    $stmt->execute([
        ':uid'=>$user_id,
        ':ip'=>$ip,
        ':country'=>$country,
        ':city'=>$city,
        ':isp'=>$isp,
        ':lat'=>$lat,
        ':lon'=>$lon,
        ':is_vpn'=>$is_vpn
    ]);

    // update users.last_ip
    $stmt2 = $pdo->prepare("UPDATE users SET last_ip = :ip, last_login = NOW() WHERE id = :uid");
    $stmt2->execute([':ip'=>$ip, ':uid'=>$user_id]);

    // device tracking
    $device_hash = hash('sha256', ($user_agent ?? '') . '||' . $ip);
    $stmt3 = $pdo->prepare("SELECT id FROM login_devices WHERE device_hash = :d AND user_id = :uid LIMIT 1");
    $stmt3->execute([':d'=>$device_hash, ':uid'=>$user_id]);
    if (!$stmt3->fetch()) {
        $ins = $pdo->prepare("INSERT INTO login_devices (user_id, device_hash, ip, user_agent, is_suspicious) VALUES (:uid,:d,:ip,:ua,:is_suspicious)");
        $ins->execute([':uid'=>$user_id, ':d'=>$device_hash, ':ip'=>$ip, ':ua'=>$user_agent, ':is_suspicious'=>$is_vpn]);
    } else {
        $upd = $pdo->prepare("UPDATE login_devices SET last_seen = NOW(), ip = :ip WHERE device_hash = :d AND user_id = :uid");
        $upd->execute([':ip'=>$ip, ':d'=>$device_hash, ':uid'=>$user_id]);
    }
}
