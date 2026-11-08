<?php
require_once __DIR__ . '/includes/i18n.php';

require_once 'includes/config.php';

$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($news_id <= 0) { header('Location: paper.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$news = $stmt->fetch();

if (!$news) { header('Location: paper.php'); exit; }

$related_stmt = $pdo->prepare("SELECT id, title, summary FROM news WHERE id != ? ORDER BY RAND() LIMIT 3");
$related_stmt->execute([$news_id]);
$related_news = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<link rel="icon" href="/gorsel/favicon.ico?v=20261104" type="image/x-icon" />
<link rel="apple-touch-icon" sizes="180x180" href="/gorsel/apple-touch-icon.png?v=20261104">
<link rel="icon" type="image/png" sizes="32x32" href="/gorsel/favicon-32x32.png?v=20261104">
<link rel="icon" type="image/png" sizes="16x16" href="/gorsel/favicon-16x16.png?v=20261104">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($news['title']) ?> - DeuCritic Haberler</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { background-color: #f9fafb; font-family: 'Inter', sans-serif; }
.news-content p { margin-bottom: 1rem; }
.back-btn:hover { text-decoration: underline; }
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="min-h-screen flex flex-col">

<header class="bg-blue-800 text-white shadow-md" style="background:#45b0cf">
  <div class="container mx-auto p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold">Son Haberler</h1>
    <nav class="flex gap-4 text-sm">
      <a href="paper.php" class="hover:underline">Tüm Haberler</a>
      <a href="index.php" class="hover:underline">Baba Sayfa</a>
    </nav>
  </div>
</header>

<main class="container mx-auto flex-1 mt-8 p-6 bg-white rounded-2xl shadow-lg">
  <a href="paper.php" class="back-btn text-blue-600 text-sm mb-4 inline-flex items-center gap-1">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
    Geri dön
  </a>

  <h2 class="text-3xl font-bold text-blue-700 mb-2"><?= htmlspecialchars($news['title']) ?></h2>
  <span class="text-xs text-gray-400 mb-6 block">
    Yayınlanma tarihi: <?= date('d.m.Y H:i', strtotime($news['created_at'])) ?>
  </span>

<div class="news-content text-gray-700 leading-relaxed whitespace-pre-line mb-8">
  <?= nl2br(htmlspecialchars($news['content'])) ?>

  <?php if (!empty($news['link'])): ?>
    <div class="mt-6">
      <a href="<?= htmlspecialchars($news['link']) ?>" 
         target="_blank" 
         rel="noopener noreferrer"
         class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition duration-200 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10v11a1 1 0 001 1h11" />
        </svg>
        <span>Bağlantıya Git</span>
      </a>
    </div>
  <?php endif; ?>
</div>




  <div class="flex gap-3 mt-6 mb-10">
    <span class="text-gray-500 text-sm">Paylaş:</span>
    <a href="https://x.com/intent/tweet?text=<?= urlencode($news['title']) ?>&url=<?= urlencode('https://deucritic.com/pprdetail.php?id=' . $news['id']) ?>" 
       target="_blank" class="text-blue-500 hover:text-blue-700">X.com</a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://deucritic.com/pprdetail.php?id=' . $news['id']) ?>" 
       target="_blank" class="text-blue-700 hover:text-blue-900">Facebook</a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('https://deucritic.com/pprdetail.php?id=' . $news['id']) ?>" 
       target="_blank" class="text-blue-600 hover:text-blue-800">LinkedIn</a>
  </div>

  <?php if ($related_news): ?>
    <div class="border-t pt-6">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Benzer Haberler</h3>
      <div class="grid md:grid-cols-3 gap-4">
        <?php foreach ($related_news as $rel): ?>
          <a href="pprdetail.php?id=<?= $rel['id'] ?>" class="block p-4 border border-gray-200 rounded-xl hover:shadow transition">
            <h4 class="font-semibold text-blue-600 mb-2"><?= htmlspecialchars($rel['title']) ?></h4>
            <p class="text-sm text-gray-600"><?= htmlspecialchars(mb_strimwidth($rel['summary'], 0, 100, '...')) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</main>

<footer class="text-center text-gray-500 text-sm py-6">
  © <?= date('Y') ?> DeuCritic — Tüm hakları saklıdır.
</footer>

</body>
</html>
