<?php
require_once __DIR__ . '/includes/i18n.php';

require_once 'includes/config.php';
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$remoteIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$user_ip = filter_var($remoteIp, FILTER_VALIDATE_IP) !== false ? $remoteIp : null;

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Geçersiz istek.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $update = $pdo->prepare("UPDATE users SET last_ip = ?, last_login = NOW() WHERE id = ?");
                $update->execute([$user_ip, $user['id']]);

                try {
                    $hasUserAgent = $pdo->prepare(
                        "SELECT COUNT(*) FROM information_schema.columns
                         WHERE table_schema = DATABASE() AND table_name = 'user_ip_logs' AND column_name = 'user_agent'"
                    );
                    $hasUserAgent->execute();
                    if ((int) $hasUserAgent->fetchColumn() > 0) {
                        $loginLog = $pdo->prepare(
                            "INSERT INTO user_ip_logs (user_id, ip, user_agent) VALUES (?, ?, ?)"
                        );
                        $loginLog->execute([
                            $user['id'],
                            $user_ip,
                            substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 512),
                        ]);
                    } else {
                        $loginLog = $pdo->prepare(
                            "INSERT INTO user_ip_logs (user_id, ip) VALUES (?, ?)"
                        );
                        $loginLog->execute([$user['id'], $user_ip]);
                    }
                } catch (PDOException $e) {
                    error_log('Login history record failed: ' . $e->getMessage());
                }

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
    "/uploads/login/123312.jpg?v=20261104",
    "/uploads/login/10346.jpg?v=20261104",
    "/uploads/login/183193.jpg?v=20261104",
    "/uploads/login/9737.jpg?v=20261104",
    "/uploads/login/12165.jpg?v=20261104",
    "/uploads/login/6502.jpg?v=20261104"
];
$bg = $images[array_rand($images)];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, viewport-fit=cover">
<title>Giriş Yap - Deucritic</title>

<link rel="icon" href="/gorsel/favicon.ico?v=20261104">
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
    const button = icon.closest('button');
    if (i.type === "password") {
        i.type = "text";
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10a9.963 9.963 0 012.175-6.125M21.825 13.875A9.963 9.963 0 0122 12c0-5.523-4.477-10-10-10-1.78 0-3.44.48-4.875 1.325 M15 12a3 3 0 11-6 0 3 3 0 016 0z M3 3l18 18" />';
        button.setAttribute('aria-label', 'Şifreyi gizle');
        button.setAttribute('aria-pressed', 'true');
    } else {
        i.type = "password";
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        button.setAttribute('aria-label', 'Şifreyi göster');
        button.setAttribute('aria-pressed', 'false');
    }
}
</script>

<style>
body {
    -webkit-tap-highlight-color: transparent;
    background:
        radial-gradient(ellipse at 9% 12%, rgba(184, 72, 52, .11), transparent 34rem),
        radial-gradient(ellipse at 94% 88%, rgba(233, 198, 90, .14), transparent 30rem),
        #f7f8f6;
}
input { font-size: 16px !important; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp .55s cubic-bezier(.16, 1, .3, 1) both;
}

@keyframes shake {
    0%,100%{transform:translateX(0);}
    25%{transform:translateX(-4px);}
    50%{transform:translateX(4px);}
    75%{transform:translateX(-4px);}
}
.animate-shake { animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }

