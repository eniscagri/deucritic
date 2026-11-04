<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
@session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, username, email, school, grade_level FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $userId = $user['id'];
        $username = $user['username'];
        $email = $user['email'];
        $school = $user['school'];
        $gradeLevel = $user['grade_level'];
    } else {
        // Kullanıcı bulunamazsa oturumu kapat
        session_destroy();
    }
} else {
    $userId = null;
    $username = 'Ziyaretçi';
}

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
if (!$isLoggedIn) {
    $error = "Giriş yapmadan istek gönderemezsiniz.";
}
    