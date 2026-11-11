<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow, noarchive');

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

function admin_csrf_token(): string
{
    return $_SESSION['admin_csrf'];
}

function admin_csrf_ok($token): bool
{
    return is_string($token)
        && isset($_SESSION['admin_csrf'])
        && hash_equals($_SESSION['admin_csrf'], $token);
}

function csrf_token(): string
{
    return admin_csrf_token();
}

function csrf_ok($token): bool
{
    return admin_csrf_ok($token);
}

function admin_settings(): array
{
    static $settings = null;
    if ($settings === null) {
        $settings = $GLOBALS['deucriticPrivateConfig'] ?? [];
        $envHash = getenv('DEUCRITIC_ADMIN_PASSWORD_HASH');
        if ($envHash !== false && $envHash !== '') {
            $settings['admin_password_hash'] = $envHash;
        }
        $envIps = getenv('DEUCRITIC_ADMIN_ALLOWED_IPS');
        if ($envIps !== false && $envIps !== '') {
            $settings['admin_allowed_ips'] = $envIps;
        }
    }
    return is_array($settings) ? $settings : [];
}

function admin_password_hash(): string
{
    return trim((string) (admin_settings()['admin_password_hash'] ?? ''));
}

function admin_allowed_ips(): array
{
    $value = admin_settings()['admin_allowed_ips'] ?? [];
    if (is_string($value)) {
        $value = explode(',', $value);
    }
    if (!is_array($value)) {
        return [];
    }

    return array_values(array_filter(array_map('trim', $value)));
}

function admin_request_ip_allowed(): bool
{
    $allowedIps = admin_allowed_ips();
    if ($allowedIps === []) {
        return true;
    }

    $remoteIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if (filter_var($remoteIp, FILTER_VALIDATE_IP) === false) {
        return false;
    }

    foreach ($allowedIps as $allowedIp) {
        if (filter_var($allowedIp, FILTER_VALIDATE_IP) !== false && hash_equals($allowedIp, $remoteIp)) {
            return true;
        }
    }

    return false;
}

function require_admin(): void
{
    if (!admin_request_ip_allowed()) {
        unset($_SESSION['admin_logged'], $_SESSION['admin_last_activity']);
        http_response_code(403);
        exit('Bu ağ adresinden yönetici paneline erişilemiyor.');
    }

    $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? 0);
    if (empty($_SESSION['admin_logged']) || $lastActivity < time() - 1800) {
        unset($_SESSION['admin_logged'], $_SESSION['admin_last_activity']);
        header('Location: admin_login.php');
        exit;
    }

    $_SESSION['admin_last_activity'] = time();
}

function admin_table_exists(PDO $pdo, string $table): bool
{
    static $cache = [];
    if (!array_key_exists($table, $cache)) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $stmt->execute([$table]);
        $cache[$table] = (int) $stmt->fetchColumn() > 0;
    }
    return $cache[$table];
}

function admin_column_exists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (!array_key_exists($key, $cache)) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
        );
        $stmt->execute([$table, $column]);
        $cache[$key] = (int) $stmt->fetchColumn() > 0;
    }
    return $cache[$key];
}

function admin_table_count(PDO $pdo, string $table): int
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !admin_table_exists($pdo, $table)) {
        return 0;
    }
    return (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
}
