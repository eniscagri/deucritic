<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

$stmt = $pdo->prepare("SELECT id, username, bio, profile_photo FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.');
    }

    $bio = trim($_POST['bio'] ?? '');
    $profilePhotoPath = $user['profile_photo'];

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $photo = $_FILES['profile_photo'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = isset($photo['tmp_name']) && is_uploaded_file($photo['tmp_name']) ? $finfo->file($photo['tmp_name']) : false;
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

        if ($photo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($photo['tmp_name'])) {
            $errors[] = "Görsel yüklenemedi. Lütfen yeniden deneyin.";
        } elseif ($photo['size'] > 5 * 1024 * 1024) {
            $errors[] = "Dosya boyutu çok büyük, lütfen 5MB'ı geçmeyen bir fotoğraf seçin.";
        } elseif (!isset($extensions[$mime]) || @getimagesize($photo['tmp_name']) === false) {
            $errors[] = "Sadece geçerli JPG, PNG, GIF veya WEBP görselleri yükleyebilirsiniz.";
        } else {
            $uploadDir = __DIR__ . '/uploads/profile_photos/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                $errors[] = "Görsel şu anda kaydedilemiyor.";
            } else {
                $newName = 'profile_' . (int) $_SESSION['user_id'] . '_' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                $uploadPath = $uploadDir . $newName;
                if (!move_uploaded_file($photo['tmp_name'], $uploadPath)) {
                    $errors[] = "Dosya yüklenirken sistemsel bir hata oluştu.";
                } else {
                    $profilePhotoPath = 'uploads/profile_photos/' . $newName;
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_photo = ? WHERE id = ?");
        $stmt->execute([$bio, $profilePhotoPath, $_SESSION['user_id']]);
        
        header('Location: index.php');
        exit;
    }
}

$defaultAvatar = !empty($user['profile_photo']) 
    ? $user['profile_photo'] 
    : 'https://ui-avatars.com/api/?background=44b5d5&color=fff&bold=true&size=256&name=' . urlencode($user['username']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hoşgeldin - DeuCritic</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<script>
    tailwind.config = { 
        theme: { 
            extend: { 
                fontFamily: { sans: ['Inter', 'sans-serif'] },
                colors: { primary: '#b84834', secondary: '#e9c65a', dark: '#1d352f' }
            } 
        } 
    };

    function previewImage(event) {
        const file = event.target.files[0];
        if(file) {
            const reader = new FileReader();
            reader.onload = () => { 
                document.getElementById('preview').src = reader.result; 
                // Fotoğraf seçilince atla butonunu gizle, kaydet butonunu vurgula
                document.getElementById('saveBtn').innerHTML = 'Fotoğrafı Kaydet ve Başla 🚀';
            }
            reader.readAsDataURL(file);
        }
    }
</script>
<style>
    .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
<link rel="icon" href="/gorsel/favicon.ico?v=20261104" type="image/x-icon" />
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">

<div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8 md:p-10 relative overflow-hidden animate-fadeIn">
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary/20 rounded-full blur-3xl"></div>

    <div class="relative z-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-dark mb-2 tracking-tight">
                Hoş Geldin, <span class="text-primary"><?= htmlspecialchars($user['username']) ?></span>! 🎉
            </h1>
            <p class="text-slate-500 font-medium">Topluluğa katılmadan önce hesabına biraz renk katalım.</p>
        </div>

        <?php if($errors): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-6 shadow-sm text-sm font-medium">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <!-- Profil Fotoğrafı Yükleme Alanı -->
            <div class="flex flex-col items-center justify-center mb-4">
                <div class="relative group cursor-pointer">
                    <img id="preview" src="<?= htmlspecialchars($defaultAvatar) ?>" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg group-hover:opacity-80 transition-opacity duration-300">
                    
                    <label for="profile_photo" class="absolute bottom-1 right-1 bg-primary p-2.5 rounded-full cursor-pointer border-4 border-white shadow-md hover:scale-110 transition-transform duration-300" title="Fotoğraf Yükle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg, image/png, image/webp, image/gif" onchange="previewImage(event)" class="hidden">
                </div>
                <p class="text-xs text-slate-400 mt-3 font-medium">JPEG, PNG veya WEBP (Maks 5MB)</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Takma Adın</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled
                    class="w-full p-3 rounded-xl border-2 border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <label for="bio" class="block text-sm font-semibold text-slate-700 mb-1">Kısa Biyografi (İsteğe bağlı)</label>
                <textarea name="bio" id="bio" rows="2" placeholder="Kendinden kısaca bahset..." 
                    class="w-full p-3 rounded-xl border-2 border-slate-200 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition resize-none"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>

            <div class="flex flex-col gap-3 mt-8">
                <button type="submit" id="saveBtn"
                        class="w-full bg-primary text-white p-3.5 rounded-xl font-extrabold text-lg hover:bg-[#963926] transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-1">
                    Bilgileri Onayla ve Giriş Yap
                </button>
                
                <a href="index.php" 
                   class="w-full text-slate-500 p-3 rounded-xl text-center font-bold hover:bg-slate-100 hover:text-slate-800 transition-colors">
                    Şimdilik Geç (Varsayılan Avatarı Kullan)
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
