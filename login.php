<?php
require_once 'includes/config.php';
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'];
}
$user_ip = getUserIP();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Geçersiz istek.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];

                $update = $pdo->prepare("UPDATE users SET last_ip = ?, last_login = NOW() WHERE id = ?");
                $update->execute([$user_ip, $user['id']]);

                header("Location: index.php");
                exit;
            } else {
                $error = "Kullanıcı adı veya şifre hatalı.";
            }
        } else {
            $error = "Lütfen tüm alanları doldurun.";
        }
    }
}

// Görsel havuzu
$images = [
    "/uploads/login/123312.jpg",
    "/uploads/login/10346.jpg",
    "/uploads/login/183193.jpg",
    "/uploads/login/9737.jpg",
    "/uploads/login/12165.jpg",
    "/uploads/login/6502.jpg"
];
$bg = $images[array_rand($images)];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, viewport-fit=cover">
<title>Giriş Yap - Deucritic</title>

<link rel="icon" href="/gorsel/favicon.ico">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
            colors: {
                primary: '#b84834',
                primaryDark: '#2c92b0',
                secondary: '#e9c65a',
                dark: '#1d352f'
            }
        }
    }
}

function togglePassword(id, btnIconId){ 
    const i = document.getElementById(id); 
    const icon = document.getElementById(btnIconId);
    if (i.type === "password") {
        i.type = "text";
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10a9.963 9.963 0 012.175-6.125M21.825 13.875A9.963 9.963 0 0122 12c0-5.523-4.477-10-10-10-1.78 0-3.44.48-4.875 1.325 M15 12a3 3 0 11-6 0 3 3 0 016 0z M3 3l18 18" />';
    } else {
        i.type = "password";
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}
</script>

<style>
body {
    -webkit-tap-highlight-color: transparent;
    background-color: #f8fafc;
}
input { font-size: 16px !important; }

/* Animasyonlar */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes shake {
    0%,100%{transform:translateX(0);}
    25%{transform:translateX(-4px);}
    50%{transform:translateX(4px);}
    75%{transform:translateX(-4px);}
}
.animate-shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }

.input-field:focus-within label { color: #b84834; }
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="font-sans text-slate-800 antialiased selection:bg-primary/20 selection:text-primaryDark">

<div class="min-h-[100dvh] flex items-center justify-center relative overflow-hidden md:p-6 lg:p-8">

    <div class="hidden md:block absolute top-[-15%] left-[-10%] w-[500px] h-[500px] bg-primary/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="hidden md:block absolute bottom-[-15%] right-[-10%] w-[500px] h-[500px] bg-secondary/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="w-full min-h-[100dvh] md:min-h-[650px] md:max-w-5xl md:h-auto bg-white md:rounded-[2.5rem] md:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] flex flex-col md:flex-row overflow-hidden relative z-10">

        <div class="w-full h-[45vh] md:h-auto md:w-5/12 relative group overflow-hidden flex-shrink-0">
            <div class="dc-login-photo-shade absolute inset-0 z-10" aria-hidden="true"></div>
            <div class="absolute inset-0 bg-primary/10 mix-blend-overlay z-10"></div>
            
            <img src="<?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?>" alt="DEÜ kampüsünden bir görünüm" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high" onerror="this.onerror=null;this.src='/gorsel/bckg/bckgrondone.jpg';">
            
            <div class="absolute top-0 left-0 w-full p-6 md:p-10 z-20 flex justify-between items-center mt-4 md:mt-0">
                <a href="index.php" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border border-white/30 text-white hover:bg-white/30 transition shadow-sm">
                    <svg class="w-5 h-5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <span class="text-white font-black tracking-widest uppercase text-sm drop-shadow-md">Deucritic</span>
            </div>

            <div class="absolute bottom-12 md:bottom-10 left-0 w-full px-8 md:px-10 z-20">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/20 backdrop-blur-md text-white text-xs font-bold rounded-full border border-white/30 mb-4 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-primary" aria-hidden="true"></span>
                    Kampüse Dönüş
                </div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white mb-2 leading-tight drop-shadow-lg">
                    Tekrar <br> Hoş Geldin!
                </h1>
                <p class="text-slate-200 text-sm md:text-base font-medium drop-shadow">Gündemi yakala, tartışmalara yön ver.</p>
            </div>
        </div>

        <div class="w-full md:w-7/12 bg-white flex-1 -mt-6 md:mt-0 relative z-20 rounded-t-[2rem] md:rounded-none px-6 py-8 md:p-12 lg:p-16 xl:p-20 flex flex-col justify-center animate-fade-in-up">
            
            <div class="w-full flex justify-center md:hidden mb-8">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
            </div>

            <div class="w-full max-w-md mx-auto md:mx-0 lg:max-w-sm">
                
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-800 mb-2 hidden md:block">Giriş Yap</h2>
                <p class="text-slate-500 text-sm font-medium mb-8 hidden md:block">Hesabına erişerek kaldığın yerden devam et.</p>

                <?php if($error): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl mb-6 text-sm font-semibold animate-shake flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <span class="leading-relaxed"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="input-field group">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1 transition-colors">Takma Ad / Okul E-Postası</label>
                        <input type="text" name="username" placeholder="Takma adın veya DEÜ mailin..."
                               class="w-full text-[16px] bg-slate-50/50 border border-slate-200 text-slate-800 px-5 py-4 rounded-2xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-medium placeholder-slate-400 hover:border-slate-300" required>
                    </div>

                    <div class="input-field group">
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider transition-colors">Şifre</label>
                            <a href="forgot.php" class="text-xs font-bold text-primary hover:text-primaryDark hover:underline transition-colors">Şifremi Unuttum.</a>
                        </div>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                   class="w-full text-[16px] bg-slate-50/50 border border-slate-200 text-slate-800 px-5 py-4 rounded-2xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-medium placeholder-slate-400 hover:border-slate-300 pr-12" required>
                            <button type="button" onclick="togglePassword('password', 'eyeIcon')" class="absolute right-0 top-0 h-full w-14 flex items-center justify-center text-slate-400 hover:text-primary transition focus:outline-none">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-2 bg-dark text-white py-4 rounded-2xl font-extrabold text-base shadow-lg shadow-dark/20 active:scale-[0.98] hover:bg-primary md:hover:-translate-y-1 md:hover:shadow-xl md:hover:shadow-primary/30 transition-all duration-300 flex justify-center items-center gap-2 group">
                        Giriş Yap
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm text-slate-500 font-medium">
                        Deucritic.com'u merak mı ettin?
                        <a href="register.php" class="text-primary font-extrabold underline decoration-2 decoration-primary/30 hover:decoration-primary transition-all underline-offset-4 ml-1">
                            Hesap Oluştur
                        </a>
                    </p>
                </div>
                
            </div>
        </div>

    </div>
</div>

</body>
</html>
