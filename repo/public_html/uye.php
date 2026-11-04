<?php
require_once 'includes/config.php';


$username = $_GET['user'] ?? '';
if (!$username) {
    http_response_code(400);
    echo '<div class="text-red-600 font-bold text-center mt-10">Kullanıcı adı belirtilmedi.</div>';
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, username, school, grade_level, created_at, profile_photo, last_active, bio 
    FROM users 
    WHERE username = :username
");
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo '<div class="text-red-600 font-bold text-center mt-10">Böyle bir kullanıcı bulunamadı.</div>';
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM entries WHERE user_id = :user_id");
$stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
$stmt->execute();
$totalEntries = (int)$stmt->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT e.id, e.content, e.created_at, t.title, t.id as topic_id 
    FROM entries e 
    JOIN topics t ON e.topic_id = t.id 
    WHERE e.user_id = :user_id
    ORDER BY e.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($totalEntries / $perPage);

$isOwnProfile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'];

setlocale(LC_TIME, 'tr_TR.UTF-8', 'turkish');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
  <link rel="icon" href="/gorsel/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@<?= htmlspecialchars($user['username']) ?> | Deucritic Profil</title>
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
      colors: {
        primary: '#44b5d5',
        secondary: '#f3cc8b',
      }
    }
  }
}
</script>
</head>
<body class="bg-gradient-to-br from-primary/10 via-secondary/10 to-white min-h-screen text-gray-800">

<header class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-5xl mx-auto flex items-center justify-between p-4">
    <a href="index.php" class="flex items-center gap-2">
      <img src="/gorsel/logo.png" alt="Logo" class="h-10 w-10 rounded-full">
      <span class="text-lg font-bold text-primary">Deucritic Sözlük |  @<?= htmlspecialchars($user['username']) ?></span>
    </a>
    <nav class="space-x-4">
      <a href="index.php" class="hover:underline">Baba Sayfa</a>
      <a href="about.php" class="hover:underline">Hakkımda</a>
      <?php if (isset($_SESSION['user_id'])): ?>
      <a href="logout.php" class="bg-primary text-white px-4 py-1 rounded-full hover:bg-cyan-600 transition">Çıkış</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="max-w-4xl mx-auto px-4 py-12">
  <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 bg-white p-6 rounded-2xl shadow-lg">
  
  <img src="<?= htmlspecialchars($user['profile_photo'] ?: 'uploads/default-avatar.png') ?>" 
       alt="Avatar" 
       class="w-32 h-32 md:w-28 md:h-28 rounded-full border-4 border-primary object-cover shadow-lg transition-transform hover:scale-105">

  <div class="flex-1">
    <h1 class="text-3xl md:text-4xl font-bold text-primary flex items-center gap-2">
      @<?= htmlspecialchars($user['username']) ?>
      <?php if ($isOwnProfile ?? false): ?>
        <span class="text-sm text-gray-400 italic">(Kendi Profilin)</span>
      <?php endif; ?>
    </h1>

    <?php if (!empty($user['bio'])): ?>
      <p class="mt-2 text-gray-700 italic text-sm md:text-base break-words">
        <?= nl2br(htmlspecialchars($user['bio'])) ?>
      </p>
    <?php else: ?>
      <p class="mt-2 text-gray-400 italic text-sm md:text-base">Kullanıcı henüz bio eklememiş.</p>
    <?php endif; ?>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-600 text-sm md:text-base">
      <p class="flex items-center gap-1"><span class="material-icons text-primary text-base">school</span> <strong>Okul:</strong> <?= htmlspecialchars($user['school'] ?: 'Belirtilmedi') ?></p>
      <p class="flex items-center gap-1"><span class="material-icons text-primary text-base">grade</span> <strong>Sınıf:</strong> <?= htmlspecialchars($user['grade_level'] ?: 'Belirtilmedi') ?></p>
      <p class="flex items-center gap-1"><span class="material-icons text-primary text-base">calendar_today</span> <strong>Üye Olma Tarihi:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
      <p class="flex items-center gap-1"><span class="material-icons text-primary text-base">description</span> <strong>Onep Sayısı:</strong> <?= $totalEntries ?></p>
    </div>

    <button disabled class="mt-4 inline-flex items-center gap-2 px-5 py-2 rounded-full font-semibold shadow-md text-white bg-gradient-to-r from-primary to-secondary opacity-70 cursor-not-allowed hover:opacity-90 transition">
      <span class="material-icons text-sm">person_add</span> Takip Et <span class="text-xs italic">(yakında)</span>
    </button>
  </div>
</div>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


  <section class="mt-12">
    <h2 class="text-2xl font-semibold text-primary mb-6">Son Onepler</h2>

    <?php if (empty($entries)): ?>
      <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg shadow-sm">
        Henüz hiç onep paylaşılmamış.
      </div>
    <?php else: ?>
      <ul class="space-y-6">
        <?php foreach ($entries as $entry): 
          $fmt = new IntlDateFormatter('tr_TR', IntlDateFormatter::LONG, IntlDateFormatter::SHORT);
          $dt = new DateTime($entry['created_at']);
          $formattedDate = $fmt->format($dt);
        ?>
        <li class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 hover:shadow-lg transition flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6">
          <div class="flex-1">
            <a href="topic.php?id=<?= $entry['topic_id'] ?>" class="block text-lg font-semibold text-primary hover:underline transition">
              <?= htmlspecialchars($entry['title']) ?>
            </a>
            <time class="text-sm text-gray-500 mt-1 block">
              <?= $formattedDate ?>
            </time>
            <p class="text-gray-700 mt-3 leading-relaxed max-w-full md:max-w-[700px] whitespace-pre-wrap break-words">
              <?= nl2br(htmlspecialchars(mb_strimwidth($entry['content'], 0, 350, '...'))) ?>
            </p>
          </div>
          <div class="flex-shrink-0 flex items-center gap-3 mt-4 md:mt-0">
            <a href="topic.php?id=<?= $entry['topic_id'] ?>&entry=<?= $entry['id'] ?>" 
               class="inline-flex items-center gap-1 px-4 py-2 bg-primary text-white rounded-full font-medium text-sm hover:bg-cyan-600 transition shadow-md">
              Devamını oku
            </a>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($totalPages > 1): ?>
      <div class="mt-8 flex justify-center gap-2 flex-wrap">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?user=<?= urlencode($username) ?>&page=<?= $i ?>" 
             class="px-3 py-1 rounded-full border <?= $i==$page ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
             <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
