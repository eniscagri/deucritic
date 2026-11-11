<?php
require_once __DIR__ . '/includes/i18n.php';

require_once __DIR__ . '/includes/config.php';

$q = trim(substr((string) ($_GET['q'] ?? ''), 0, 100));
$results = [];
$topicResults = [];
$entryResults = [];

if ($q !== '') {
    $searchTerm = "%{$q}%";
    $stmt = $pdo->prepare("SELECT id, username, bio, profile_photo FROM users WHERE username LIKE :username_q OR bio LIKE :bio_q ORDER BY username LIMIT 30");
    $stmt->bindValue(':username_q', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':bio_q', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT t.id, t.title, t.description, t.created_at, u.username FROM topics t JOIN users u ON u.id = t.created_by WHERE t.title LIKE :title_q OR t.description LIKE :description_q ORDER BY t.created_at DESC LIMIT 30");
    $stmt->bindValue(':title_q', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':description_q', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $topicResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT e.topic_id, e.content, e.created_at, t.title
        FROM entries e
        JOIN topics t ON t.id = e.topic_id
        WHERE e.content LIKE :entry_content_q OR t.title LIKE :entry_title_q
        ORDER BY e.created_at DESC
        LIMIT 30
    ");
    $stmt->bindValue(':entry_content_q', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':entry_title_q', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $entryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProfilePhoto($photo) {
    return $photo && file_exists(__DIR__ . '/' . $photo) ? $photo : 'uploads/default-avatar.png';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arama Sonuçları</title>
    <script src="https://cdn.tailwindcss.com" defer></script>
    <link rel="stylesheet" href="/css/brand.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen dc-search-page">
    <?php include __DIR__ . '/header.php'; ?>

    <main class="dc-search-main max-w-3xl mx-auto p-6">
        <section class="dc-search-intro">
            <p class="dc-label">Kampüsün gündemi</p>
            <h1 class="text-2xl font-bold mb-2">Aradığını bul</h1>
            <p>Gönderilerde, konu başlıklarında ve öğrenci profillerinde ara.</p>
        </section>
        <form action="/search.php" method="GET" class="dc-search-form mb-6 flex gap-2" role="search">
            <label class="sr-only" for="people-search">Gönderi, konu veya kullanıcı ara</label>
            <input id="people-search" type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Gönderi, konu veya kullanıcı ara..." class="flex-1 p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" autocomplete="off" maxlength="100" required>
            <button type="submit" class="bg-primary text-white px-4 py-3 rounded-xl hover:bg-cyan-600 transition"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>Ara</span></button>
        </form>

        <?php if ($q === ''): ?>
            <div class="dc-search-empty p-4 bg-white rounded-xl shadow text-gray-600"><span class="dc-search-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span><strong>Keşfe hazır mısın?</strong><p>Bir kullanıcı adı ya da ilgi alanı yazarak başla.</p></div>
        <?php elseif (empty($results) && empty($topicResults) && empty($entryResults)): ?>
            <div class="dc-search-empty p-4 bg-white rounded-xl shadow text-gray-600"><span class="dc-search-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span><strong>Bu aramayla eşleşme yok</strong><p>Yazımı kontrol et veya daha kısa bir kelime dene.</p></div>
        <?php else: ?>
          <?php if (!empty($topicResults)): ?>
            <p class="dc-search-count">Konu başlıkları · <?= count($topicResults) ?> sonuç</p>
            <ul class="space-y-3 mb-8">
              <?php foreach ($topicResults as $topic): ?>
                <li><a href="topic.php?id=<?= (int) $topic['id'] ?>" class="block rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                  <p data-dc-user-content class="font-bold text-gray-900"><?= htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8') ?></p>
                  <p data-dc-user-content class="mt-1 text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars($topic['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                  <span data-dc-user-content class="mt-2 block text-xs font-semibold text-gray-400">@<?= htmlspecialchars($topic['username'], ENT_QUOTES, 'UTF-8') ?></span>
                </a></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <?php if (!empty($entryResults)): ?>
            <p class="dc-search-count">Gönderiler · <?= count($entryResults) ?> sonuç</p>
            <ul class="space-y-3 mb-8">
              <?php foreach ($entryResults as $entry): ?>
                <li>
                  <a href="topic.php?id=<?= (int) $entry['topic_id'] ?>" class="block rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p data-dc-user-content class="font-bold text-gray-900"><?= htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p data-dc-user-content class="mt-2 text-sm text-gray-600 line-clamp-3"><?= htmlspecialchars(strip_tags($entry['content']), ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-2 block text-xs font-semibold text-gray-400"><?= htmlspecialchars((string) $entry['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <?php if (!empty($results)): ?>
            <p class="dc-search-count">Öğrenci profilleri · <?= count($results) ?> sonuç</p>
            <ul class="space-y-4">
                <?php foreach ($results as $r):
                    $profilePhoto = getProfilePhoto($r['profile_photo']);
                    $userLink = "uye.php?user=" . urlencode($r['username']);
                ?>
                    <li>
                        <a href="<?= $userLink ?>" class="block bg-white p-4 rounded-xl shadow hover:shadow-lg hover:bg-gray-50 transition">
                            <div class="flex items-center gap-4">
                                <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="<?= htmlspecialchars($r['username']) ?> profil fotoğrafı" class="w-14 h-14 rounded-full object-cover flex-shrink-0">
                                <div class="flex-1">
                                    <p data-dc-user-content class="font-semibold text-gray-900">@<?= htmlspecialchars($r['username']) ?></p>
                                    <p data-dc-user-content class="text-gray-500 text-sm line-clamp-2"><?= htmlspecialchars($r['bio'] ?? '') ?></p>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
