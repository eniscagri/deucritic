<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['login_error'] = 'Lütfen tüm alanları doldurun.';
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    header('Location: ../index.php');
    exit;
} else {
    $_SESSION['login_error'] = 'E-posta veya şifre hatalı.';
    header('Location: ../login.php');
    exit;
}
