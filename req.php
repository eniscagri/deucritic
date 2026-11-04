<?php
include 'includes/config.php'; // içinde $conn var (mysqli)

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
$error = '';

$userId = $_SESSION['user_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'];
$is_logged_in = !empty($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['csrf_token'] ?? '') !== $_SESSION['csrf_token']) {
        $error = "Güvenlik doğrulaması başarısız.";
    } else {
        // Rate limiting check
        $check = $conn->prepare(
            "SELECT COUNT(*) FROM request 
             WHERE guest_ip = ? AND date > NOW() - INTERVAL 1 MINUTE"
        );
        $check->bind_param('s', $ip);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            $error = "Biraz yavaş 🙂 Tekrar dene.";
        } else {
            /* -------- ÖZELLİK İSTEĞİ -------- */
            if ($_POST['form_type'] === 'feature') {
                $feature = trim($_POST['feature'] ?? '');

                if ($feature === '') {
                    $error = "Özellik alanı boş olamaz.";
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO request (user_id, guest_ip, feature, date)
                         VALUES (?, ?, ?, NOW())"
                    );
                    $stmt->bind_param('iss', $userId, $ip, $feature);
                    $stmt->execute();
                    $stmt->close();

                    $success = "İsteğin alındı 🎉";
                }
            }

            /* -------- OKUL ÖNERİSİ -------- */
            if ($_POST['form_type'] === 'school_request') {
                $school = trim($_POST['school_name'] ?? '');
                $reason = trim($_POST['reason'] ?? '');

                if ($school === '') {
                    $error = "Okul adı zorunlu.";
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO school_requests 
                        (user_id, guest_ip, school_name, reason, date)
                        VALUES (?, ?, ?, ?, NOW())"
                    );
                    $stmt->bind_param('isss', $userId, $ip, $school, $reason);
                    $stmt->execute();
                    $stmt->close();

                    $success = "Önerin kaydedildi 🙌";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>İstekler | Deucritic</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { brandOrange: '#f97316', brandBlue: '#3b82f6' }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(249, 115, 22, 0.05) 0px, transparent 50%), 
                              radial-gradient(at 100% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px) saturate(150%);
            -webkit-backdrop-filter: blur(20px) saturate(150%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06);
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
    <link rel="stylesheet" href="/css/brand.css">
</head>

<body class="text-slate-800 min-h-screen flex flex-col items-center pt-24 pb-12 px-4">

