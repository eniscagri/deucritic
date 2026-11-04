<?php
require_once 'includes/config.php';

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($topic_id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.title, u.username 
    FROM topics t 
    JOIN users u ON t.created_by = u.id 
    WHERE t.id = ?
");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch();

if (!$topic) {
    header('Location: index.php');
    exit;
}
$stmt = $pdo->prepare("
    SELECT e.id, e.content, e.created_at, u.username 
    FROM entries e 
    JOIN users u ON e.user_id = u.id 
    WHERE e.topic_id = ? 
    ORDER BY e.created_at ASC
");
$stmt->execute([$topic_id]);
$entries = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT description, anonymous 
    FROM topics 
    WHERE id = ?
");
$stmt->execute([$topic_id]);
$topicDetails = $stmt->fetch();
if ($topicDetails) {
    $topic['description'] = $topicDetails['description'];
    $topic['anonymous'] = $topicDetails['anonymous'];
} else {
    $topic['description'] = '';
    $topic['anonymous'] = 0;
}




$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lAnY="tr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="/uploads/default-avatar.png" type="image/x-icon" />
  <title><?= htmlspecialchars($topic['title']) ?> - Deucritic Sözlük</title>
  <meta name="description" content="DEU öğrencileri için anonim, samimi ve eğlenceli bir sözlük platformu. Oneplerini yaz, üniversite kültürünü birlikte yaşat.">
  <meta name="author" content="Deucritic Ekibi">
     <link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon" />
  <link rel="apple-touch-icon" sizes="180x180" href="/gorsel/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/gorsel/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/gorsel/favicon-16x16.png">

  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://deucritic.com/konu.php?id=<?= urlencode($topic_id) ?>">

  <meta property="og:title" content="<?= htmlspecialchars($topic['title']) ?> - Deucritic Sözlük">
  <meta property="og:description" content="Deucritic Sözlük'te DEU öğrencileriyle birlikte oneplerini paylaş. Goygoy, bilgi, dedikodu burada.">
  <meta property="og:image" content="https://deucritic.com/gorsel/og-image.jpg">
  <meta property="og:url" content="https://deucritic.com/konu.php?id=<?= urlencode($topic_id) ?>">
  <meta name="twitter:card" content="summary_large_image">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#44b5d5',
            secondary: '#f3cc8b',
          },
          fontFamily: {
            sans: ['Inter', 'Arial', 'sans-serif'],
          },
        },
      },
    };
  </script>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f9fafb;
    }
    .text-primary {
      color: #44b5d5;
    }
    .bg-primary {
      background-color: #44b5d5;
    }
    .bg-secondary {
      background-color: #f3cc8b;
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
<header class="bg-primary text-white shadow">
    <div class="container mx-auto p-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <a href="index.php">
                <img src="/gorsel/logo.png" alt="Logo" class="w-10 h-10" />
            </a>
            <h1 class="text-2xl font-bold">Deucritic Sözlük</h1>
        </div>
        <nav class="flex gap-4">
            <?php if ($is_logged_in): ?>
        <a href="profile.php" class="hover:underline">Profilim</a>|

                <a href="logout.php" class="hover:underline">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php" class="hover:underline">Giriş</a>
                <a href="register.php" class="hover:underline">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </div>

</header>
<main class="container mx-auto mt-8 p-6 bg-white rounded-2xl shadow-lg min-h-[60vh]">
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4" style="margin-top: 10px;" >
        <div>
            <h2 class="text-3xl font-bold text-primary mb-1"><?= htmlspecialchars($topic['title']) ?></h2>
            <?php if (!empty($topic['description'])): ?>
                <p class="text-gray-700 mb-1"><?= nl2br(htmlspecialchars($topic['description'])) ?></p>
            <?php endif; ?>
           
        </div>  
        <div class="flex items-center gap-3 text-gray-500 text-sm mb-2">
            <span class="font-semibold flex items-center gap-1">
            <?php
                if (!empty($topic['anonymous'])) {
                echo '<svg class="w-4 h-4 text-primary inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h1M4 12H3m15.364-6.364l.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707" /></svg> Anonim';
                } else {
                echo '<svg class="w-4 h-4 text-primary inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg> ' . htmlspecialchars($topic['username']);
                }
            ?>
            </span>
            <span class="hidden md:inline">•</span>
            <span class="italic flex items-center gap-1">
            <?php
                $stmt = $pdo->prepare("SELECT created_at FROM topics WHERE id = ?");
                $stmt->execute([$topic_id]);
                $topic_created = $stmt->fetchColumn();
                if ($topic_created) {
                echo date('d.m.Y H:i', strtotime($topic_created));
                }
            ?>
            </span>
            <?php if (!empty($topic['anonymous'])): ?>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                var btn = document.getElementById('anonymousInfoBtn2');
                var modal = document.getElementById('anonymousInfoModal');
                if (btn && modal) {
                    btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.classList.remove('hidden');
                    });
                }
                });
            </script>
            <?php endif; ?>
        </div>
    </div>
    <section class="mb-10" style="margin-top: 20px ;">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-2xl font-semibold text-secondary">Onepler</h3>
        </div>
       
        <?php if (!$entries): ?>
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10a2 2 0 012-2h2M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V3m0 0L8.5 7.5M12 3l3.5 4.5" />
                </svg>
                <p class="text-gray-500 text-lg">Henüz bu konu hakkında onep yazılmamış. 
                </p>
                
            </div>
        <?php else: ?>
            <div class="space-y-6">
            <?php foreach ($entries as $entry): ?>
                <div class="group border border-gray-200 rounded-xl p-5 bg-gray-50 hover:bg-gray-100 transition relative shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
                    <?= strtoupper(mb_substr($entry['username'], 0, 1)) ?>
                    </div>
          <span class="block text-xs text-gray-400">
            <?php if (!empty($topic['anonymous'])): ?>
              AnY Kullanıcı
            <?php else: ?>
              <a href="uye.php?user=<?= urlencode($entry['username']) ?>" class="hover:underline text-primary">
                @<?= htmlspecialchars($entry['username']) ?>
              </a>
            <?php endif; ?>
          </span>           
                </div>
                    <?= nl2br(htmlspecialchars($entry['content'])) ?>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                    <span>
                        
                        <?= date('d.m.Y H:i', strtotime($entry['created_at'])) ?>
                    </span>
                </div>
                <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition">
                    <?php
                    $likeStmt = $pdo->prepare("SELECT COUNT(*) FROM entry_likes WHERE entry_id = ?");
                    $likeStmt->execute([$entry['id'] ?? 0]);
                    $likeCount = $likeStmt->fetchColumn();

                    $userLiked = false;
                    if ($is_logged_in) {
                        $userLikeStmt = $pdo->prepare("SELECT 1 FROM entry_likes WHERE entry_id = ? AND user_id = ?");
                        $userLikeStmt->execute([$entry['id'] ?? 0, $_SESSION['user_id']]);
                        $userLiked = $userLikeStmt->fetchColumn() ? true : false;
                    }
                    ?>
                    <form action="actions/like_entry.php" method="POST" class="like-form">
                    <input type="hidden" name="entry_id" value="<?= $entry['id'] ?? '' ?>">
                    <button type="submit"
                        class="text-green-600 hover:underline text-sm flex items-center gap-1 <?= $userLiked ? 'font-bold' : '' ?>"
                        <?= !$is_logged_in ? 'disabled title="Giriş yapmalısınız"' : '' ?>>
                        <svg class="w-4 h-4 <?= $userLiked ? 'fill-green-600' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9V5a3 3 0 00-6 0v4m6 0a3 3 0 01-6 0m6 0v6a3 3 0 01-6 0V9"></path>
                        </svg>
                        <?= $userLiked ? 'Kaldır' : 'Muqaddesle' ?>
                        <span class="like-count">(<?= $likeCount ?>)</span>
                    </button>
                    </form>
                </div>
                </div>
            <?php endforeach; ?>
            </div>
            <script>
            document.querySelectorAll('.like-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('actions/like_entry.php', {
                    method: 'POST',
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                    const btn = form.querySelector('button');
                    const countSpan = form.querySelector('.like-count');
                    countSpan.textContent = `(${data.like_count})`;
                    if (data.liked) {
                        btn.classList.add('font-bold');
                        btn.querySelector('svg').classList.add('fill-green-600');
                        btn.innerHTML = btn.innerHTML.replace('Beğendin', 'Muqaddes');
                    } else {
                        btn.classList.remove('font-bold');
                        btn.querySelector('svg').classList.remove('fill-green-600');
                        btn.innerHTML = btn.innerHTML.replace('Beğendin', 'Muqaddes');
                    }
                    } else if (data.error) {
                    alert(data.error);
                    }
                });
                });
            });
            </script>
        <?php endif; ?>
    </section>



    <?php if ($is_logged_in): ?>
        <section class="mt-10">
            <h3 class="text-2xl font-semibold text-secondary mb-3">Onep Yaz</h3>
            <form action="actions/post_entry.php" method="POST" class="flex flex-col gap-4 bg-gray-50 rounded-xl p-6 shadow" id="entryForm">
            <textarea name="content" required rows="4" maxlength="500"
                class="border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary transition resize-none"
                placeholder="Onepinizi yazın..." id="entryContent"></textarea>
            <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">
                <span id="charCount">0</span>/500 karakter
                </span>
                <button type="submit"
                class="px-6 py-2 bg-secondary text-black font-semibold rounded-lg shadow hover:bg-yellow-300 transition">
                Gönder
                </button>
            </div>
            <div id="entryError" class="text-red-500 text-xs mt-2 hidden"></div>
            </form>
        </section>
        <style>
            .break-words {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: pre-line;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.entry-content').forEach(function(el) {
                el.classList.add('break-words');
            });
            });
        </script></style>
        <script>
            const textarea = document.getElementById('entryContent');
            const charCount = document.getElementById('charCount');
            textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            });

            document.getElementById('entryForm').addEventListener('submit', function(e) {
            const errorDiv = document.getElementById('entryError');
            errorDiv.classList.add('hidden');
            if (textarea.value.trim().length < 5) {
                e.preventDefault();
                errorDiv.textContent = "Onep en az 5 karakter olmalı.";
                errorDiv.classList.remove('hidden');
                textarea.focus();
            }
            });
        </script>
    <?php else: ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-xl mt-8 flex items-center gap-2">
            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h.01" />
            </svg>
            <p class="text-gray-700">
                Onep yazabilmek için
                <a href="login.php" class="text-primary underline font-semibold">giriş yapmanız</a>
                gerekiyor.
            </p>
        </div>
    <?php endif; ?>

        
    </div></a>
        <?php if ($is_logged_in && !empty($topic['anonymous'])): ?>
            <div class="mt-6">

            <button id="anonymousInfoBtn" class="text-xs text-gray-500 hover:text-primary ml-2">
                AnY Başlık Nedir?
            </button>
            <div id="anonymousInfoModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-6 max-w-md w-full relative">
                    <button id="closeAnonymousInfo" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
                    <h4 class="text-lg font-bold mb-2 text-primary">AnY Başlık Nedir?</h4>
                    <p class="text-gray-700 text-sm">
                      AnY (Anonim Yazı), kullanıcıların kimliklerini gizleyerek özgürce fikir paylaşabildiği bir platformdur. AnY olarak açılan başlıklardaki ve oneplerdeki tüm katılımcılar AnY Kullanıcı olarak görünür. Ancak, yazılan onepler kişinin kendi profilinde listelenir, yani profiline ulaşan biri anonim yazılarını görebilir. <b>Başlığı ilk açanın profili bu başlıktan muaftır.</b>
                    </p>
                </div>
                
                
            </div>
            </div>
            <script>
                document.getElementById('anonymousInfoBtn').addEventListener('click', function() {
                    document.getElementById('anonymousInfoModal').classList.remove('hidden');
                });
                document.getElementById('closeAnonymousInfo').addEventListener('click', function() {
                    document.getElementById('anonymousInfoModal').classList.add('hidden');
                });
                document.getElementById('anonymousInfoModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                    }
                });
            </script></div>
            <?php endif; ?>
   <div class="text-xs text-gray-400 mt-2 md:mt-0" style="margin-top: 10px;">
            <span>Bu başlık <?= count($entries) ?> onep içeriyor.</span>

</main>


</body>
</html>
