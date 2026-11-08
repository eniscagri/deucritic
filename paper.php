<?php
require_once __DIR__ . '/includes/i18n.php';

require_once 'includes/config.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$query = "SELECT id, title, summary, created_at FROM news";
$params = [];

if ($search !== '') {
    $query .= " WHERE title LIKE :search OR summary LIKE :search";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$news_list = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="tr">
    
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Deucritic Haberler</title>
<link rel="icon" href="/gorsel/favicon.ico?v=20261104">
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
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { background-color: #f9fafb; font-family: 'Inter', sans-serif; }
.news-card { transition: all 0.3s ease; }
.news-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.news-summary { display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
.fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>

<body class="min-h-screen">
<header class="bg-blue-800 text-white shadow-md" style="background:#45b0cf">
  <div class="container mx-auto p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold">Son Güncellemeler ve Haberler</h1>
    <nav class="flex gap-4 text-sm">
      <a href="paper.php" class="hover:underline">Tüm Haberler</a>
      <a href="index.php" class="hover:underline">Baba Sayfa</a>
    </nav>
  </div>
</header>
<main class="container mx-auto mt-8 p-4 fade-in">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <h1 class="text-3xl font-bold text-primary">Deucritic'te Neler Oluyor?</h1>
        <form action="paper.php" method="GET" class="flex gap-2 w-full md:w-auto">
            <input 
                type="text" 
                name="q" 
                value="<?= htmlspecialchars($search) ?>" 
                placeholder="Haber ara..." 
                class="border border-gray-300 rounded-lg p-2 w-full md:w-64 focus:ring-2 focus:ring-primary focus:outline-none"
            >
            <button 
                type="submit" 
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition" style="background:#f2ce90">
                Ara
            </button>
        </form>
    </div>

    <?php if (!$news_list): ?>
        <div class="text-gray-500 text-center py-16">
         
            <img width="50" height="50" src="https://img.icons8.com/ios/50/empty_1.png" alt="empty_1" class=" mx-auto mb-4 opacity-70"/>
            <p class="text-lg">DeuCritic'de Haber Bulamadık./p>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($news_list as $news): ?>
                <a href="pprdetail.php?id=<?= $news['id'] ?>" 
                   class="news-card block p-6 bg-white rounded-2xl shadow hover:shadow-lg transition">
                    <h2 class="text-xl font-bold text-primary mb-2"><?= htmlspecialchars($news['title']) ?></h2>
                    <p class="text-gray-700 mb-4 news-summary">
                        <?= htmlspecialchars(mb_strimwidth($news['summary'], 0, 150, '...')) ?>
                    </p>
                    <span class="text-xs text-gray-400"><?= date('d.m.Y', strtotime($news['created_at'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
