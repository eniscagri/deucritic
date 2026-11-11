<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if(!is_logged_in()){
    echo json_encode(['success'=>false,'error'=>'Giriş yapmalısın']);
    exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if(!$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)){
    echo json_encode(['success'=>false,'error'=>'CSRF doğrulaması başarısız']);
    exit;
}

$entry_id = intval($_POST['entry_id'] ?? 0);
if($entry_id <= 0){
    echo json_encode(['success'=>false,'error'=>'Geçersiz entry']);
    exit;
}

try {
    // Beğeni var mı kontrol
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entry_likes WHERE entry_id=? AND user_id=?");
    $stmt->execute([$entry_id, $_SESSION['user_id']]);
    $liked = $stmt->fetchColumn() > 0;

    if($liked){
        $stmt = $pdo->prepare("DELETE FROM entry_likes WHERE entry_id=? AND user_id=?");
        $stmt->execute([$entry_id, $_SESSION['user_id']]);
        $liked = false;
    } else {
        $stmt = $pdo->prepare("INSERT INTO entry_likes(entry_id, user_id, created_at) VALUES(?, ?, NOW())");
        $stmt->execute([$entry_id, $_SESSION['user_id']]);
        $liked = true;
    }

    // Like sayısı
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entry_likes WHERE entry_id=?");
    $stmt->execute([$entry_id]);
    $like_count = (int)$stmt->fetchColumn();

    echo json_encode(['success'=>true,'liked'=>$liked,'like_count'=>$like_count]);

} catch(Exception $ex){
    echo json_encode(['success'=>false,'error'=>'Veritabanı hatası: '.$ex->getMessage()]);
}
