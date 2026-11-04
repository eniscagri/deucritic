<?php
require_once 'includes/config.php';

$message = "";
$resetLink = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if ($csrfToken !== $_SESSION['csrf_token']) {
        $message = "Geçersiz işlem. Sayfayı yenileyip tekrar dene.";
    } elseif (empty($email)) {
        $message = "E-posta alanını rica etsem boş bırakmaz mısın?";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Geçerli bir e-posta gir dayım";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = "Eğer bu e-posta ile kayıtlıysan, sana sıfırlama bağlantısı gönderdik (veya yakında göndeririz). Hesabın yok gibiyse bize yaz; yardımcı olalım 😭";
        } else {
            $token = bin2hex(random_bytes(32));
            $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

            $del = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid");
            $del->execute([':uid' => $user['id']]);

            $ins = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :token, :exp)");
            $ins->execute([
                ':uid' => $user['id'],
                ':token' => $token,
                ':exp' => $expiresAt
            ]);

            $siteURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $resetLink = $siteURL . "/reset.php?email=" . urlencode($user['email']) . "&token=" . urlencode($token);

            $message = "Harika! Şifre sıfırlama linkin hazır.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DeuCritic | Şifreni Mi Unuttun?</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md relative overflow-hidden">
    <div class="absolute -top-16 -left-16 w-32 h-32 bg-primary/20 rounded-full animate-bounce-slow"></div>
    <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-secondary/20 rounded-full animate-bounce-slow"></div>

    <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Şifre mi unuttun?</h1>
    <p class="text-center text-gray-500 mb-6 text-sm">Merak etme, DeuCritic olarak ağlamaman için yardımcı olacağız 💌</p>

    <?php if($message): ?>
      <div class="mb-4 p-3 rounded-xl text-center <?= $resetLink ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if($resetLink): ?>
      <div class="mb-4 p-3 rounded-xl bg-yellow-50 text-yellow-800 text-sm text-center break-words">
       
        <a href="<?= htmlspecialchars($resetLink) ?>" class="text-primary underline font-semibold break-all"><?= htmlspecialchars($resetLink) ?></a>
        <div class="text-xs text-gray-500 mt-2">Link 1 saat sonra geçersiz olacaktır.</div>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-6">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div>
        <label class="block mb-1 text-sm font-semibold text-gray-700" for="email">Kayıtlı E-posta</label>
        <input type="email" name="email" id="email" required
          class="w-full border-2 border-indigo-300 p-3 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary transition shadow-sm hover:shadow-md"
          placeholder="ornek@deu.edu.tr" autocomplete="email"
          value="<?= htmlspecialchars($email) ?>">
      </div>

      <button type="submit"
        class="w-full bg-yellow-500 text-black p-3 rounded-2xl font-extrabold hover:bg-cyan-500 transition-all hover:text-white p-3 shadow-xl flex items-center justify-center gap-3 transform hover:-translate-y-1 hover:scale-105">
        Şifre Gönder 
      </button>
    </form>

    <p class="text-center text-gray-500 text-xs mt-4">
     Şifreni hatırladın mı? <a href="login.php" class="text-primary underline font-semibold">Giriş Yap</a> ya da 
      <a href="index.php" class="text-primary underline font-semibold">Baba Sayfa'ya Dön</a> 
    </p>
  </div>

  <style>
    .animate-bounce-slow { animation: bounce 4s infinite; }
    @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }
  </style>
</body>
</html>
