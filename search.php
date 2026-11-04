<?php
require_once __DIR__ . '/includes/config.php';

$username = 'Ziyaretçi';
$userId = null;
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT id, username, email, school, grade_level, profile_photo, bio FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
        $username = $user['username'];
    } else {
        session_destroy();
        $isLoggedIn = false;
    }
}

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $sql = "SELECT id, username, bio, profile_photo FROM users WHERE username LIKE :q OR bio LIKE :q LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%{$q}%";
    $stmt->bindParam(':q', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include __DIR__ . '/header.php'; ?>

    <main class="max-w-3xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Arama Sonuçları</h1>

        <!-- Arama kutusu -->
        <form method="GET" class="mb-6 flex gap-2">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Kullanıcı adı veya bio ara..." class="flex-1 p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
            <button type="submit" class="bg-primary text-white px-4 py-3 rounded-xl hover:bg-cyan-600 transition">Ara</button>
        </form>

        <?php if ($q === ''): ?>
            <div class="p-4 bg-white rounded-xl shadow text-gray-600">Bir şey aramak için yukarıdaki kutuyu kullan.</div>
        <?php elseif (empty($results)): ?>
            <div class="p-4 bg-white rounded-xl shadow text-gray-600">Sonuç bulunamadı.</div>
        <?php else: ?>
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
                                    <p class="font-semibold text-gray-900">@<?= htmlspecialchars($r['username']) ?></p>
                                    <p class="text-gray-500 text-sm line-clamp-2"><?= htmlspecialchars($r['bio'] ?? '') ?></p>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
