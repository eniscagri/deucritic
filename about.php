<?php
require_once 'includes/config.php';

$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hakkında</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b84834',
                        secondary: '#e9c65a',
                    }
                }
            }
        }
    </script>
 <link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon" />
  <link rel="apple-touch-icon" sizes="180x180" href="/gorsel/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/gorsel/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/gorsel/favicon-16x16.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between">
    
<header class="bg-primary text-white shadow">
    <div class="container mx-auto p-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-2">
            <img src="/gorsel/deucritic-mark.svg" alt="Logo" class="w-10 h-10">
            <h1 class="text-2xl font-bold">Deucritic Sözlük</h1>
        </a>
        <nav class="flex gap-4">
            <?php if ($is_logged_in): ?>
                <a href="profile.php" class="hover:underline">Profilim</a>
                <span>|</span>
                <a href="logout.php" class="hover:underline">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php" class="hover:underline">Giriş</a>
                <a href="register.php" class="hover:underline">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </div>
</header>


    <main class="flex-grow flex items-center justify-center px-2 sm:px-4 py-8">
        <div class="bg-white shadow-2xl rounded-2xl p-4 sm:p-8 w-full max-w-2xl">
            <h1 class="text-2xl sm:text-3xl font-bold text-primary mb-6 text-center">Deucritic Sözlük Hakkında</h1>
            <p class="text-gray-700 mb-4 text-base sm:text-lg">
                Deucritic Sözlük, Üniversite tayfası için hazırlanmış, tam anlamıyla ortamın nabzını tutan bir platform. 
                Okulda ne var ne yok, dedikodu, öneri, geyik... Hepsi burada dönüyor.
            </p>
            <p class="text-gray-700 mb-4 text-base sm:text-lg">
                Ünide başına gelen absürt olayları, kafa dengi muhabbetleri, dertleri, goygoyları buraya dökebilirsin. İstediğini gömer, ister kantin fiyatlarına söversin, ister sınavdan sonra “ne yaptık lan biz?” diye isyan edersin. Not paylaşan, geyik yapan, ortamı şenlendiren herkes burada; sen de takıl, rahat ol :D
            </p>
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <div class="flex-1 bg-primary/10 rounded-lg p-4">
                    <h3 class="font-semibold text-primary mb-2">Kolay Kullanım</h3>
                    <p class="text-gray-700 text-sm">
                        Kayıt olmak, içerik atmak çocuk oyuncağı. Takılmak serbest, ortam samimi.
                    </p>
                </div>

                <div class="flex-1 bg-secondary/10 rounded-lg p-4">
                    <h3 class="font-semibold text-secondary mb-2">Geyik ve Mizah</h3>
                    <p class="text-gray-700 text-sm">
                        Mizahın dibine vur! Geyik yap, eğlen, üniversite hayatını renklendir.
                    </p>
                </div>

                <div class="flex-1 bg-secondary/10 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-700 mb-2">Topluluk</h3>
                    <p class="text-gray-700 text-sm">
                        Gel, muhabbetin dibine vur! Fikirlerini paylaş, yeni kankalar edin, üniversite hayatını efsaneleştir.
                    </p>
                </div>

            </div>
            <hr>
            <p class="text-gray-500 text-xs mt-4 text-center">
                Hatalar mizah amaçlıdır; ama bir şeyler patlarsa hemen haber verin, uçarcasına bakarım. Kodlar bazen kafasına göre takılır, ama merak etmeyin, müdahale etmek benim işim.
            </p>
           <hr class="my-8 border-gray-300/20">
<section class="max-w-4xl mx-auto px-4">
    <h2 class="text-2xl sm:text-3xl font-bold text-primary mb-8 text-center"> Commander Geliştirici Hakkında</h2>

    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=enis" 
             alt="Geliştirici" 
             class="w-28 h-28 sm:w-32 sm:h-32 rounded-full border-4 border-primary shadow-xl transition-transform transform hover:scale-105">

        <div class="text-center sm:text-left flex-1">
            <p class="text-gray-700 text-base sm:text-lg mb-4 leading-relaxed">
                Bu site, kahve veya birayla yaşayan, sabahlara kadar kod yazan ve arada bilgisayara bakıp triplenen DEÜ öğrencisinin eseridir. Yeni eve çıktı kafası ağrıyor, ama yine de bu siteyi yapmaya karar verdi.
                
            </p>
            <p class="text-gray-700 text-base sm:text-lg mb-4 leading-relaxed">
    Ha...Ayrıca 7 yılı aşkın süredir web, yapay zeka, sosyal medya ve e-ticaret projeleri geliştiriyor.  Çeşitli şirketlerde çalıştı ama ona göre olmadığını anladı.

    Tutkusu yazılım, kullanıcı deneyimi ve hayattaki boşlukları kapatmak olan geliştirici (ben), her satır koda bir parça eğlence ve yaratıcılık bırakıyor. Amacı, insanlara fayda sağlayacak yenilikçi ve estetik çözümler üretmek.
</p>

<hr>
            <div class="flex flex-col sm:flex-row sm:items-center justify-center sm:justify-start gap-3 text-gray-500 text-sm">
                <div class="flex items-center gap-2">
<i class="fa-solid fa-envelope" style="color: #2596be;"></i>
<a href="mailto:eniscagrigilik1@gmail.com" class="text-primary hover:underline">eniscagrigilik1@gmail.com</a>
                </div>
               
                
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    <span>Yazılımcı, Öğrenci</span>
                   

                </div>
            </div>
        </div>
    </div>
</section>

           
    </main>
    
    

</body>
</html>