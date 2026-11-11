<?php
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

function like_response(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    like_response(405, ['status' => 'error', 'message' => 'Bu işlem yalnızca POST isteğiyle yapılabilir.']);
}

if (empty($_SESSION['user_id'])) {
    like_response(401, ['status' => 'error', 'message' => 'Beğenmek için giriş yapmalısın.']);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrf) || $csrf === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $csrf)) {
    like_response(403, ['status' => 'error', 'message' => 'Oturum doğrulanamadı. Sayfayı yenileyip tekrar dene.']);
}

$data = json_decode(file_get_contents('php://input'), true);
$entryId = filter_var(is_array($data) ? ($data['entry_id'] ?? null) : null, FILTER_VALIDATE_INT);
if (!$entryId || $entryId < 1) {
    like_response(422, ['status' => 'error', 'message' => 'Geçersiz onep.']);
}

$userId = (int) $_SESSION['user_id'];
try {
    $entryCheck = $pdo->prepare('SELECT id FROM entries WHERE id = ?');
    $entryCheck->execute([$entryId]);
    if (!$entryCheck->fetchColumn()) {
        like_response(404, ['status' => 'error', 'message' => 'Onep bulunamadı.']);
    }

    $stmt = $pdo->prepare('SELECT id FROM entry_likes WHERE entry_id = ? AND user_id = ?');
    $stmt->execute([$entryId, $userId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare('DELETE FROM entry_likes WHERE id = ?')->execute([$existing['id']]);
        $action = 'unliked';
    } else {
        $pdo->prepare('INSERT INTO entry_likes (entry_id, user_id) VALUES (?, ?)')->execute([$entryId, $userId]);
        $action = 'liked';
    }

    $newCount = $pdo->prepare('SELECT COUNT(*) FROM entry_likes WHERE entry_id = ?');
    $newCount->execute([$entryId]);
    like_response(200, ['status' => 'success', 'action' => $action, 'new_count' => (int) $newCount->fetchColumn()]);
} catch (PDOException $exception) {
    error_log('Entry like failed: ' . $exception->getMessage());
    like_response(500, ['status' => 'error', 'message' => 'Beğeni kaydedilemedi. Lütfen tekrar dene.']);
}