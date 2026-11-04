<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    exit('İstek doğrulanamadı.');
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$school = trim($_POST['school'] ?? '');
$grade = trim($_POST['grade_level'] ?? '');

if (!$username || !$email || !$password) {
    $_SESSION['register_error'] = 'Lütfen tüm zorunlu alanları doldurun.';
    header('Location: ../register.php');
    exit;
}

// Aynı e-posta veya kullanıcı adı var mı kontrol et
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$stmt->execute([$email, $username]);
if ($stmt->rowCount() > 0) {
    $_SESSION['register_error'] = 'Bu e-posta veya kullanıcı adı zaten kullanılıyor.';
    header('Location: ../register.php');
    exit;
}

// Şifreyi güvenli şekilde hashle
$hash = password_hash($password, PASSWORD_DEFAULT);

// Yeni kullanıcıyı ekle
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, school, grade_level) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $email, $hash, $school, $grade]);

// Oturum aç
session_regenerate_id(true);
$_SESSION['user_id'] = $pdo->lastInsertId();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Anasayfaya yönlendir
header('Location: ../index.php');
exit;
