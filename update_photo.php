<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    exit('Geçersiz istek.');
}

if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Lütfen geçerli bir görsel seçin.');
}

$file = $_FILES['profile_photo'];
if ($file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
    http_response_code(413);
    exit('Dosya 5 MB sınırını aşamaz.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
    http_response_code(415);
    exit('Yalnızca geçerli JPG, PNG, GIF veya WEBP görselleri kabul edilir.');
}

$userId = (int) $_SESSION['user_id'];
$uploadDir = __DIR__ . '/uploads/profile_photos';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    exit('Dosya şu anda kaydedilemiyor.');
}

$filename = 'profile_' . $userId . '_' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
$relativePath = 'uploads/profile_photos/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    exit('Dosya şu anda kaydedilemiyor.');
}

try {
    $stmt = $pdo->prepare('UPDATE users SET profile_photo = ? WHERE id = ?');
    $stmt->execute([$relativePath, $userId]);
} catch (PDOException $e) {
    @unlink($targetPath);
    error_log('Profile image update failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Profil fotoğrafı güncellenemedi.');
}

header('Location: profile.php?user=' . urlencode($_SESSION['username'] ?? 'profil'));
exit;
