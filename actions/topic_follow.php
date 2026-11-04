<?php


require_once '../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

function send_json($success, $message = '', $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

if(!is_logged_in()){
    send_json(false, 'Giriş yapmalısın');
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if(!$csrf || !hash_equals($_SESSION['csrf_token'], $csrf)){
    send_json(false, 'CSRF doğrulaması başarısız');
}

$topic_id = intval($_POST['topic_id'] ?? 0);
if($topic_id <= 0){
    send_json(false, 'Geçersiz konu');
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM topic_followers WHERE topic_id=? AND user_id=?");
    $stmt->execute([$topic_id, $user_id]);
    $is_following = $stmt->fetchColumn() > 0;

    if($is_following){
        $stmt = $pdo->prepare("DELETE FROM topic_followers WHERE topic_id=? AND user_id=?");
        $stmt->execute([$topic_id, $user_id]);
        $is_following = false;
    } else {
        $stmt = $pdo->prepare("INSERT INTO topic_followers(topic_id, user_id, followed_at) VALUES(?, ?, NOW())");
        $stmt->execute([$topic_id, $user_id]);
        $is_following = true;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM topic_followers WHERE topic_id=?");
    $stmt->execute([$topic_id]);
    $follower_count = $stmt->fetchColumn();

    send_json(true, $is_following ? 'Takip edildi' : 'Takip bırakıldı', [
        'following' => $is_following,
        'follower_count' => $follower_count
    ]);

} catch(Exception $ex){
    send_json(false, 'Veritabanı hatası: '.$ex->getMessage());
}