<header class="glass-panel fixed top-0 left-0 w-full z-50 border-b border-white/50">
    <div class="max-w-5xl mx-auto flex justify-between items-center px-4 py-3">
        <a href="index.php" class="flex items-center gap-3 group">
            <div class="p-1.5 bg-gradient-to-tr from-yellow-500 to-brandBlue rounded-xl shadow-sm group-hover:scale-105 transition-transform">
                <img src="/gorsel/deucritic-mark.svg" class="w-8 h-8 rounded-lg" alt="Logo">
            </div>
            <span class="font-extrabold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-500">Deucritic</span>
        </a>
        
        <nav class="hidden md:flex items-center gap-6 text-sm font-bold text-slate-500">
            <a href="/" class="hover:text-brandOrange transition-colors">Ana Sayfa</a>
            <a href="/about.php" class="hover:text-brandOrange transition-colors">Biz Kimiz?</a>
            <a href="/sss.php" class="hover:text-brandOrange transition-colors">SSS</a>
        </nav>

        <div class="flex items-center gap-4">
             <?php if ($is_logged_in): ?>
                <a href="profile.php" class="hidden md:block text-sm font-bold text-slate-500 hover:text-brandOrange transition-colors">Profilim</a>
            <?php else: ?>
                <a href="register.php" class="hidden md:block px-4 py-2 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-brandOrange transition-colors shadow-md">Katıl</a>
            <?php endif; ?>
            
            <button class="md:hidden text-slate-800 text-2xl p-2 focus:outline-none" id="mobileMenuBtn">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div id="mobileMenu" class="hidden md:hidden bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="/" class="block px-3 py-2 rounded-md text-base font-bold text-slate-800 hover:bg-slate-50 hover:text-brandOrange">Ana Sayfa</a>
            <a href="/about.php" class="block px-3 py-2 rounded-md text-base font-bold text-slate-800 hover:bg-slate-50 hover:text-brandOrange">Biz Kimiz?</a>
            <a href="/sss.php" class="block px-3 py-2 rounded-md text-base font-bold text-slate-800 hover:bg-slate-50 hover:text-brandOrange">SSS</a>
             <?php if ($is_logged_in): ?>
                <a href="profile.php" class="block px-3 py-2 rounded-md text-base font-bold text-slate-800 hover:bg-slate-50 hover:text-brandOrange">Profilim</a>
            <?php else: ?>
                 <a href="register.php" class="block px-3 py-2 mt-4 text-center rounded-xl bg-slate-900 text-white font-bold hover:bg-brandOrange">Katıl</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="w-full max-w-xl">
    
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-orange-100 to-yellow-100 mb-4 shadow-sm border border-white">
            <i class="fa-solid fa-lightbulb text-2xl text-brandOrange"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Fikirlerini Paylaş</h1>
        <p class="text-slate-500 font-medium mt-2">Deucritic'i birlikte daha iyi bir yer haline getirelim.</p>
    </div>

    <div class="glass-panel rounded-[2rem] p-6 sm:p-8 space-y-10 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-bl from-orange-300/20 to-brandBlue/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-wand-magic-sparkles text-brandOrange"></i>
                <h2 class="text-xl font-extrabold text-slate-800">Özellik İsteği</h2>
            </div>
            
            <form method="post" class="space-y-4">
                <input type="hidden" name="form_type" value="feature">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <textarea name="feature" rows="3" required
                    class="w-full rounded-[1.5rem] bg-white/50 border border-slate-200/60 p-4 text-slate-700 font-medium resize-none focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-400 transition-all placeholder-slate-400"
                    placeholder="Keşke şöyle bir özellik olsa dediğin şey nedir?"></textarea>

                <button class="w-full px-6 py-3 bg-gradient-to-r from-orange-500 to-yellow-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                    İsteği Gönder
                </button>
            </form>
        </div>

        <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-slate-200/60"></div>
            <span class="flex-shrink-0 mx-4 text-slate-300 text-sm font-bold">VEYA</span>
            <div class="flex-grow border-t border-slate-200/60"></div>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-building-columns text-brandBlue"></i>
                <h2 class="text-xl font-extrabold text-slate-800">Üniversite Öner</h2>
            </div>
            
            <form method="post" class="space-y-4">
                <input type="hidden" name="form_type" value="school_request">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <input type="text" name="school_name" required
                    class="w-full rounded-xl bg-white/50 border border-slate-200/60 p-4 text-slate-700 font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400 transition-all placeholder-slate-400"
                    placeholder="Hangi üniversite eklensin?">

                <textarea name="reason" rows="2"
                    class="w-full rounded-[1.5rem] bg-white/50 border border-slate-200/60 p-4 text-slate-700 font-medium resize-none focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400 transition-all placeholder-slate-400"
                    placeholder="Neden bu üniversite? (Opsiyonel)"></textarea>

                <button class="w-full px-6 py-3 bg-slate-900 text-white text-sm font-bold rounded-xl shadow-md hover:bg-slate-800 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                    Üniversiteyi Öner
                </button>
            </form>
        </div>

    </div>
</div>

<div id="toast" class="fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-full shadow-2xl font-bold text-sm transition-all duration-300 opacity-0 translate-y-10 pointer-events-none z-[100]">
    Mesaj buraya gelecek
</div>

<script>
    // --- Mobile Menu Toggle ---
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // --- Toast Gösterimi ---
    function showToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = `fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-full shadow-2xl font-bold text-sm transition-all duration-300 z-[100] ${isError ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white'}`;
        toast.classList.remove('opacity-0', 'translate-y-10');
        setTimeout(() => toast.classList.add('opacity-0', 'translate-y-10'), 3500);
    }

    // Server-side mesajları yakala ve Toast olarak göster
    <?php if ($success): ?>
        document.addEventListener('DOMContentLoaded', () => {
            showToast("<?= addslashes($success) ?>", false);
        });
    <?php endif; ?>

    <?php if ($error): ?>
        document.addEventListener('DOMContentLoaded', () => {
            showToast("<?= addslashes($error) ?>", true);
        });
    <?php endif; ?>
</script>

</body>
</html>
