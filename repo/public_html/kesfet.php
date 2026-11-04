<?php
require_once 'includes/config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            e.id AS entry_id, e.content, e.created_at, e.likes,
            u.username, u.id AS user_id,
            t.title, t.id AS title_id
        FROM entries e
        JOIN users u ON e.user_id = u.id
        JOIN titles t ON e.title_id = t.id
        ORDER BY e.created_at DESC
        LIMIT 15
    ");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $entries = [];
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    echo json_encode($entries);
    exit;
}


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <title>Keşfet - Deucritic Sözlük</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-4">
        <a href="index.php" class="flex items-center gap-2">
            <img src="/gorsel/logo.png" alt="Logo" class="h-8 w-auto">
            <span class="text-xl font-bold text-primary">Deucritic</span>
        </a>
          <nav class="hidden md:flex gap-6 items-center">
            <a href="index.php" class="text-gray-700 hover:text-primary transition">Ana Sayfa</a>
            <a href="kesfet.php" class="text-gray-700 hover:text-primary transition">Keşfet</a>
            <a href="profile.php?id=<?= $userId ?>" class="text-gray-700 hover:text-primary transition">Profilim</a>


            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="text-gray-700 hover:text-primary transition">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-700 hover:text-primary transition">Giriş Yap</a>
            <?php endif;
            if (isset($_SESSION['user_id'])): ?>
                <a href="index.php#yarat" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-cyan-700 transition">Yeni Entry</a>
            <?php else: ?>
                <a href="register.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-cyan-700 transition">Kayıt Ol</a>
            <?php endif;
            ?>
          </nav>
     <div class="md:hidden">
            <button id="mobile-menu-toggle" class="text-gray-600 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>
     <div id="mobile-menu" class="md:hidden hidden px-4 pb-4">
        <nav class="flex flex-col space-y-2">
            <a href="index.php" class="text-gray-700 hover:text-primary transition">Ana Sayfa</a>
            <a href="kesfet.php" class="text-gray-700 hover:text-primary transition">Keşfet</a>
            <a href="profile.php?id=<?= $userId ?>" class="text-gray-700 hover:text-primary transition">Profilim</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="text-gray-700 hover:text-primary transition">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php" class="text-gray-700 hover:text-primary transition">Giriş Yap</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php#yarat" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-cyan-700 transition">Yeni Entry</a>
            <?php else: ?>
                <a href="register.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-cyan-700 transition">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
     </div>
     <script>
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const toggleButton = document.getElementById('mobile-menu-toggle');
            if (!menu.contains(event.target) && !toggleButton.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

     </script>
</header>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-6 text-primary text-center">🔍 Keşfet</h1>

        <?php if (empty($entries)): ?>
            <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-4 rounded text-center shadow">
                Şu an hiç entry yok 😢 İlk yazan sen olabilirsin!
            </div>
        <?php else: ?>
            <?php foreach ($entries as $entry): ?>
                <div class="bg-white rounded-xl shadow-md p-5 mb-6 transition hover:shadow-lg">
                    <div class="text-sm text-gray-500 mb-1 flex justify-between items-center">
                        <a href="profile.php?id=<?= $entry['user_id'] ?>" class="font-semibold text-primary hover:underline">
                            <?= htmlspecialchars($entry['username']) ?>
                        </a>
                        <span><?= date("d M Y, H:i", strtotime($entry['created_at'])) ?></span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">
                        <a href="title.php?id=<?= $entry['title_id'] ?>" class="hover:underline text-gray-800">#<?= htmlspecialchars($entry['title']) ?></a>
                    </h2>
                    <p class="text-gray-800 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($entry['content'])) ?></p>
                    <div class="mt-3 flex items-center justify-between">
                        <button
                            onclick="likeEntry(<?= $entry['entry_id'] ?>)"
                            class="text-sm text-primary hover:underline flex items-center gap-1"
                        >
                            <i class="fa-solid fa-heart"></i> <span id="likes-<?= $entry['entry_id'] ?>"><?= $entry['likes'] ?? 0 ?></span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="bg-gray-50 border border-gray-200 text-gray-700 p-4 rounded-lg shadow-sm mb-6">
            <h2 class="text-xl font-semibold mb-3">Nasıl Keşfedebilirim?</h2>
            <p class="text-black-600">
                Bu sayfada, son yazılan entry'leri keşfedebilirsiniz. Beğendiğiniz entry'lere tıklayarak daha fazla bilgi alabilir ve yazarın profilini ziyaret edebilirsiniz.
            </p>
            <p class="text-gray-600 mt-2">
                Eğer bir entry'yi beğenirseniz, <i class="fa-solid fa-heart"></i> butonuna tıklayarak destekleyebilirsiniz. Beğeniler, entry'nin popülerliğini artırır.

        <div class="text-center mt-8">
            <a href="index.php" class="text-primary underline hover:text-cyan-700">Baba Sayfa</a>
        </div>
    </div>


    <script>
        function likeEntry(entryId) {
            fetch('like_entry_kesfet.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'entry_id=' + encodeURIComponent(entryId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likesSpan = document.getElementById('likes-' + entryId);
                    likesSpan.textContent = data.likes;
                }
            })
            .catch(err => console.error('Like error:', err));
        }
    </script>
</body>
</html>
