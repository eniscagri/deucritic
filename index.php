<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';


$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['user_name'] ?? 'Ziyaretçi';

$stmt = $pdo->prepare("
  SELECT t.id, t.title, t.description, t.anonymous, t.created_at, u.username 
  FROM topics t
  JOIN users u ON t.created_by = u.id
  ORDER BY t.created_at DESC
  LIMIT 12
");
$stmt->execute();
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($is_logged_in) {
  $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  $username = $user['username'] ?? 'Kullanıcı';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Deucritic | Kampüsün Özgür Sesi</title>
    <link rel="preconnect" href="https://deucritic.com">
    <link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#b84834" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1d352f" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Deucritic">
    <link rel="apple-touch-icon" sizes="180x180" href="/gorsel/180x180logo.png">
<link rel="apple-touch-icon" sizes="167x167" href="/gorsel/167x167logo.png">
<link rel="apple-touch-icon" sizes="152x152" href="/gorsel/152x152logo.png">
<link rel="apple-touch-icon" sizes="512x512" href="/gorsel/512x512logo.png">

<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <link rel="apple-touch-icon" href="/gorsel/logoapp.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/gorsel/favicon-32x32.png">
    <link rel="shortcut icon" href="/gorsel/favicon.ico">

    <meta name="description" content="Deucritic: DEÜ öğrencilerinin fikirlerini paylaştığı, kampüs gündemini belirleyen anonim ve interaktif sözlük platformu.">
    <meta name="keywords" content="Deucritic, DEU sözlük, dokuz eylül üniversitesi, kampüs eleştiri, sosyal platform">
    <link rel="canonical" href="https://deucritic.com">
    
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">

    <meta property="og:site_name" content="Deucritic">
    <meta property="og:title" content="Deucritic | Kampüsün Özgür Sesi">
    <meta property="og:description" content="Kampüs tartışmalarını kaçırma. Hemen katıl, sesini duyur.">
    <meta property="og:image" content="https://deucritic.com/assets/og-image-v2.png">
    <meta property="og:url" content="https://deucritic.com">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@deucritic">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b84834',
                        secondary: '#1d352f',
                        dark: '#1d352f',
                        gold_light: '#e9c65a'
                    },
                    borderRadius: { 'ios': '22px' }
                }
            }
        }
    </script>
    <style>
        :root { --pwa-gold: #b84834; }
        body {
            background: #000 no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            -webkit-tap-highlight-color: transparent;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .no-select { user-select: none; }
        @media (max-width: 768px) { body { background-attachment: scroll; } }
    </style>

    <meta name="google-adsense-account" content="ca-pub-2770419211347612">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2770419211347612" crossorigin="anonymous"></script>

    <script defer>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(err => console.error('SW Error:', err));
        }
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '921891054149834');
        fbq('track', 'PageView');

        document.addEventListener('DOMContentLoaded', () => {            console.log("%c DEUCRITIC V3 ", "background:#e9c65a; color:#1d352f; font-size:20px; font-weight:bold; border-radius:5px; padding:2px 10px;");
            console.log("%cGüvenliğiniz için bu konsola bilmediğiniz kodları yapıştırmayın.", "color:#888;");
        });
    </script>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="bg-gray-100 min-h-screen font-sans">
  <?php include 'header.php'; ?>
    <?php include 'parts/stories_bar.php'; ?>

  <section class="dc-hero" aria-labelledby="dc-home-title">
    <div>
      <p class="dc-hero__eyebrow">DOKUZ EYLÜL ÜNİVERSİTESİ <span>•</span> ÖĞRENCİ SÖZLÜĞÜ</p>
      <h1 id="dc-home-title">Kampüs konuşuyor.<br><em>Söz sende.</em></h1>
      <p class="dc-hero__summary">Ders arası fikirlerinden şehir gündemine — DEÜ öğrencilerinin sözlüğü.</p>
      <div class="dc-hero__actions">
        <a class="dc-hero__button" href="<?= $is_logged_in ? '#onepForm' : 'register.php' ?>">
          <?= $is_logged_in ? 'Bir şey yaz' : 'Sözlüğe katıl' ?>
          <span aria-hidden="true">↗</span>
        </a>
        <a class="dc-hero__link" href="#gundem">Gündeme göz at ↓</a>
      </div>
    </div>
    <div class="dc-hero__stamp" aria-label="Deucritic öğrenci sözlüğü">
      <strong>DC</strong>
      <small>DEÜ<br>ÖĞRENCİ SÖZLÜĞÜ</small>
    </div>
  </section>
  <main class="max-w-4xl mx-auto p-4 mt-6 space-y-8">

    <?php include 'npost.php'; ?>
    
    <?php include 'icerik.php'; ?>
    


  </main>
  
<div id="adsense-container"
     class="w-full min-h-[120px] flex items-center justify-center overflow-hidden">

    <ins class="adsbygoogle"
         style="display:block"
         data-ad-format="fluid"
         data-ad-layout-key="-fb+62+3f-do+by"
         data-ad-client="ca-pub-2770419211347612"
         data-ad-slot="4280438624">
    </ins>

</div>

<script defer>

(() => {

    let adLoaded = false;

    const loadAdsense = () => {

        if (adLoaded) return;

        adLoaded = true;

        const script = document.createElement('script');

        script.src =
        'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2770419211347612';

        script.async = true;

        script.crossOrigin = 'anonymous';

        script.onload = () => {

            try {

                (adsbygoogle = window.adsbygoogle || []).push({});

            } catch (e) {

                console.log('Adsense init error');

            }
        };

        document.body.appendChild(script);
    };


    const observer = new IntersectionObserver((entries) => {

        entries.forEach(entry => {

            if (entry.isIntersecting) {

                loadAdsense();

                observer.disconnect();
            }

        });

    }, {
        rootMargin: '300px'
    });


    const adContainer =
    document.getElementById('adsense-container');

    if (adContainer) {

        observer.observe(adContainer);
    }

})();
</script>

  <?php include 'footer.php'; ?>
</body>
</html>
