<?php
require 'includes/config.php';
require_once __DIR__ . '/includes/content_moderation.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store');

$respond = static function (int $status, string $message) {
    http_response_code($status);
    echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, 'Geçersiz istek.');
}

if (!isset($_SESSION['user_id'])) {
    $respond(401, 'Oturum açılmamış.');
}
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    $respond(403, 'İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.');
}

$sender = (int) $_SESSION['user_id'];
$receiver = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
$convo = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$msg = trim((string) ($_POST['message'] ?? ''));

if (!$receiver || !$convo || $receiver === $sender || mb_strlen($msg, 'UTF-8') > 5000) {
    $respond(400, 'Geçersiz veri.');
}

$blocked = $pdo->prepare('SELECT 1 FROM user_blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?) LIMIT 1');
$blocked->execute([$sender, $receiver, $receiver, $sender]);
if ($blocked->fetchColumn()) {
    $respond(403, 'Bu kullanıcıyla mesajlaşamazsınız.');
}

$moderationCategory = dcFindProhibitedContent($msg);
if ($moderationCategory !== null) {
    $respond(422, 'Mesaj küfür, hakaret veya +18 içerik içeremez.');
}

$membership = $pdo->prepare('SELECT COUNT(DISTINCT user_id) FROM conversation_users WHERE conversation_id = ? AND user_id IN (?, ?)');
$membership->execute([$convo, $sender, $receiver]);
if ((int) $membership->fetchColumn() !== 2) {
    $respond(403, 'Bu sohbete mesaj gönderme yetkiniz yok.');
}

$image_tag = '';

if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK || $_FILES['photo']['size'] > 10 * 1024 * 1024 || !is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $respond(413, 'Görsel yüklenemedi veya 10 MB sınırını aşıyor.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['photo']['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || @getimagesize($_FILES['photo']['tmp_name']) === false) {
        $respond(415, 'Yalnızca geçerli JPG, PNG, GIF veya WEBP görselleri kabul edilir.');
    }

    $upload_dir = __DIR__ . '/uploads/messages';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        $respond(500, 'Görsel şu anda kaydedilemiyor.');
    }
    $new_filename = 'message_' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
        $respond(500, 'Görsel şu anda kaydedilemiyor.');
    }
    $image_tag = '[IMAGE]uploads/messages/' . $new_filename . '[/IMAGE]';
}

if (empty($msg) && empty($image_tag)) {
    $respond(400, 'Boş mesaj gönderilemez.');
}

$final_message = $msg;
if (!empty($image_tag)) {
    $final_message = empty($msg) ? $image_tag : $msg . "\n<br>\n" . $image_tag;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$convo, $sender, $receiver, $final_message]);

    echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log('Message save failed: ' . $e->getMessage());
    $respond(500, 'Mesaj kaydedilirken bir hata oluştu.');
}
