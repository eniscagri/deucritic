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
  LIMIT 50
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Deucritic | Sözlük</title>
  <link rel="icon" href="/gorsel/favicon.ico">
  <script src="https://cdn.tailwindcss.com"></script>
  <!--SEO-->
 <meta name="description" content="Deucritic, kullanıcıların fikirlerini özgürce paylaşabildiği, yorum yapabildiği ve çeşitli konularda eleştiri üretebildiği insan odaklı bir platformdur.">
    <meta name="keywords" content="Deucritic, eleştiri platformu, yorum, kullanıcı yorumları, sosyal platform, interaktif sözlük, özgür fikir, Türkiye">
    <meta name="author" content="deucritic.com">
    <meta property="og:title" content="Deucritic - İnsan Odaklı Eleştiri ve Yorum Platformu">
    <meta property="og:description" content="Deucritic ile fikirlerinizi özgürce paylaşın, yorum yapın ve toplulukla etkileşimde bulunun.">
    <meta property="og:image" content="https://deucritic.com/assets/og-image.png">
    <meta property="og:url" content="https://deucritic.com">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Deucritic - İnsan Odaklı Eleştiri ve Yorum Platformu">
    <meta name="twitter:description" content="Deucritic ile fikirlerinizi özgürce paylaşın, yorum yapın ve toplulukla etkileşimde bulunun.">

    <link rel="canonical" href="https://deucritic.com">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: { primary: '#44b5d5', secondary: '#f3cc8b' },
        }
      }
    }
  </script>
  <style>
      body {
    background-image: url('/gorsel/bckgrond.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    min-height: 100vh;
     background-color: rgba(0,0,0,0.3);
}

@media (max-width: 768px) {
    body {
        background-image: url('/gorsel/bckgrondmb.jpg');
        background-attachment: scroll;    }
}

  </style>
<script>
const backgroundsDesktop = [
    '/gorsel/bckg/bckgrond.jpg',
    '/gorsel/bckg/bckgrondone.jpg',
    '/gorsel/bckg/bckgrondtw.jpg',
    '/gorsel/bckg/bckgrondthe.jpg'
];

const backgroundsMobile = [
    '/gorsel/bckg/bckgrondonemb.jpg',
    '/gorsel/bckg/bckgrondtwmb.jpg',
    '/gorsel/bckg/bckgrondmb.jpg'

    
];

function setRandomBackground() {
    const isMobile = window.innerWidth <= 768;
    const backgrounds = isMobile ? backgroundsMobile : backgroundsDesktop;
    const randomBg = backgrounds[Math.floor(Math.random() * backgrounds.length)];
    document.body.style.backgroundImage = `url('${randomBg}')`;
}

window.addEventListener('load', setRandomBackground);
</script>



</head>

<body class="bg-gray-100 min-h-screen font-sans">
  <?php include 'header.php'; ?>

  <nav class="bg-white shadow sticky top-0 z-50 border-b border-gray-200">
    <div class="max-w-screen-xl mx-auto px-4 py-3 flex items-center justify-between">
              <a href="/" class="flex items-center gap-2 text-xl font-bold text-primary transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        
          <span>
            <?php if (isset($username)): ?>
              Merhaba, <span class="text-primary"><?= htmlspecialchars($username) ?></span>
            <?php else: ?>
              Merhaba, <span class="text-gray-600">Ziyaretçi</span>
            <?php endif; ?>
          </span>
        </a>


      <div class="flex items-center gap-4">
        <?php if ($is_logged_in): ?>
          <a href="profile.php" class="flex items-center gap-2 text-primary font-semibold hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <?= htmlspecialchars($username) ?>
          </a>
        <?php else: ?>
          <a href="login.php" class="text-primary font-semibold hover:underline">Giriş Yap</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="max-w-4xl mx-auto p-4 mt-6 space-y-8">

    <?php include 'npost.php'; ?>

<section class="bg-white bg-opacity-90 rounded-2xl shadow-md p-5">
          <h2 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v16m8-8H4"/>
        </svg>
        En Yeni Onepler
      </h2>

      <ul class="space-y-4">
        <?php foreach ($topics as $topic): ?>
          <li class="border-b last:border-0 pb-3">
            <a href="topic.php?id=<?= $topic['id'] ?>" class="text-lg font-medium text-primary hover:underline">
              <?= htmlspecialchars($topic['title']) ?>
            </a>
            <?php if (!empty($topic['description'])): ?>
              <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars(mb_strimwidth($topic['description'], 0, 120, '...')) ?></p>
            <?php endif; ?>
            <div class="text-xs text-gray-500 mt-1">
              <?php if ($topic['anonymous']): ?>
                <span>AnY Kullanıcı</span>
              <?php else: ?>
                <a href="uye.php?user=<?= urlencode($topic['username']) ?>" class="text-primary hover:underline">@<?= htmlspecialchars($topic['username']) ?></a>
              <?php endif; ?>
<span class="flex items-center gap-2 text-sm text-gray-600">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
  </svg>


  <?php
    $date = new DateTime($topic['created_at']);
    $now = new DateTime();
    $diff = $now->diff($date);

    if ($diff->days === 0) {
        if ($diff->h > 0) {
            $timeString = $diff->h . " saat önce";
        } elseif ($diff->i > 0) {
            $timeString = $diff->i . " dakika önce";
        } else {
            $timeString = "az önce";
        }
    } elseif ($diff->days === 1) {
        $timeString = "dün " . $date->format("H:i");
    } elseif ($diff->days < 7) {
        $days = ["Pazar","Pazartesi","Salı","Çarşamba","Perşembe","Cuma","Cumartesi"];
        $dayName = $days[$date->format("w")];
        $timeString = $dayName . " günü " . $date->format("H:i");
    } else {
        setlocale(LC_TIME, 'tr_TR.UTF-8');
        $timeString = @strftime("%e %B %Y, %H:%M", $date->getTimestamp());
    }
  ?>

  <span><?= htmlspecialchars($timeString) ?></span>
</span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </main>

  <?php include 'footer.php'; ?>
</body>
</html>