.input-field:focus-within label { color: #b84834; }
.login-shell {
    width: min(1120px, 100%);
    min-height: min(720px, calc(100dvh - 64px));
    border: 1px solid rgba(255,255,255,.82);
    box-shadow: 0 32px 90px -38px rgba(29,53,47,.3), 0 4px 18px rgba(29,53,47,.05);
}
.login-photo::after {
    position: absolute;
    inset: 0;
    z-index: 1;
    content: "";
    background: linear-gradient(180deg, rgba(15,35,31,.08) 8%, rgba(15,35,31,.05) 42%, rgba(15,35,31,.78) 100%);
}
.login-input {
    transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease;
}
.login-input:focus {
    border-color: #b84834;
    background-color: #fff;
    box-shadow: 0 0 0 4px rgba(184,72,52,.1);
    outline: none;
}
.login-submit { box-shadow: 0 12px 24px -12px rgba(29,53,47,.65); }
.login-submit:hover { box-shadow: 0 16px 30px -14px rgba(184,72,52,.7); }
@media (max-width: 767px) {
    .login-shell {
        min-height: 100dvh;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
}
@media (max-height: 760px) and (min-width: 768px) {
    .login-shell { min-height: 620px; }
}
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="font-sans text-slate-800 antialiased selection:bg-primary/20 selection:text-primaryDark">

<main class="min-h-[100dvh] flex items-center justify-center md:p-5 lg:p-8">
    <section class="login-shell bg-white md:rounded-[2rem] flex flex-col md:flex-row overflow-hidden relative">

        <div class="login-photo w-full h-[250px] sm:h-[290px] md:h-auto md:w-[44%] relative overflow-hidden flex-shrink-0 bg-dark">
            <img src="<?= htmlspecialchars($bg, ENT_QUOTES, 'UTF-8') ?>" alt="DEÜ kampüsünden bir görünüm" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high" onerror="this.onerror=null;this.src='/gorsel/bckg/bckgrondone.jpg';">
            <div class="absolute top-0 left-0 w-full p-5 sm:p-7 md:p-9 z-20 flex justify-between items-center">
                <a href="index.php" aria-label="Ana sayfaya dön" class="w-10 h-10 bg-white/15 backdrop-blur-md rounded-full flex items-center justify-center border border-white/25 text-white hover:bg-white/25 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <svg class="w-5 h-5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <span class="text-white font-extrabold tracking-[.18em] uppercase text-xs drop-shadow-md">Deucritic</span>
            </div>

            <div class="absolute bottom-7 sm:bottom-9 md:bottom-11 left-0 w-full px-6 sm:px-8 md:px-10 z-20">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/15 backdrop-blur-md text-white text-[11px] font-bold rounded-full border border-white/20 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-300" aria-hidden="true"></span>
                    DEÜ kampüsünün sesi
                </div>
                <h1 class="text-[2rem] sm:text-4xl md:text-5xl font-extrabold text-white mb-2 leading-[1.05] tracking-tight">
                    Kampüs<br class="hidden md:block"> burada başlar.
                </h1>
                <p class="text-white/80 text-sm md:text-base font-medium">Gündemi yakala, fikrini paylaş, sohbete katıl.</p>
            </div>
        </div>

        <div class="w-full md:w-[56%] bg-white flex-1 relative px-6 py-8 sm:px-10 sm:py-10 md:px-12 lg:px-[4.5rem] flex flex-col justify-center animate-fade-in-up">
            <div class="w-full max-w-md mx-auto">

                <div class="mb-7 md:mb-9">
                    <div class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-[.16em] text-primary mb-3">
                        <span class="w-7 h-px bg-primary/50" aria-hidden="true"></span>
                        Hesabına devam et
                    </div>
                    <h2 class="text-[1.8rem] sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Tekrar hoş geldin</h2>
                    <p class="text-slate-500 text-sm sm:text-[15px] leading-relaxed">Deucritic hesabına giriş yap ve kampüste neler olup bittiğini keşfet.</p>
                </div>

                <?php if($error): ?>
                    <div role="alert" class="bg-red-50 border border-red-100 text-red-700 p-4 rounded-2xl mb-6 text-sm font-semibold animate-shake flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <span class="leading-relaxed"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="input-field">
                        <label for="username" class="block text-[13px] font-bold text-slate-700 mb-2 transition-colors">Takma ad veya okul e-postası</label>
                        <input id="username" type="text" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" placeholder="Takma adın ya da DEÜ e-postan"
                               autocomplete="username" autocapitalize="none" spellcheck="false"
                               class="login-input w-full text-[16px] bg-slate-50 border border-slate-200 text-slate-800 px-4 py-[15px] rounded-xl font-medium placeholder-slate-400 hover:border-slate-300" required>
                    </div>

                    <div class="input-field">
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-[13px] font-bold text-slate-700 transition-colors">Şifre</label>
                            <a href="forgot.php" class="text-xs font-bold text-primary hover:text-primaryDark hover:underline transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary rounded">Şifremi unuttum</a>
                        </div>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Şifreni gir"
                                   autocomplete="current-password"
                                   class="login-input w-full text-[16px] bg-slate-50 border border-slate-200 text-slate-800 px-4 py-[15px] rounded-xl font-medium placeholder-slate-400 hover:border-slate-300 pr-12" required>
                            <button type="button" onclick="togglePassword('password', 'eyeIcon')" aria-label="Şifreyi göster" aria-pressed="false" class="absolute right-1 top-1 h-[calc(100%-8px)] w-11 rounded-lg flex items-center justify-center text-slate-400 hover:text-primary hover:bg-primary/5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-submit w-full mt-2 bg-dark text-white py-[15px] rounded-xl font-extrabold text-[15px] active:scale-[0.99] hover:bg-primary md:hover:-translate-y-0.5 transition-all duration-200 flex justify-center items-center gap-2 group focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-primary">
                        Giriş Yap
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

                <div class="mt-7 pt-6 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-500 font-medium">
                        Henüz hesabın yok mu?
                        <a href="register.php" class="text-primary font-extrabold hover:text-primaryDark transition-colors ml-1 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary rounded">
                            Hemen hesap oluştur
                        </a>
                    </p>
                </div>
            </div>
        </div>

    </section>
</main>

</body>
</html>
