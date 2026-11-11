
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/content_moderation.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function post_entry_response(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    post_entry_response(405, ['success' => false, 'error' => 'Bu işlem yalnızca POST isteğiyle yapılabilir.']);
}

if (empty($_SESSION['user_id'])) {
    post_entry_response(401, ['success' => false, 'error' => 'Oturumun sona ermiş. Yanıtını paylaşmak için yeniden giriş yap.']);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrf) || $csrf === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $csrf)) {
    post_entry_response(403, ['success' => false, 'error' => 'Oturum doğrulanamadı. Sayfayı yenileyip tekrar dene.']);
}

$topicId = filter_var($_POST['topic_id'] ?? null, FILTER_VALIDATE_INT);
$content = trim((string) ($_POST['content'] ?? ''));
$contentLength = mb_strlen($content, 'UTF-8');
$anonymous = (isset($_POST['anonymous']) && $_POST['anonymous'] === '1') ? 1 : 0;

if (!$topicId || $contentLength < 5 || $contentLength > 500) {
    post_entry_response(422, ['success' => false, 'error' => 'Yanıt 5 ile 500 karakter arasında olmalı.']);
}

$topicStmt = $pdo->prepare('SELECT anonymous FROM topics WHERE id = ?');
$topicStmt->execute([$topicId]);
$topicIsAnonymous = $topicStmt->fetchColumn();
if ($topicIsAnonymous === false) {
    post_entry_response(404, ['success' => false, 'error' => 'Yanıtlamak istediğin konu bulunamadı.']);
}
$anonymous = ($anonymous || (bool) $topicIsAnonymous) ? 1 : 0;

$forceAnonymousStmt = $pdo->prepare('SELECT 1 FROM entries WHERE user_id = ? AND anonymous = 1 LIMIT 1');
$forceAnonymousStmt->execute([$_SESSION['user_id']]);
if ($forceAnonymousStmt->fetchColumn()) {
    $anonymous = 1;
}

if (dcFindProhibitedContent($content) !== null) {
    post_entry_response(422, ['success' => false, 'error' => 'Paylaşım küfür, hakaret veya +18 içerik içeremez.']);
}

$imagePath = null;
$uploadedImage = null;
if (isset($_FILES['image']) && (int) $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];
    if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        post_entry_response(422, ['success' => false, 'error' => 'Görsel yüklenemedi. Tekrar deneyebilir veya görselsiz paylaşabilirsin.']);
    }

    if ((int) $file['size'] <= 0 || (int) $file['size'] > 2 * 1024 * 1024) {
        post_entry_response(422, ['success' => false, 'error' => 'Görsel boyutu en fazla 2 MB olabilir.']);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        error_log('Unable to initialize fileinfo for topic image upload.');
        post_entry_response(500, ['success' => false, 'error' => 'Görsel şu anda doğrulanamıyor. Lütfen daha sonra tekrar dene.']);
    }
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowedMimes[$mime])) {
        post_entry_response(422, ['success' => false, 'error' => 'Yalnızca JPEG, PNG veya WEBP görseli ekleyebilirsin.']);
    }

    $uploadDir = __DIR__ . '/../uploads/onepler/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        error_log('Unable to create topic image upload directory.');
        post_entry_response(500, ['success' => false, 'error' => 'Görsel yükleme alanı şu anda kullanılamıyor.']);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];
    $uploadedImage = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $uploadedImage)) {
        post_entry_response(500, ['success' => false, 'error' => 'Görsel kaydedilemedi. Lütfen tekrar dene.']);
    }
    $imagePath = '/uploads/onepler/' . $filename;
}

$link = null;
if (preg_match('/\b((https?:\/\/|www\.)[^\s<]+)/i', $content, $match)) {
    $candidate = rtrim($match[1], '.,;:!?)');
    $link = strpos($candidate, 'www.') === 0 ? 'http://' . $candidate : $candidate;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO entries (topic_id, user_id, content, link, image, anonymous, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$topicId, $_SESSION['user_id'], $content, $link, $imagePath, $anonymous]);
    post_entry_response(201, ['success' => true, 'entry_id' => (int) $pdo->lastInsertId()]);
} catch (PDOException $exception) {
    if ($uploadedImage !== null && is_file($uploadedImage)) {
        unlink($uploadedImage);
    }
    error_log('Topic reply failed: ' . $exception->getMessage());
    post_entry_response(500, ['success' => false, 'error' => 'Yanıt kaydedilemedi. Lütfen tekrar dene.']);
}
