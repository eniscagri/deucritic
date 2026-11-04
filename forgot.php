<?php
require_once 'includes/config.php';
require_once 'includes/mail_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$message = "";
$messageType = "";
$recoveryEmail = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recoveryEmail = trim($_POST['recovery_email'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if ($csrfToken !== $_SESSION['csrf_token']) {
        $message = "Geçersiz işlem. Sayfayı yenileyip tekrar dene.";
        $messageType = "error";
    } elseif (empty($recoveryEmail)) {
        $message = "E-posta alanını rica etsem boş bırakmaz mısın?";
        $messageType = "error";
    } elseif (!filter_var($recoveryEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Geçerli bir e-posta gir dayım.";
        $messageType = "error";
    } elseif (preg_match('/deu\.edu\.tr$/i', $recoveryEmail)) {
        $message = "Lütfen DEÜ okul e-postanı DEĞİL, kayıt olurken belirlediğin kişisel iletişim e-postanı (Gmail, vb.) gir.";
        $messageType = "error";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, recovery_email FROM users WHERE recovery_email = :email LIMIT 1");
        $stmt->execute([':email' => $recoveryEmail]);
        $user = $stmt->fetch();

        if ($user && !empty($user['recovery_email'])) {
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
            $resetLink = $siteURL . "/reset.php?email=" . urlencode($user['recovery_email']) . "&token=" . urlencode($token);

            sendResetLinkEmail($user['recovery_email'], $resetLink);
        }
        
        $message = "Eğer bu iletişim e-postası sistemimizde kayıtlıysa, sana sıfırlama bağlantısını postaladık! 💌 Mail kutunu (ve spam klasörünü) kontrol et.";
        $messageType = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Deucritic | Şifreni Mi Unuttun?</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="icon" href="/gorsel/favicon.ico?v=20261104" type="image/x-icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script>
    tailwind.config = { 
        theme: { 
            extend: { 
                fontFamily: { sans: ['Inter', 'sans-serif'] },
                colors: { primary: '#b84834', secondary: '#e9c65a', dark: '#1d352f' }
            } 
        } 
    }
</script>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="min-h-screen bg-[#f8fafc] flex items-center justify-center p-4 font-inter selection:bg-primary/20">
    

    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white p-8 md:p-12 w-full max-w-md relative overflow-hidden transition-all duration-500 hover:shadow-[0_20px_60px_rgba(68,181,213,0.1)]">
        
        <div class="relative z-10 text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tr from-primary to-cyan-400 rounded-3xl shadow-lg shadow-primary/20 mb-6 rotate-3 hover:rotate-0 transition-transform duration-500">
                <i class="fa-solid fa-key text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-black text-dark tracking-tight mb-2">Şifremi Unuttum</h1>
            <p class="text-slate-500 font-medium leading-relaxed">
                Merak etme <span class="text-primary font-bold">Deucritic.com</span> yanında! 💌 <br> 
                Sana bir kurtarma halatı atacağız.
            </p>
        </div>

        <?php if($message): ?>
            <div class="relative z-10 mb-8 animate-fadeIn">
                <div class="flex items-center p-4 rounded-2xl border <?= $messageType == 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' ?> shadow-sm">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fa-solid <?= $messageType == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> text-lg"></i>
                    </div>
                    <p class="text-sm font-bold leading-tight"><?= htmlspecialchars($message) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="relative z-10 space-y-8">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="group">
                <label class="flex items-center text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-primary transition-colors" for="recovery_email">
                    Kurtarma E-postası
                </label>
                <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-envelope text-slate-300 group-focus-within:text-primary transition-colors"></i>
                    </div>
                    <input type="email" name="recovery_email" id="recovery_email" required
                        class="w-full bg-slate-50/50 border-2 border-slate-100 p-4 pl-12 rounded-2xl focus:outline-none focus:ring-0 focus:border-primary focus:bg-white transition-all text-dark font-semibold placeholder:text-slate-300 shadow-sm"
                        placeholder="adin@gmail.com" autocomplete="email"
                        value="<?= htmlspecialchars($recoveryEmail ?? '') ?>">
                </div>
                <div class="mt-4 flex items-start gap-3 p-4 bg-blue-50/40 rounded-2xl border border-blue-100/50">
                    <i class="fa-solid fa-circle-info text-blue-400 mt-0.5"></i>
                    <p class="text-[11px] text-blue-600/80 font-semibold leading-relaxed uppercase tracking-wider">
                        Okul mailine değil, kayıt olurken verdiğin <span class="text-blue-700">kişisel adresine</span> link göndereceğiz.
                    </p>
                </div>
            </div>

            <button type="submit"
                class="group relative w-full bg-dark text-white p-5 rounded-2xl font-black text-base shadow-xl shadow-dark/10 hover:shadow-primary/20 hover:bg-primary transition-all duration-500 active:scale-[0.98] overflow-hidden">
                <span class="relative z-10 flex items-center justify-center gap-3">
                    Şifremi Sıfırla <i class="fa-solid fa-paper-plane group-hover:translate-x-2 group-hover:-translate-y-2 transition-transform duration-500"></i>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            </button>
        </form>

        <div class="relative z-10 mt-12 pt-8 border-t border-slate-100 text-center">
            <p class="text-slate-400 text-sm font-bold tracking-tight">
                Şifreni hatırladın mı? 
                <a href="login.php" class="text-primary hover:text-dark underline decoration-2 underline-offset-4 transition-all ml-1">Giriş Yap</a>
            </p><hr>
             <p class="text-slate-400 text-sm font-bold tracking-tight">
                Eğer şifreni hatırlamıyorsan
                <a href="mailto:bilgi@deucritic.com" class="text-primary">bize mail atabilirsin.</a>
            </p>
            <a href="index.php" class="inline-flex items-center gap-2 mt-6 text-xs font-black text-slate-300 uppercase tracking-widest hover:text-primary transition-colors group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-2 transition-transform"></i> Baba Sayfa'ya Dön
            </a>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        .animate-fadeIn { animation: fadeIn 0.4s ease-out forwards; }
    </style>
</body>
</html>