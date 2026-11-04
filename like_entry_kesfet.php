<?php

require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if(!isset($_POST['entry_id']) || !is_numeric($_POST['entry_id'])) {
    echo json_encode(['error' => 'Geçersiz entry ID']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Giriş yapmalısınız']);
    exit;
}

$entry_id = intval($_POST['entry_id'] ?? 0);
try {
    $stmt = $pdo->prepare("SELECT likes FROM entries WHERE id = ?");
    $stmt->execute([$entry_id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        echo json_encode(['error' => 'Entry bulunamadı']);
        exit;
    }

    $likes = json_decode($entry['likes'], true) ?: [];
    
    if (in_array($userId, $likes)) {
        $likes = array_diff($likes, [$userId]);
    } else {
        $likes[] = $userId;
    }

    $stmt = $pdo->prepare("UPDATE entries SET likes = ? WHERE id = ?");
    $stmt->execute([json_encode(array_values($likes)), $entry_id]);

    echo json_encode(['success' => true, 'likes' => count($likes)]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Beklenmeyen hata: ' . $e->getMessage()]);
}


fetch('kesfet.php?ajax=1&page=2');




?>