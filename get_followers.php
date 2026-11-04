<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require 'includes/config.php'; // Burada $pdo var

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['error' => 'Geçersiz kullanıcı ID']);
    exit;
}

$user_id = (int)$_GET['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.profile_photo
        FROM followers f
        JOIN users u ON f.follower_id = u.id
        WHERE f.followed_id = ?
        ORDER BY f.id DESC
    ");
    $stmt->execute([$user_id]);
    $followers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$followers) $followers = [];

    echo json_encode($followers);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
