<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$logged_user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Geçersiz istek!');
    }

    $target_id = (int) ($_POST['target_id'] ?? 0);
    if ($target_id <= 0) {
        die('Geçersiz kullanıcı.');
    }

    if ($target_id === $logged_user_id) {
        die('Kendinizi takip edemezsiniz.');
    }

    try {
        // Takip durumu kontrol
        $stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id=? AND followed_id=?");
        $stmt->execute([$logged_user_id, $target_id]);

        if ($stmt->rowCount() > 0) {
            // Zaten takip ediliyor, takipten çıkar
            $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id=? AND followed_id=?");
            $stmt->execute([$logged_user_id, $target_id]);
        } else {
            // Takip et
            $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$logged_user_id, $target_id]);
        }

        // Profil sayfasına geri yönlendir
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id=?");
        $stmt->execute([$target_id]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($target_user) {
            header("Location: profile.php?user=" . urlencode($target_user['username']));
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Bir hata oluştu: " . e($e->getMessage()));
    }
} else {
    header("Location: index.php");
    exit;
}
