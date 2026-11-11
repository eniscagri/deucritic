<?php

ob_start();

error_reporting(E_ALL);
ini_set('log_errors', '1');
$isLocalEnvironment = strtolower((string) (getenv('APP_ENV') ?: 'production')) === 'local';
ini_set('display_errors', $isLocalEnvironment ? '1' : '0');

require_once __DIR__ . '/session.php';

// Keep database secrets outside the document root and out of Git.
$privateConfigPath = getenv('DEUCRITIC_CONFIG_FILE') ?: '';
$configCandidates = array_filter([
    $privateConfigPath,
    __DIR__ . '/config.local.php',
    '/home/deucditiccom/private/deucritic.php',
]);
$dbConfig = null;
foreach ($configCandidates as $candidate) {
    if (is_file($candidate) && is_readable($candidate)) {
        $dbConfig = require $candidate;
        break;
    }
}

if (!is_array($dbConfig)) {
    $dbConfig = [
        'host' => getenv('DEUCRITIC_DB_HOST'),
        'name' => getenv('DEUCRITIC_DB_NAME'),
        'user' => getenv('DEUCRITIC_DB_USER'),
        'pass' => getenv('DEUCRITIC_DB_PASS'),
    ];
}

$GLOBALS['deucriticPrivateConfig'] = $dbConfig;

$db_host = trim((string) ($dbConfig['host'] ?? ''));
$db_name = trim((string) ($dbConfig['name'] ?? ''));
$db_user = trim((string) ($dbConfig['user'] ?? ''));
$db_pass = (string) ($dbConfig['pass'] ?? '');
if ($db_host === '' || $db_name === '' || $db_user === '' || $db_pass === '') {
    error_log('Deucritic database configuration is missing.');
    http_response_code(503);
    exit('Site yapılandırması tamamlanmadı. Lütfen daha sonra tekrar deneyin.');
}

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Deucritic database connection failed: ' . $e->getMessage());
    http_response_code(503);
    exit('Şu anda bağlantı sağlanamıyor. Lütfen daha sonra tekrar deneyin.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
$userId = null;
$username = 'Ziyaretçi';
$streakCount = 0;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT id, username, email, school, grade_level, streak_count FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = $user['id'];
        $username = $user['username'];
        $email = $user['email'];
        $school = $user['school'];
        $gradeLevel = $user['grade_level'];
        $streakCount = $user['streak_count'];

        if (empty($_SESSION['last_seen_update']) || time() - (int) $_SESSION['last_seen_update'] >= 300) {
            $update_seen = $pdo->prepare("UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE id = ?");
            $update_seen->execute([$userId]);
            $_SESSION['last_seen_update'] = time();
        }
    } else {
        session_unset();
        session_destroy();
        $isLoggedIn = false;
    }
}

if (!function_exists('updateStreak')) {
    function updateStreak($pdo, $uid) {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $stmt = $pdo->prepare("SELECT last_post_date, streak_count FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $userData = $stmt->fetch();

        if (!$userData) return;

        if ($userData['last_post_date'] == $today) {
            return;
        } elseif ($userData['last_post_date'] == $yesterday) {
            $new_streak = $userData['streak_count'] + 1;
        } else {
            $new_streak = 1;
        }

        $update = $pdo->prepare("UPDATE users SET streak_count = ?, last_post_date = ? WHERE id = ?");
        $update->execute([$new_streak, $today, $uid]);
    }
}
