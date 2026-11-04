<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Oturum bulunamadı']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$user_id = $data['user_id'] ?? null;

if(!$user_id) {
    echo json_encode(['status'=>'error','message'=>'Geçersiz kullanıcı ID']);
    exit;
}

$me = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id=:me AND followed_id=:user_id");
    $stmt->execute([':me'=>$me, ':user_id'=>$user_id]);
    $exists = $stmt->fetchColumn();

    if($exists) {
        $stmt = $pdo->prepare("DELETE FROM followers WHERE follower_id=:me AND followed_id=:user_id");
        $stmt->execute([':me'=>$me, ':user_id'=>$user_id]);
        $status = 'unfollowed';
    } else {
        $stmt = $pdo->prepare("INSERT INTO followers(follower_id, followed_id) VALUES(:me, :user_id)");
        $stmt->execute([':me'=>$me, ':user_id'=>$user_id]);
        $status = 'followed';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE followed_id=:user_id");
    $stmt->execute([':user_id'=>$user_id]);
    $totalFollowers = (int)$stmt->fetchColumn();

    echo json_encode(['status'=>$status,'totalFollowers'=>$totalFollowers]);

} catch(PDOException $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
