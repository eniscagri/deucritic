<?php

session_start();
include 'includes/config.php';

@ini_set('session.use_only_cookies', 1);
@ini_set('session.cookie_httponly', 1);
@ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deucritic | Sosyal Eleştiri Platformu</title>

<link rel="icon" href="/gorsel/favicon.ico?v=20261104">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            },
            colors: {
                primary: '#b84834',
                primaryDark: '#369ab7',
                secondary: '#e9c65a',
                darkbg: '#1d352f'
            }
        }
    }
}
</script>

<style>
body {
    background-color: #f8fafc;
    background-image: 
        radial-gradient(at 0% 0%, hsla(193, 62%, 55%, 0.15) 0px, transparent 50%),
        radial-gradient(at 100% 100%, hsla(38, 83%, 75%, 0.2) 0px, transparent 50%);
    background-attachment: fixed;
}
.glass-panel {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.5);
}
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>

<body class="min-h-screen flex items-center justify-center p-4 sm:p-8 font-sans text-slate-800">

<div class="max-w-5xl w-full bg-white rounded-3xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.07)] overflow-hidden flex flex-col md:flex-row border border-slate-100">

    <div class="md:w-5/12 bg-gradient-to-br from-primary via-[#61c5e0] to-secondary p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 rounded-full bg-secondary opacity-30 blur-2xl"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-8">
                <div class="bg-white p-2 rounded-xl shadow-lg">
                    <img src="/gorsel/deucritic-mark.svg" alt="Deucritic Logo" class="w-10 h-10 object-contain">
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight">Deucritic</h1>
            </div>

            <p class="text-white/90 text-lg font-medium leading-relaxed mb-8">
                Kampüsün nabzını tut. <br>
                <span class="text-white font-bold">Anonim</span> veya <span class="text-white font-bold">açık</span> şekilde tartış, eleştir ve topluluğa katıl.
            </p>

           <div class="flex flex-wrap gap-2 mb-8">

    <!-- Anonim -->
    <span class="group bg-white/20 backdrop-blur-xl border border-white/30 text-white text-xs font-semibold px-4 py-2 rounded-full flex items-center gap-2 transition hover:bg-white/30 hover:scale-105">
        <div class="p-1 rounded-full bg-white/20 group-hover:bg-white/40 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                </path>
            </svg>
        </div>
        <span>Anonim Paylaşım</span>
    </span>

    <!-- Tartışma -->
    <span class="group bg-white/20 backdrop-blur-xl border border-white/30 text-white text-xs font-semibold px-4 py-2 rounded-full flex items-center gap-2 transition hover:bg-white/30 hover:scale-105">
        <div class="p-1 rounded-full bg-white/20 group-hover:bg-white/40 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z">
                </path>
            </svg>
        </div>
        <span>Canlı Tartışmalar</span>
        <span class="ml-1 text-[10px] bg-green-400 text-black px-1.5 py-0.5 rounded-full animate-pulse">LIVE</span>
    </span>

    <!-- Rozet -->
    <span class="group bg-white/20 backdrop-blur-xl border border-white/30 text-white text-xs font-semibold px-4 py-2 rounded-full flex items-center gap-2 transition hover:bg-white/30 hover:scale-105">
        <div class="p-1 rounded-full bg-white/20 group-hover:bg-white/40 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                </path>
            </svg>
        </div>
        <span>Rozet & Seviye Sistemi</span>
    </span>

    <!-- Yeni: Sosyal Kanıt -->
    <span class="bg-gradient-to-r from-primary to-secondary text-white text-xs font-bold px-4 py-2 rounded-full flex items-center gap-2 shadow-lg animate-pulse">
        🔥 1000+ Aktif Öğrenci
    </span>

</div>
        </div>

        <?php
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $realUserCount = $stmt->fetchColumn();
        $activeStudentCount = $realUserCount + 1000;
        ?>
        <div class="relative z-10 glass-panel rounded-2xl p-4 mt-8 flex justify-between items-center">
            <div>
                <p class="text-white/80 text-xs font-semibold uppercase tracking-wider mb-1">DEÜ Aktif Öğrenci</p>
                <p class="text-2xl font-bold text-white"><?= number_format($activeStudentCount, 0, ',', '.') ?></p>
            </div>
            <div class="relative flex items-center justify-center h-10 w-10 bg-white/20 rounded-full">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-300 opacity-60"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-400"></span>
            </div>
        </div>
    </div>

    <div class="md:w-7/12 p-8 md:p-12 lg:p-16 flex flex-col justify-center relative">
        
        <nav class="absolute top-6 right-8 flex gap-6 text-sm font-semibold text-slate-400">
            <a href="/sss.php" class="hover:text-primary transition-colors">SSS</a>
            <a href="/about.php" class="hover:text-primary transition-colors">Kimiz?</a>
            <a href="/paper.php" class="hover:text-primary transition-colors">Haberler</a>
        </nav>

        <div class="mt-8 md:mt-0 max-w-sm w-full mx-auto space-y-8">
            
            <div class="text-center md:text-left">
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Aramıza Gel veya Katıl</h2>
                <p class="text-slate-500 text-sm">İçerikleri görmek ve tartışmalara katılmak için giriş yapmalısın. Bunu paylaşımlarınızın gizliliği için yapıyoruz.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg text-sm font-medium shadow-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <a href="/login.php" class="block w-full text-center bg-primary text-white font-bold py-3.5 rounded-xl shadow-lg shadow-primary/30 hover:bg-primaryDark hover:-translate-y-0.5 transition-all duration-200">
                    Giriş Yap
                </a>
                
                <div class="relative flex items-center py-2">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink-0 mx-4 text-slate-400 text-xs font-semibold uppercase tracking-wider">veya ilk defa geliyorsan</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <a href="/register.php" class="block w-full text-center bg-white border-2 border-slate-200 text-slate-700 font-bold py-3.5 rounded-xl hover:border-primary hover:text-primary transition-all duration-200">
                    Yeni Hesap Oluştur
                </a>
            </div>

            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Yakında Diğer Okullarda</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="bg-white border border-slate-200 text-slate-500 text-[11px] font-semibold px-2.5 py-1 rounded-md">Ege Üni.</span>
                    <span class="bg-white border border-slate-200 text-slate-500 text-[11px] font-semibold px-2.5 py-1 rounded-md">Demokrasi Üni.</span>
                    <span class="bg-white border border-slate-200 text-slate-500 text-[11px] font-semibold px-2.5 py-1 rounded-md">Ekonomi Üni.</span>
                    <span class="bg-white border border-slate-200 text-slate-500 text-[11px] font-semibold px-2.5 py-1 rounded-md">Marmara Üni.</span>
                </div>
                <a href="req.php" class="inline-block mt-4 text-sm font-semibold text-primary hover:text-primaryDark hover:underline transition-colors">
                    Kendi okulunda da görmek ister misin? &rarr;
                </a>
            </div>

        </div>

        <footer class="mt-12 text-center text-[11px] text-slate-400 font-medium">
            <p>Yalnızca sizin için. © 2026 Deucritic</p>
            <p class="mt-1">an eniscagri production</p>
        </footer>

    </div>

</div>

</body>
</html>