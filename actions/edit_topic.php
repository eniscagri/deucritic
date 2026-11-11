<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/content_moderation.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$topic_id = $_GET['id'] ?? null;

if (!$topic_id) die('Geçersiz istek.');

$stmt = $pdo->prepare("SELECT * FROM topics WHERE id=? AND created_by=?");
$stmt->execute([$topic_id, $user_id]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topic) die('Gönderi bulunamadı veya erişim izni yok.');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Geçersiz güvenlik isteği.';
    } else {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);

        // Hem başlık hem de karakter sınırını arka planda da doğruluyoruz
        if (dcFindProhibitedContent($title . ' ' . $description) !== null) {
            $error = 'Başlık ve açıklama küfür, hakaret veya +18 içerik içeremez.';
        } elseif (!$title) {
            $error = 'Başlık boş olamaz.';
        } elseif (mb_strlen($description) > 2000) {
            $error = 'Açıklama 2000 karakterden uzun olamaz.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE topics SET title=?, description=? WHERE id=? AND created_by=?");
                $stmt->execute([$title, $description, $topic_id, $user_id]);

                $success = 'Gönderin başarıyla güncellendi. Yönlendiriliyorsun...';

                // Güncel verileri anında yansıt
                $topic['title'] = $title;
                $topic['description'] = $description;

                // 2 saniye sonra yönlendir
                header("Refresh:2; url=../profile.php");
            } catch (PDOException $e) {
                $error = 'Veritabanı güncellenirken bir hata oluştu: ' . e($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gönderiyi Düzenle | Deucritic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0891b2', // Deucritic Cyan
                        primaryHover: '#0e7490'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f0f2f5; font-family: system-ui, -apple-system, sans-serif; }
        .fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Özel Scrollbar */
        textarea::-webkit-scrollbar { width: 8px; }
        textarea::-webkit-scrollbar-track { background: transparent; }
        textarea::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        textarea::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="max-w-2xl w-full bg-white p-6 md:p-8 rounded-2xl shadow-[0_2px_10px_-3px_rgba(0,0,0,0.1)] border border-gray-100 fade-in">

    <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-primary"></i> Gönderiyi Düzenle
        </h2>
        <a href="../profile.php" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition" title="İptal Et ve Kapat">
            <i class="fa-solid fa-xmark text-lg"></i>
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-exclamation mt-1"></i>
            <p><?= e($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-check mt-1 text-green-500 text-lg"></i>
            <div class="w-full">
                <p class="font-semibold mb-2"><?= e($success) ?></p>
                <div class="w-full bg-green-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-green-500 h-1.5 rounded-full animate-[progress_2s_ease-in-out_forwards]" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <style>
            @keyframes progress { to { width: 100%; } }
        </style>
    <?php endif; ?>

    <form method="POST" class="space-y-6" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Başlık</label>
            <input type="text" name="title" value="<?= e($topic['title']) ?>" maxlength="150"
                class="w-full bg-[#f0f2f5] border border-transparent rounded-xl px-4 py-3 text-gray-800 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all shadow-inner"
                placeholder="Gönderin için dikkat çekici bir başlık..."
                required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">İçerik</label>
            <div class="relative group">
                <textarea name="description" id="desc" maxlength="2000"
                    class="w-full bg-[#f0f2f5] border border-transparent rounded-xl px-4 py-3 pb-8 text-gray-800 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all resize-none min-h-[180px] shadow-inner"
                    placeholder="Gönderi içeriğini buraya yaz..."
                    required><?= e($topic['description']) ?></textarea>

                <div class="absolute bottom-3 right-3 bg-white/80 backdrop-blur-sm px-2 py-1 rounded shadow-sm border border-gray-100 pointer-events-none transition-opacity opacity-70 group-focus-within:opacity-100">
                    <p class="text-[11px] font-bold text-gray-500 transition-colors" id="charCountText">
                        <span id="charCount">0</span>/2000
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="../profile.php" class="px-5 py-2.5 rounded-xl font-semibold text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                İptal Et
            </a>
            <button type="submit" <?= $success ? 'disabled' : '' ?>
                    class="bg-primary text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-primaryHover hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2 <?= $success ? 'opacity-50 cursor-not-allowed' : '' ?>">
                <i class="fa-solid fa-check"></i> Kaydet
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const desc = document.getElementById('desc');
    const counter = document.getElementById('charCount');
    const charCountText = document.getElementById('charCountText');

    if(desc) {
        // Karakter sayacını güncelleyen fonksiyon
        const updateCounter = () => {
            const len = desc.value.length;
            counter.textContent = len;

            // Kullanıcı sınıra (2000) yaklaşırsa (son 100 karakter kala) rengi kırmızı yap
            if (len > 1900) {
                charCountText.classList.remove('text-gray-500');
                charCountText.classList.add('text-red-500');
            } else {
                charCountText.classList.remove('text-red-500');
                charCountText.classList.add('text-gray-500');
            }
        };

        // Sayfa yüklendiğinde mevcut metin uzunluğunu hesapla
        updateCounter();

        // Kullanıcı her tuşa bastığında sayacı güncelle
        desc.addEventListener('input', updateCounter);
    }
});
</script>

</body>
</html>
