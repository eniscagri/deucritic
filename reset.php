<?php
require_once 'includes/config.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$message = "";
$error = "";
$showForm = false;

// Linkten gelen verileri alıyoruz
$email_from_url = trim($_GET['email'] ?? '');
$token_from_url = trim($_GET['token'] ?? '');

// FORM GÖNDERİLDİĞİNDE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $token = trim($_POST['token'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Geçersiz işlem (CSRF). Sayfayı yenileyip tekrar dene.";
    } elseif (empty($email) || empty($token)) {
        $error = "Parametreler eksik. Lütfen maildeki linke tekrar tıkla.";
    } elseif (strlen($password) < 8) {
        $error = "Şifre en az 8 karakter olmalı.";
        $showForm = true;
    } elseif ($password !== $password_confirm) {
        $error = "Şifreler eşleşmiyor.";
        $showForm = true;
    } else {
        // Doğrulama sorgusu: recovery_email sütununu kontrol ediyoruz!
        $stmt = $pdo->prepare("
            SELECT pr.id AS pr_id, pr.user_id, pr.expires_at 
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = :token AND u.recovery_email = :email
            LIMIT 1
        ");
        $stmt->execute([':token' => $token, ':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = "Geçersiz link veya e-posta eşleşmiyor.";
        } else {
            if (new DateTime() > new DateTime($row['expires_at'])) {
                $pdo->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$row['pr_id']]);
                $error = "Bu linkin süresi dolmuş.";
            } else {
                // Şifreyi güncelle (Tablondaki sütun adın password_hash ise onu kullan)
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE id = :uid");
                $update->execute([':ph' => $new_hash, ':uid' => $row['user_id']]);

                // Token'ı yak ve imha et (Tek kullanımlık)
                $pdo->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$row['pr_id']]);
                $message = "Şifren başarıyla güncellendi! 😎 <a href='login.php' class='text-primary underline font-bold'>Giriş yapabilirsin.</a>";
            }
        }
    }
}

// SAYFA İLK AÇILDIĞINDA (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($email_from_url) || empty($token_from_url)) {
        $error = "Eksik bağlantı. Lütfen maildeki linki kontrol et.";
    } else {
        // Sorgu: Linkteki mail recovery_email sütununda var mı?
        $stmt = $pdo->prepare("
            SELECT pr.id, pr.expires_at 
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = :token AND u.recovery_email = :email
            LIMIT 1
        ");
        $stmt->execute([':token' => $token_from_url, ':email' => $email_from_url]);
        $row = $stmt->fetch();

        if ($row) {
            if (new DateTime() > new DateTime($row['expires_at'])) {
                $pdo->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$row['id']]);
                $error = "Bağlantının süresi dolmuş.";
            } else {
                $showForm = true;
            }
        } else {
            $error = "Geçersiz veya kullanılmış bağlantı. Lütfen yeni bir talep oluştur.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DeuCritic | Şifre Sıfırlama</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon">
<script>
    tailwind.config = { theme: { extend: { colors: { primary: '#b84834', secondary: '#e9c65a', dark: '#1d352f' } } } }
</script>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md relative overflow-hidden">
    <div class="absolute -top-16 -left-16 w-32 h-32 bg-primary/20 rounded-full animate-bounce-slow"></div>
    <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-secondary/20 rounded-full animate-bounce-slow"></div>

    <h1 class="text-2xl font-extrabold text-dark mb-2 text-center tracking-tight">Yeni Şifre Belirle</h1>
    <p class="text-center text-slate-500 mb-6 text-sm font-medium">Güvenliğin bizim için önemli dayım.</p>

    <?php if ($error): ?>
      <div class="mb-4 p-4 rounded-xl text-center bg-red-50 text-red-700 text-sm font-bold border border-red-100">
        <?= e($error) ?>
      </div>
      <div class="text-center">
          <a href="forgot.php" class="text-primary underline text-xs font-bold">Yeni Link İste</a>
      </div>
    <?php endif; ?>

    <?php if ($message): ?>
      <div class="mb-4 p-4 rounded-xl text-center bg-emerald-50 text-emerald-800 text-sm font-bold border border-emerald-100">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
      <form method="POST" action="" class="space-y-6" id="reset-form">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="email" value="<?= e($email_from_url) ?>">
        <input type="hidden" name="token" value="<?= e($token_from_url) ?>">

        <div>
          <label class="block mb-1 text-sm font-bold text-slate-700" for="password">Yeni Şifre</label>
          <div class="relative">
            <input id="password" name="password" type="password" required
              class="w-full border-2 border-slate-200 p-3 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition"
              placeholder="En az 8 karakter">
          </div>
        </div>

        <div>
          <label class="block mb-1 text-sm font-bold text-slate-700" for="password_confirm">Şifre (Tekrar)</label>
          <input id="password_confirm" name="password_confirm" type="password" required
            class="w-full border-2 border-slate-200 p-3 rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition"
            placeholder="Aynısını tekrar yaz">
          <p id="pw-match" class="text-xs mt-2 hidden font-bold"></p>
        </div>

        <button type="submit"
          class="w-full bg-primary text-white p-4 rounded-2xl font-extrabold text-lg hover:bg-[#963926] transition-all shadow-lg hover:-translate-y-1">
          Şifreyi Güncelle 🔐
        </button>
      </form>
    <?php endif; ?>

    <p class="text-center text-slate-400 text-xs mt-8">
       <a href="index.php" class="hover:text-primary transition underline font-medium">Anasayfa'ya Dön</a>
    </p>
  </div>

  <style>
    .animate-bounce-slow { animation: bounce 4s infinite; }
    @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }
  </style>

  <script>
    // JS şifre eşleşme kontrolü (Kodun daha profesyonel durması için)
    const pw = document.getElementById('password');
    const pwc = document.getElementById('password_confirm');
    const pwMatch = document.getElementById('pw-match');

    if(pw && pwc) {
        [pw, pwc].forEach(el => el.addEventListener('input', () => {
            if(!pw.value || !pwc.value) { pwMatch.classList.add('hidden'); return; }
            pwMatch.classList.remove('hidden');
            if(pw.value === pwc.value) {
                pwMatch.textContent = "Şifreler eşleşti ✅";
                pwMatch.className = "text-xs mt-2 font-bold text-emerald-600";
            } else {
                pwMatch.textContent = "Şifreler farklı ❌";
                pwMatch.className = "text-xs mt-2 font-bold text-red-600";
            }
        }));
    }
  </script>
</body>
</html>