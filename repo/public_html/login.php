<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'E-posta ya da şifre yanlış. Tekrar dene!';
        }
    } else {
        $error = 'Hadi, tüm alanları doldur da devam edelim.';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Deucritic - Giriş Yap</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon">
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#44b5d5',
        secondary: '#f3cc8b',
      }
    }
  }
}
</script>
<style>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  75% { transform: translateX(-5px); }
}
.animate-shake { animation: shake 0.4s; }
</style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-blue-100 to-indigo-50 min-h-screen text-gray-800">

<section class="flex items-center justify-center px-4 py-16 min-h-screen bg-gradient-to-br from-indigo-100 via-white to-blue-100">
  <div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl p-10 relative overflow-hidden">

    <!-- Dekoratif animasyonlar -->
    <div class="absolute -top-24 -left-24 w-52 h-52 bg-indigo-300 rounded-full opacity-20 animate-ping"></div>
    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-blue-300 rounded-full opacity-10 animate-ping"></div>

    <div class="text-center mb-10 relative z-10">
      <h1 class="text-4xl md:text-5xl font-extrabold text-indigo-700 mb-4">
        🎭 <span class="text-primary font-black">Deucritic</span>'e Hoş Geldin!
      </h1>
      <p class="text-gray-600 text-lg leading-relaxed">
        Burada <span class="font-semibold text-indigo-600">gerçekmiş gibi görünen ama uyduruk hikayelerden</span>, sıradan itiraflara, <span class="italic text-indigo-500">dramadan dedikoduya</span> her şey yazılabilir.
      </p>
    </div>

    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-8 text-base z-10 relative">
      <li>👀 Derslerde kim ne yapıyor, öğrenmek serbest.</li>
      <li>🕶️ Anonim kalmak mı? Rahat ol, kimse peşinde değil.</li>
      <li>💔 Eski sevgilini stalklamak için buton yok, ama yollar var...</li>
      <li>🧠 Dersle veya hayatla ilgili ne varsa, paylaşabilirsin.</li>
      <li>🔥 Eleştir, öv, anlat. Burada kimse sahte cool değil.</li>
    </ul>

    <div class="flex flex-col sm:flex-row gap-4 z-10 relative">
      <a href="register.php" class="flex items-center justify-center gap-2 bg-indigo-700 text-white py-3 rounded-xl font-semibold shadow-lg hover:bg-indigo-800 transition text-center w-full sm:w-1/2">
        🚀 Kayıt Ol ve Kaosunu Paylaş
      </a>
      <a href="#login" class="flex items-center justify-center gap-2 border-2 border-indigo-700 text-indigo-700 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition text-center w-full sm:w-1/2">
        🔐 Zaten Üyeyim
      </a>
    </div>

    
  </div>
</section>
<section id="login" class="flex items-center justify-center px-4 py-16 min-h-screen bg-gradient-to-br from-indigo-100 via-blue-200 to-indigo-100">
  <div class="max-w-md mx-auto bg-white/90 backdrop-blur-lg p-10 rounded-3xl shadow-2xl border border-indigo-300 relative overflow-hidden">
    

    <div class="flex flex-col items-center mb-8 relative z-10">
    
      <h1 class="text-3xl font-extrabold mb-1 text-primary drop-shadow-lg text-center"> Giriş Yap</h1>
      <p class="text-gray-500 text-sm italic text-center">İçeri gel dadlım😎</p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-5 text-sm flex items-center gap-2 animate-shake shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414 1.414M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <?= htmlspecialchars($error) ?> <span>🤯</span>
      </div>
    <?php endif; ?>

    <form method="POST" action="#login" autocomplete="on" class="space-y-6 relative z-10">
      <div>
        <label class="block mb-1 text-sm font-semibold text-gray-700" for="email">E-posta</label>
        <input type="email" name="email" id="email" required
          class="w-full border-2 border-indigo-300 p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary transition shadow-sm hover:shadow-md"
          value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
          placeholder="ornek@deu.edu.tr" autocomplete="email">
        <span class="text-xs text-gray-400">* Spam yok, valla 😏</span>
      </div>
      <div>
        <label class="block mb-1 text-sm font-semibold text-gray-700" for="password"> Şifre</label>
        <div class="relative">
          <input type="password" name="password" id="password" required
            class="w-full border-2 border-indigo-300 p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary transition shadow-sm hover:shadow-md pr-10"
            placeholder="Şifreni gir" autocomplete="current-password">
          <button type="button" id="toggle-password" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary focus:outline-none" title="Şifreyi göster/gizle">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
        </div>
        
      </div>
      <button type="submit" class="w-full bg-primary text-white p-3 rounded-xl font-bold hover:bg-cyan-600 transition shadow-lg flex items-center justify-center gap-2">
        Giriş Yap
      </button>
    </form>

    <div class="flex justify-between mt-6 text-sm">
      <a href="register.php" class="text-primary hover:text-cyan-700 transition flex items-center gap-1">Kayıt Ol</a>
      <a href="forgot.php" class="text-secondary hover:text-black-600 transition flex items-center gap-1">Şifremi Unuttum?</a>
      <a href="index.php" class="text-blue-600 hover:underline">Baba Sayfa</a>
    </div>

    <div class="mt-8 text-center text-xs text-gray-400">
      Yakında kapatmasalar bari :D
    </div>
  </div>
</section>


<script>
document.getElementById('toggle-password').addEventListener('click', function() {
  const pwd = document.getElementById('password');
  const eye = document.getElementById('eye-icon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9-4-9-7s4-7 9-7c1.657 0 3.22.41 4.563 1.125M19.07 4.93l-14.14 14.14M9.88 9.88a3 3 0 104.24 4.24"/>';
  } else {
    pwd.type = 'password';
    eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" />';
  }
});
window.onload = () => { document.getElementById('email').focus(); };
</script>

</body>
</html>
