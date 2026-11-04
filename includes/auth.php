<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Eğer giriş yoksa login sayfasına yönlendir
if (!is_logged_in()) {
    redirect('login.php');
}

// Aktif kullanıcı bilgilerini değişkene ata
$currentUser = current_user($pdo);
