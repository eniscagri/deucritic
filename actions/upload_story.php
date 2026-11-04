<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    die('❌ Geçersiz istek (CSRF koruması devrede).');
}

$user_id = $_SESSION['user_id'];
$text_content = trim($_POST['text_content'] ?? '');
$upload_dir = realpath(__DIR__ . '/../uploads/stories');

if (!$upload_dir || !is_dir($upload_dir)) {
    mkdir(__DIR__ . '/../uploads/stories', 0755, true);
    $upload_dir = realpath(__DIR__ . '/../uploads/stories');
}

$media_path = null;
$media_type = null;

try {
    if (!empty($_FILES['story_media']) && $_FILES['story_media']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['story_media'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);

        $allowed_images = ['image/jpeg','image/png','image/gif','image/webp'];
        $allowed_videos = ['video/mp4','video/webm','video/ogg'];
        $allowed_types = array_merge($allowed_images, $allowed_videos);

        $max_size = 20 * 1024 * 1024; // 20 MB sınır
        $mime_extensions = [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
            'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogv',
        ];

        if ($f['size'] > $max_size) {
            throw new RuntimeException('Dosya çok büyük (maksimum 20 MB).');
        }

        if (!in_array($mime, $allowed_types, true) || !isset($mime_extensions[$mime]) || !is_uploaded_file($f['tmp_name'])) {
            throw new RuntimeException('Yalnızca resim veya video yükleyebilirsiniz.');
        }

        if (in_array($mime, $allowed_images, true) && @getimagesize($f['tmp_name']) === false) {
            throw new RuntimeException('Geçerli bir görsel dosyası yükleyin.');
        }

        $media_type = in_array($mime, $allowed_images, true) ? 'image' : 'video';

        $filename = sprintf('story_%s_%s.%s', $user_id, bin2hex(random_bytes(16)), $mime_extensions[$mime]);
        $target = $upload_dir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($f['tmp_name'], $target)) {
            throw new RuntimeException('Yükleme başarısız oldu.');
        }

        $media_path = 'uploads/stories/' . basename($filename);
    }

    if (empty($media_path) && empty($text_content)) {
        throw new RuntimeException('Lütfen bir medya dosyası veya metin girin.');
    }

    $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));

    $stmt = $pdo->prepare("
        INSERT INTO stories (user_id, media_path, media_type, text_content, expires_at)
        VALUES (:uid, :path, :type, :text, :exp)
    ");
    $stmt->execute([
        ':uid' => $user_id,
        ':path' => $media_path,
        ':type' => $media_type,
        ':text' => $text_content,
        ':exp'  => $expires_at
    ]);

    $_SESSION['flash_success'] = '✅ Story başarıyla yüklendi!';
    header('Location: ../index.php');
    exit;

} catch (RuntimeException $e) {
    // ❌ Hata mesajını oturuma ekle
    $_SESSION['flash_error'] = '❌ ' . $e->getMessage();
    header('Location: ../index.php');
    exit;
} catch (Exception $e) {
    $_SESSION['flash_error'] = '⚠️ Beklenmedik bir hata oluştu.';
    header('Location: ../index.php');
    exit;
}
