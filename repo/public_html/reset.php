<?php
require_once 'includes/config.php';

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$message = "";
$error = "";
$showForm = false;
$email = trim($_GET['email'] ?? '');
$token = trim($_GET['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $token = trim($_POST['token'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($csrf !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Geçersiz işlem (CSRF). Sayfayı yenileyip tekrar dene.";
    } elseif (empty($email) || empty($token)) {
        $error = "Eksik parametreler. Linki kontrol et veya tekrar istekte bulun.";
    } elseif (strlen($password) < 8) {
        $error = "Şifre en az 8 karakter olmalı — DeuCritic bile sınır koydu.";
    } elseif ($password !== $password_confirm) {
        $error = "Şifreler eşleşmiyor. İkinci şifreyi bir daha kontrol et.";
    } else {
        $stmt = $pdo->prepare("
            SELECT pr.id AS pr_id, pr.user_id, pr.expires_at, u.email
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = :token AND u.email = :email
            LIMIT 1
        ");
        $stmt->execute([':token' => $token, ':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = "Geçersiz veya kullanılmamış şifre sıfırlama linki. Yeniden talep etmelisin.";
        } else {
            $now = new DateTime();
            $expires = new DateTime($row['expires_at']);
            if ($expires < $now) {
                $del = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
                $del->execute([':id' => $row['pr_id']]);
                $error = "Bu linkin süresi dolmuş. Yeni bir şifre sıfırlama isteği oluştur.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $update = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE id = :uid");
                $update->execute([':ph' => $password_hash, ':uid' => $row['user_id']]);

                $del = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
                $del->execute([':id' => $row['pr_id']]);

                $message = "Tebrikler! Şifren başarıyla güncellendi. Artık <a href=\"login.php\" class=\"text-primary underline font-semibold\">giriş yap</a>abilirsin. DeuCritic seni bekliyor 😎";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($email) || empty($token)) {
        $error = "Eksik veya hatalı bağlantı. Reset linkini kontrol et.";
    } else {
        $stmt = $pdo->prepare("
            SELECT pr.id AS pr_id, pr.user_id, pr.expires_at, u.email
            FROM password_resets pr
            JOIN users u ON u.id = pr.user_id
            WHERE pr.token = :token AND u.email = :email
            LIMIT 1
        ");
        $stmt->execute([':token' => $token, ':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = "Geçersiz veya zaten kullanılmış link. Yeni bir şifre sıfırlama isteği oluştur.";
        } else {
            $now = new DateTime();
            $expires = new DateTime($row['expires_at']);
            if ($expires < $now) {
                $del = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
                $del->execute([':id' => $row['pr_id']]);
                $error = "Üzgünüz, bu linkin süresi dolmuş. Yeni bir şifre sıfırlama isteğinde bulun.";
            } else {
                $showForm = true;
            }
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
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md relative overflow-hidden">
    <div class="absolute -top-16 -left-16 w-32 h-32 bg-primary/20 rounded-full animate-bounce-slow"></div>
    <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-secondary/20 rounded-full animate-bounce-slow"></div>

    <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Şifre Sıfırlama</h1>
    <p class="text-center text-gray-500 mb-6 text-sm">
      DGS: DeuCritic Güvenlik Servisi.
    </p>

    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded-xl text-center bg-red-100 text-red-700">
        <?= e($error) ?>
      </div>
      <p class="text-center text-xs text-gray-500 mb-4">
        İstersen <a href="forgot.php" class="text-primary underline font-semibold">yeni şifre talebi</a> oluştur veya bize <a href="mailto:destek@DeuCritic.com" class="text-primary underline">mail</a> at.
      </p>
    <?php endif; ?>

    <?php if ($message): ?>
      <div class="mb-4 p-3 rounded-xl text-center bg-green-100 text-green-700">
        <?= $message  ?>
      </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
      <form method="POST" action="" class="space-y-6" id="reset-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div>
          <label class="block mb-1 text-sm font-semibold text-gray-700" for="password">Yeni Şifre</label>
          <div class="relative">
            <input id="password" name="password" type="password" required
              class="w-full border-2 border-indigo-300 p-3 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary transition shadow-sm pr-10" disabled
              placeholder="En az 8 karakter">
            <button type="button" id="toggle-password" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary focus:outline-none" title="Şifreyi göster/gizle">
              
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-1">Güçlü bir şifre kullan: en az 8 karakter, rakam ve harf karışımı iyi olur.</p>
        </div>

        <div>
          <label class="block mb-1 text-sm font-semibold text-gray-700" for="password_confirm">Şifre (Tekrar)</label>
          <input id="password_confirm" name="password_confirm" type="password" required disabled
            class="w-full border-2 border-indigo-300 p-3 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary transition shadow-sm"
            placeholder="Aynısını tekrar yaz">
          <p id="pw-match" class="text-xs mt-1 hidden"></p>
        </div>

        <button  type="submit"
          class="w-full bg-cyan-500 text-white p-3 rounded-2xl font-extrabold hover:bg-cyan-500 transition-all shadow-xl flex items-center justify-center gap-3 transform hover:-translate-y-1 hover:scale-105">
          Yeni Şifreyi Kaydet 🔐
        </button>
      </form>
    <?php endif; ?>

    <p class="text-center text-gray-500 text-xs mt-4">
      Hâlâ takılıyorsan bize <a href="mailto:eniscagrigilik1@gmail.com" class="text-primary underline font-semibold">mail at</a> — DeuCritic yardıma koşar.
    </p>
    <p class="text-center text-gray-500 text-xs mt-4">
       <a href="/index.php" class="text-primary underline font-semibold">Baba Sayfa'ya</a> dön.
    </p>
  </div>

  <style>
    .animate-bounce-slow { animation: bounce 4s infinite; }
    @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }
  </style>

  <script>
    (function(){
      const toggle = document.getElementById('toggle-password');
      const pw = document.getElementById('password');
      const pwc = document.getElementById('password_confirm');
      const eye = document.getElementById('eye-icon');
      const pwMatch = document.getElementById('pw-match');

      if (toggle && pw && pwc) {
        toggle.addEventListener('click', () => {
          if (pw.type === 'password') {
            pw.type = 'text';
            pwc.type = 'text';
            eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10a9.963 9.963 0 012.175-6.125M21.825 13.875A9.963 9.963 0 0122 12c0-5.523-4.477-10-10-10-1.78 0-3.44.48-4.875 1.325"/>';
          } else {
            pw.type = 'password';
            pwc.type = 'password';
            eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
          }
        });

        function checkMatch() {
          if (!pw.value && !pwc.value) {
            pwMatch.className = 'text-xs mt-1 hidden';
            pwMatch.textContent = '';
            return;
          }
          if (pw.value === pwc.value) {
            pwMatch.className = 'text-xs mt-1 text-green-600';
            pwMatch.textContent = 'Şifreler eşleşiyor ✅';
          } else {
            pwMatch.className = 'text-xs mt-1 text-red-600';
            pwMatch.textContent = 'Şifreler farklı ❌';
          }
        }

        pw.addEventListener('input', checkMatch);
        pwc.addEventListener('input', checkMatch);
      }

      const form = document.getElementById('reset-form');
      if (form) {
        form.addEventListener('submit', (e) => {
          if (pw.value.length < 8) {
            e.preventDefault();
alert("Şu anda şifre sıfırlama sistemi geçici olarak kullanımda değil. Ancak endişelenme, bize kısa bir e-posta gönderirsen sana hızlıca şifre sıfırlama linki sağlayabiliriz.");
          }
          if (pw.value !== pwc.value) {
            e.preventDefault();
            alert('Şifreler eşleşmiyor.');
          }
        });
      }
    })();
  </script>
</body>
</html>
