<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Veritabanı Bağlantısı
$db_host = 'localhost';
$db_name = 'deucditiccom_deucritic';
$db_user = 'deucditiccom_eniscagri';      
$db_pass = 'Enis07654607';          

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Veritabanı bağlantı hatası: ' . $e->getMessage());
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

        $update_seen = $pdo->prepare("UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE id = ?");
        $update_seen->execute([$userId]);
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
