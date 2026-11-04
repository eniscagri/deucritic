<?php
require_once 'includes/config.php';
if (!isset($_SESSION['pending_verify'])) header('Location: register.php');

$user_id = $_SESSION['pending_verify'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    if ($code) {
        $stmt = $pdo->prepare("SELECT verify_code, verify_expiry FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if ($user && $user['verify_code']===$code && strtotime($user['verify_expiry'])>time()) {
            $pdo->prepare("UPDATE users SET is_verified=1, verify_code=NULL, verify_expiry=NULL WHERE id=?")
                ->execute([$user_id]);
            unset($_SESSION['pending_verify']);
            $_SESSION['user_id'] = $user_id;
            header('Location: egg.php');
            exit;
        } else $errors[] = 'Kod yanlış veya süresi dolmuş.';
    } else $errors[] = 'Kod girin.';
}
?>

<form method="POST">
    <h2>Hesap Doğrulama</h2>
    <?php if($errors): ?>
        <div><?= implode('<br>',$errors) ?></div>
    <?php endif; ?>
    <input type="text" name="code" placeholder="6 haneli kod" required>
    <button type="submit">Doğrula</button>
</form>
