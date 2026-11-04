<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['count' => 0]);
  exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE (user_id = :uid OR is_global = 1) AND is_read = 0");
    $stmt->execute(['uid' => $user_id]);
    $count = (int)$stmt->fetchColumn();
    
    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}