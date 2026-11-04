<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$logged_user_id = $_SESSION['user_id'];
$username_param = $_GET['user'] ?? null;

try {
    if ($username_param) {
        $stmt = $pdo->prepare("SELECT id, username, email, recovery_email, school, grade_level, profile_photo, cover_photo, bio FROM users WHERE username=?");
        $stmt->execute([$username_param]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_own_profile = $user['id'] == $logged_user_id;
    } else {
        $stmt = $pdo->prepare("SELECT id, username, email, recovery_email, school, grade_level, profile_photo, cover_photo, bio FROM users WHERE id=?");
        $stmt->execute([$logged_user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $is_own_profile = true;
    }

    if (!$user) die('Kullanıcı bulunamadı.');
} catch (PDOException $e) {
    die("Veritabanı hatası: " . e($e->getMessage()));
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

$form = [
    'username' => $user['username'],
    'email' => $user['email'],
    'recovery_email' => $user['recovery_email'] ?? '',
    'school' => $user['school'],
    'grade_level' => $user['grade_level'],
    'bio' => $user['bio'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = 'Geçersiz istek (CSRF doğrulanamadı)!';
    } 
    elseif (isset($_POST['update_cover'])) {
        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (in_array($_FILES['cover_photo']['type'], $allowed_types)) {
                $ext = pathinfo($_FILES['cover_photo']['name'], PATHINFO_EXTENSION);
                $filename = 'cover_' . $logged_user_id . '_' . time() . '.' . $ext;
                
                $upload_dir = __DIR__ . '/uploads/cover_photos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $target = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $target)) {
                    $cover_photo_path = 'uploads/cover_photos/' . $filename;
                    
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET cover_photo=? WHERE id=?");
                        $stmt->execute([$cover_photo_path, $logged_user_id]);
                        $user['cover_photo'] = $cover_photo_path; 
                        $success_msg = 'Kapak fotoğrafın başarıyla güncellendi!';
                    } catch (PDOException $e) {
                        $error_msg = 'Veritabanı hatası: ' . e($e->getMessage());
                    }
                } else {
                    $error_msg = 'Dosya sunucuya taşınırken bir hata oluştu.';
                }
            } else {
                $error_msg = 'Kapak fotoğrafı sadece JPG, PNG, WEBP veya GIF olabilir.';
            }
        } else {
            $error_msg = 'Lütfen geçerli bir dosya seçin.';
        }
    } 
    else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $recovery_email = trim($_POST['recovery_email'] ?? '');
        $school = trim($_POST['school'] ?? '');
        $grade = trim($_POST['grade_level'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        $form = ['username' => $username, 'email' => $email, 'recovery_email' => $recovery_email, 'school' => $school, 'grade_level' => $grade, 'bio' => $bio];
        $profile_photo = $user['profile_photo'];

        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['profile_photo']['type'], $allowed_types)) {
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $logged_user_id . '_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/uploads/profile_photos/';
                
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $target = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
                    $profile_photo = 'uploads/profile_photos/' . $filename;
                } else {
                    $error_msg = 'Profil fotoğrafı yüklenemedi.';
                }
            } else {
                $error_msg = 'Sonu JPG, PNG, WEBP veya GIF olmayan dosya yüklenemez.';
            }
        }

        if (!$username || !$email) {
            $error_msg = 'Kullanıcı adı ve e-posta boş olamaz.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Geçerli bir e-posta giriniz.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (LOWER(email)=LOWER(?) OR LOWER(username)=LOWER(?)) AND id!=?");
            $stmt->execute([$email, $username, $logged_user_id]);
            
            if ($stmt->rowCount() > 0) {
                $error_msg = 'Bu kullanıcı adı veya e-posta başka bir kullanıcı tarafından kullanılıyor.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, recovery_email=?, school=?, grade_level=?, profile_photo=?, bio=? WHERE id=?");
                    $stmt->execute([$username, $email, $recovery_email, $school, $grade, $profile_photo, $bio, $logged_user_id]);
                    
                    $success_msg = 'Profilin başarıyla güncellendi.';
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    
                    $user = array_merge($user, $form, ['profile_photo' => $profile_photo]);
                } catch (PDOException $e) {
                    $error_msg = 'Bir hata oluştu: ' . e($e->getMessage());
                }
            }
        }
    }
}

$is_following = false;
if (!$is_own_profile) {
    $stmt = $pdo->prepare("SELECT id FROM followers WHERE follower_id=? AND followed_id=?");
    $stmt->execute([$logged_user_id, $user['id']]);
    $is_following = $stmt->rowCount() > 0;
}

$stmt = $pdo->prepare("SELECT u.username, u.profile_photo FROM followers f JOIN users u ON f.follower_id=u.id WHERE f.followed_id=?");
$stmt->execute([$user['id']]);
$followers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT u.username, u.profile_photo FROM followers f JOIN users u ON f.followed_id=u.id WHERE f.follower_id=?");
$stmt->execute([$user['id']]);
$following = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT b.name, b.description, b.icon FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = ? ORDER BY ub.unlocked_at DESC");
$stmt->execute([$user['id']]);
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id, title, description, created_at, anonymous FROM topics WHERE created_by = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$autoFollowId = 6;
if ($logged_user_id != $autoFollowId) {
    $stmt = $pdo->prepare("SELECT id FROM followers WHERE follower_id=? AND followed_id=?");
    $stmt->execute([$logged_user_id, $autoFollowId]);
    if ($stmt->rowCount() === 0) {
        $stmtInsert = $pdo->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmtInsert->execute([$logged_user_id, $autoFollowId]);
    }
}

function getProfilePhotoUrl($photo_path, $default_avatar = 'gorsel/default-avatar.png') {
    if (!empty($photo_path) && file_exists(__DIR__ . '/' . $photo_path)) return $photo_path;
    return $default_avatar;
}

function getCoverPhotoUrl($photo_path) {
    if (!empty($photo_path) && file_exists(__DIR__ . '/' . $photo_path)) return $photo_path;
    return ''; 
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= e($user['username']) ?> - Profil | Deucritic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#b84834', secondary: '#e9c65a', dark: '#1d352f' }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; -webkit-tap-highlight-color: transparent; }
        .glass-header { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .tab-btn.active { background-color: #1d352f; color: white; shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .tab-btn { color: #64748b; }
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Kapak fotoğrafı yükleme butonu efekti */
        .cover-overlay { background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%); }
    </style>
    
    
    <!--profil kapak--->
    <script>
function openActionMenu(event, menuId) {
    event.stopPropagation();
    document.querySelectorAll('[id$="Actions"]').forEach(el => el.classList.add('hidden'));
    const menu = document.getElementById(menuId);
    menu.classList.toggle('hidden');
}

function viewFullImage(imgId) {
    const src = document.getElementById(imgId).src;
    if(!src) return;
    
    const lightbox = document.getElementById('imageLightbox');
    const lbImg = document.getElementById('lightboxImg');
    
    lbImg.src = src;
    lightbox.classList.remove('hidden');
    setTimeout(() => lbImg.classList.remove('scale-95'), 10);
    document.body.style.overflow = 'hidden'; // Scroll engelle
}

function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    lightbox.classList.add('hidden');
    document.getElementById('lightboxImg').classList.add('scale-95');
    document.body.style.overflow = '';
}

function handleAvatarChange(input) {
    if (input.files && input.files[0]) {
        // Avatar güncelleme işlemi için ana formunuza bir tetikleyici ekleyin
        // Örn: document.getElementById('mainEditForm').submit();
        alert('Yeni profil fotoğrafı seçildi, ayarlar panelinden kaydedebilirsiniz.');
    }
}

document.addEventListener('click', () => {
    document.querySelectorAll('[id$="Actions"]').forEach(el => el.classList.add('hidden'));
});

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") closeLightbox();
});
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.2s ease-out forwards;
}
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="text-slate-800 antialiased selection:bg-primary/20 selection:text-primary">
<?php include 'header.php'; ?>


<main class="container mx-auto mt-6 px-3 sm:px-4 max-w-5xl mb-12">
    
    <?php if ($error_msg): ?>
        <div class="auto-close-alert bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center gap-3 animate-fade-in fade-in">
            <i class="fa-solid fa-circle-exclamation"></i> <p class="font-semibold text-sm"><?= e($error_msg) ?></p>
        </div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
        <div class="auto-close-alert bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center gap-3 animate-fade-in fade-in">
            <i class="fa-solid fa-circle-check"></i> <p class="font-semibold text-sm"><?= e($success_msg) ?></p>
        </div>
    <?php endif; ?>

    <div id="imageLightbox" class="fixed inset-0 z-[100] hidden bg-black/90 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeLightbox()">
    <img id="lightboxImg" src="" class="max-w-full max-h-full rounded-lg shadow-2xl transition-transform duration-300 scale-95">
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden mb-8 relative">
    
    <div class="relative w-full h-48 sm:h-64 md:h-80 bg-gradient-to-br from-primary/20 via-cyan-300/20 to-secondary/20 group overflow-hidden">
        <?php $mini_cover = getCoverPhotoUrl($user['cover_photo'] ?? ''); ?>
        <img id="cover-preview" src="<?= $mini_cover ? e($mini_cover) : '' ?>" 
             class="<?= $mini_cover ? '' : 'hidden' ?> w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105 cursor-pointer"
             onclick="openActionMenu(event, 'coverActions')">
        
        <?php if (!$mini_cover && $is_own_profile): ?>
            <div class="absolute inset-0 flex items-center justify-center cursor-pointer" onclick="document.getElementById('coverInput').click()">
                <div class="bg-white/50 backdrop-blur-md px-4 py-2 rounded-full text-sm font-bold text-slate-600 border border-white/30">
                    <i class="fa-solid fa-plus mr-2"></i>Kapak Fotoğrafı Ekle
                </div>
            </div>
        <?php endif; ?>

        <div id="coverActions" class="hidden absolute top-4 right-4 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 z-20 min-w-[160px] animate-fade-in">
            <button onclick="viewFullImage('cover-preview')" class="w-full text-left px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors flex items-center gap-3">
                <i class="fa-solid fa-eye text-primary"></i> Görüntüle
            </button>
            <?php if ($is_own_profile): ?>
                <button onclick="document.getElementById('coverInput').click()" class="w-full text-left px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors flex items-center gap-3 border-t border-slate-50">
                    <i class="fa-solid fa-camera-rotate text-orange-500"></i> Değiştir
                </button>
            <?php endif; ?>
        </div>

        <form id="coverForm" method="POST" enctype="multipart/form-data" class="hidden">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="update_cover" value="1">
            <input type="file" name="cover_photo" id="coverInput" accept="image/*" onchange="this.form.submit()">
        </form>
    </div>

    <div class="relative px-6 sm:px-12 pb-10 flex flex-col sm:flex-row sm:items-end gap-5 sm:gap-8 -mt-14 sm:-mt-20">
        
        <div class="relative w-28 h-28 sm:w-40 sm:h-40 flex-shrink-0 z-10 group">
            <div class="w-full h-full rounded-[2.5rem] border-[6px] border-white shadow-2xl bg-slate-50 overflow-hidden cursor-pointer ring-1 ring-slate-100"
                 onclick="openActionMenu(event, 'avatarActions')">
                <img id="avatar-preview" src="<?= e(getProfilePhotoUrl($user['profile_photo'])) ?>" 
                     class="w-full h-full object-cover transition-all duration-500 group-hover:brightness-90">
            </div>

            <div id="avatarActions" class="hidden absolute top-full mt-3 left-0 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 z-20 min-w-[160px]">
                <button onclick="viewFullImage('avatar-preview')" class="w-full text-left px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors flex items-center gap-3">
                    <i class="fa-solid fa-expand text-primary"></i> Görüntüle
                </button>
                <?php if ($is_own_profile): ?>
                    <button onclick="document.getElementById('avatarInput').click()" class="w-full text-left px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 rounded-xl transition-colors flex items-center gap-3 border-t border-slate-50">
                        <i class="fa-solid fa-image-portrait text-orange-500"></i> Değiştir
                    </button>
                <?php endif; ?>
            </div>

            <input type="file" id="avatarInput" class="hidden" accept="image/*" onchange="handleAvatarChange(this)">
        </div>

        <div class="flex-1 pt-2 sm:pt-20">
            <div class="flex items-center gap-2 mb-1.5">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-none"><?= e($user['username']) ?></h1>
                <?php if($user['is_verified'] ?? false): ?>
                    <i class="fa-solid fa-circle-check text-primary text-xl" title="Onaylı Üye"></i>
                <?php endif; ?>
            </div>

            <?php if(!empty($user['bio'])): ?>
                <p class="text-slate-500 text-[15px] font-medium leading-relaxed max-w-xl mb-4 line-clamp-2">
                    <?= e($user['bio']) ?>
                </p>
            <?php endif; ?>
            
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                <div class="flex items-center gap-2 text-slate-400 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100">
                    <i class="fa-solid fa-graduation-cap text-primary"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600"><?= e($user['school'] ?: 'Kampüs Belirtilmedi') ?></span>
                </div>
                <div class="flex items-center gap-2 text-slate-400">
                    <i class="fa-solid fa-layer-group text-slate-300"></i>
                    <span class="text-xs font-extrabold uppercase tracking-widest leading-none"><?= e(ucfirst($user['grade_level'])) ?></span>
                </div>
            </div>
        </div>

        <div class="mt-2 sm:mt-0 sm:pb-2 sm:ml-auto w-full sm:w-auto flex-shrink-0 flex gap-2">
            <?php if ($is_own_profile): ?>
                <button id="toggleEditBtn" class="flex-1 sm:flex-none px-8 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold rounded-2xl transition-all flex items-center justify-center gap-3 transform active:scale-95 shadow-sm">
                    <i class="fa-solid fa-sliders text-primary"></i> Profili Düzenle
                </button>
            <?php else: ?>
                <button id="followBtn" data-user="<?= $user['id'] ?>" data-following="<?= $is_following ? '1' : '0' ?>"
                        class="flex-1 sm:flex-none px-8 py-3.5 rounded-2xl font-black transition-all shadow-md flex items-center justify-center gap-3 transform active:scale-95 <?= $is_following ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-slate-900 text-white hover:bg-slate-800' ?>">
                    <?= $is_following ? '<i class="fa-solid fa-user-minus"></i> Takibi Bırak' : '<i class="fa-solid fa-user-plus"></i> Takip Et' ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>


    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-4 space-y-6">
            
            <?php if ($is_own_profile): ?>
                <div id="editProfileSection" class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-100 hidden fade-in relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                    <h3 class="font-extrabold text-lg mb-5 text-dark">Ayarlar</h3>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Takma Ad</label>
                            <input type="text" name="username" value="<?= e($form['username']) ?>" required class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary focus:bg-white transition-all text-sm font-medium">
                        </div>
                        
                        <div class="opacity-70 cursor-not-allowed">
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider flex items-center gap-1">Okul E-postası <i class="fa-solid fa-lock text-[10px]"></i></label>
                            <input type="email" name="email" value="<?= e($form['email']) ?>" readonly class="w-full bg-slate-100 border border-transparent p-3 rounded-xl outline-none text-sm font-medium cursor-not-allowed">
                        </div>

                        <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                            <label class="block text-xs font-bold text-blue-900 mb-1 uppercase tracking-wider flex justify-between">
                                Kurtarma E-postası
                                <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded text-[9px]">GİZLİ</span>
                            </label>
                            <input type="email" name="recovery_email" value="<?= e($form['recovery_email']) ?>" placeholder="adin@gmail.com" class="w-full bg-white border border-blue-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm font-medium">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="opacity-70 cursor-not-allowed">
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Bölüm</label>
                                <input type="text" name="school" value="<?= e($form['school']) ?>" readonly class="w-full bg-slate-100 border border-transparent p-3 rounded-xl outline-none text-sm font-medium cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Sınıf</label>
                                <select name="grade_level" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" required>
                                    <option value="hazirlik" <?= $form['grade_level'] === 'hazirlik' ? 'selected' : '' ?>>Hazırlık</option>
                                    <?php for($i=1; $i<=7; $i++): ?>
                                        <option value="<?= $i ?>" <?= $form['grade_level'] == $i ? 'selected' : '' ?>><?= $i ?>. Sınıf</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Biyografi</label>
                            <textarea name="bio" rows="2" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none text-sm font-medium"><?= e($form['bio']) ?></textarea>
                        </div>

                        <div class="flex flex-col gap-3 pt-2">
                            <label class="cursor-pointer w-full text-center py-2.5 border-2 border-dashed border-slate-200 hover:border-primary hover:bg-primary/5 text-slate-500 hover:text-primary rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-image"></i> Yeni Avatar Seç
                                <input type="file" name="profile_photo" id="photoInput" accept="image/jpeg, image/png, image/webp" class="hidden">
                            </label>
                            
                            <div class="flex gap-2">
                                <button type="button" id="cancelEditBtn" class="w-1/3 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition">İptal</button>
                                <button type="submit" class="w-2/3 py-3 bg-dark text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition shadow-md">Değişiklikleri Kaydet</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($badges): ?>
           <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-100 transition-all hover:shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-extrabold text-slate-800 text-[16px] flex items-center gap-2.5 tracking-tight">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-100 to-yellow-50 text-amber-500 flex items-center justify-center shadow-inner border border-amber-200/50">
                <i class="fa-solid fa-medal"></i>
            </div>
            Kazanılan Rozetler
        </h3>
        <a href="badges.php" class="text-[11px] font-bold text-primary hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
            Tümünü Gör <i class="fa-solid fa-chevron-right text-[9px] mt-0.5"></i>
        </a>
    </div>

    <div class="flex flex-wrap gap-4">
        <?php if(empty($badges)): ?>
            <div class="w-full flex flex-col items-center justify-center py-8 bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-200 group hover:border-primary/30 transition-colors">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-award text-slate-300 group-hover:text-primary/40 text-2xl"></i>
                </div>
                <p class="text-[13px] font-bold text-slate-400">Henüz rozet kazanılmadı.</p>
                <?php if($is_own_profile): ?>
                    <a href="badges.php" class="text-[11px] font-bold text-primary mt-1 hover:underline">Rozetleri keşfet ve kazan!</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach($badges as $badge): ?>
                <a href="badges.php" class="group relative flex flex-col items-center">
                    
                    <div class="relative w-14 h-14 transition-transform duration-300 group-hover:scale-110 group-hover:-translate-y-1">
                        <div class="absolute inset-0 bg-primary/20 blur-md rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <?php if(!empty($badge['icon'])): ?>
                            <img src="<?= e($badge['icon']) ?>" alt="<?= e($badge['name']) ?>" class="w-full h-full object-contain relative z-10 drop-shadow-sm group-hover:drop-shadow-lg transition-all">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 rounded-full flex items-center justify-center font-black text-slate-500 text-xl relative z-10 shadow-inner group-hover:text-primary group-hover:border-primary/30 transition-colors">
                                <?= htmlspecialchars(mb_substr($badge['name'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="absolute -top-14 left-1/2 -translate-x-1/2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[100] pointer-events-none w-max max-w-[180px]">
                        <div class="bg-slate-900/95 backdrop-blur-md text-white px-3 py-2.5 rounded-xl shadow-2xl text-center border border-white/10">
                            <p class="text-[11px] font-black leading-tight text-amber-400 mb-1"><?= e($badge['name']) ?></p>
                            <?php if(!empty($badge['description'])): ?>
                                <p class="text-[10px] font-medium text-slate-200 leading-snug"><?= e($badge['description']) ?></p>
                            <?php endif; ?>
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2.5 h-2.5 bg-slate-900 rotate-45 border-r border-b border-white/10"></div>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 overflow-hidden">
                
                <div class="p-2 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex bg-slate-100/50 p-1 rounded-xl">
                        <button class="tab-btn active flex-1 py-2.5 text-sm font-bold rounded-lg transition-all" data-target="posts">
                            Gönderiler
                        </button>
                        <button class="tab-btn flex-1 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2" data-target="followers">
                            Takipçiler <span class="bg-white shadow-sm text-slate-600 text-[10px] px-2 py-0.5 rounded-full"><?= count($followers) ?></span>
                        </button>
                        <button class="tab-btn flex-1 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2" data-target="following">
                            Takip Edilen <span class="bg-white shadow-sm text-slate-600 text-[10px] px-2 py-0.5 rounded-full"><?= count($following) ?></span>
                        </button>
                    </div>
                </div>

                <div id="posts" class="tab-content divide-y divide-slate-100 fade-in">
                    <?php if ($topics): ?>
                        <?php foreach ($topics as $topic): ?>
                            <article class="p-6 hover:bg-slate-50/50 transition duration-300">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex-shrink-0 border border-slate-200 overflow-hidden">
                                            <img src="<?= e(getProfilePhotoUrl($user['profile_photo'])) ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h4 class="font-extrabold text-sm text-dark leading-tight">
                                                @<a href="uye.php?user=<?= e($user['username']) ?>" class="hover:underline"><?= e($user['username']) ?></a>
                                                <?php if($topic['anonymous']): ?>
                                                    <span class="text-[11px] font-semibold text-slate-400 ml-1 bg-slate-100 px-2 py-0.5 rounded-full"><i class="fa-solid fa-mask"></i> Anonim</span>
                                                <?php endif; ?>
                                            </h4>
                                            <span class="text-[11px] font-semibold text-slate-400">
                                                <?= date('d M Y, H:i', strtotime($topic['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($is_own_profile): ?>
                                    <div class="relative group">
                                        <button class="text-slate-400 hover:text-dark p-2 transition">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <div class="absolute right-0 mt-1 w-32 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right z-20">
                                            <div class="p-1">
                                                <a href="/actions/edit_topic.php?id=<?= e($topic['id']) ?>" class="flex items-center px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 rounded-lg transition"><i class="fa-solid fa-pen mr-2"></i> Düzenle</a>
                                                <a href="/actions/delete_topic.php?id=<?= e($topic['id']) ?>&csrf_token=<?= e($_SESSION['csrf_token']) ?>" onclick="return confirm('Silmek istediğine emin misin?')" class="flex items-center px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition"><i class="fa-solid fa-trash mr-2"></i> Sil</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-2 text-dark">
                                    <h5 class="font-extrabold text-base mb-1.5"><a href="topic.php?id=<?= e($topic['id']) ?>" class="hover:text-primary transition-colors"><?= e($topic['title']) ?></a></h5>
                                    <p class="text-sm font-medium leading-relaxed text-slate-600 break-words">
                                        <?= nl2br(e(strlen($topic['description']) > 300 ? mb_substr($topic['description'], 0, 300, 'UTF-8') . '...' : $topic['description'])) ?>
                                    </p>
                                    <?php if(strlen($topic['description']) > 300): ?>
                                        <a href="topic.php?id=<?= e($topic['id']) ?>" class="text-primary text-sm font-bold hover:underline mt-2 inline-block">Devamını Oku</a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-5 flex gap-4 text-xs font-bold text-slate-400">
                                    <a href="topic.php?id=<?= e($topic['id']) ?>" class="hover:bg-slate-100 hover:text-dark px-3 py-2 rounded-lg transition flex items-center gap-2"><i class="fa-regular fa-comment text-base"></i> Yorum Yap</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="py-20 px-6 text-center">
                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-5 border-2 border-dashed border-slate-200">
                                <i class="fa-solid fa-pen-nib text-4xl text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-extrabold text-dark mb-1">Buralar Çok Sessiz</h3>
                            <p class="text-sm font-medium text-slate-500">Profil şu an bomboş, hemen bir şeyler karala!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="followers" class="tab-content hidden p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 fade-in">
                    <?php if($followers): ?>
                        <?php foreach($followers as $f): ?>
                            <div class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-2xl hover:shadow-md hover:border-primary/20 transition-all duration-300 group">
                                <a href="uye.php?user=<?= e($f['username']) ?>" class="flex-shrink-0">
                                    <img src="<?= e(getProfilePhotoUrl($f['profile_photo'])) ?>" class="w-12 h-12 rounded-full object-cover border border-slate-200 group-hover:scale-105 transition-transform">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="uye.php?user=<?= e($f['username']) ?>" class="text-sm font-extrabold text-dark hover:text-primary truncate block transition-colors"><?= e($f['username']) ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12 text-slate-400 text-sm font-semibold">Henüz takipçisi yok.</div>
                    <?php endif; ?>
                </div>

                <div id="following" class="tab-content hidden p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 fade-in">
                    <?php if($following): ?>
                        <?php foreach($following as $f): ?>
                            <div class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-2xl hover:shadow-md hover:border-primary/20 transition-all duration-300 group">
                                <a href="uye.php?user=<?= e($f['username']) ?>" class="flex-shrink-0">
                                    <img src="<?= e(getProfilePhotoUrl($f['profile_photo'])) ?>" class="w-12 h-12 rounded-full object-cover border border-slate-200 group-hover:scale-105 transition-transform">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="uye.php?user=<?= e($f['username']) ?>" class="text-sm font-extrabold text-dark hover:text-primary truncate block transition-colors"><?= e($f['username']) ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12 text-slate-400 text-sm font-semibold">Henüz kimseyi takip etmiyor.</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // UYARI MESAJLARI OTOMATİK KAPANMA
    const alerts = document.querySelectorAll('.auto-close-alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 400);
            });
        }, 4000); 
    }

    // TAB KONTROLÜ
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('active', 'bg-dark', 'text-white', 'shadow-md');
                b.classList.add('text-slate-500');
            });
            btn.classList.add('active', 'bg-dark', 'text-white', 'shadow-md');
            btn.classList.remove('text-slate-500');

            tabContents.forEach(content => content.classList.add('hidden'));
            const targetId = btn.getAttribute('data-target');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });

    // PROFİL DÜZENLEME FORMU AÇ/KAPA
    const toggleEditBtn = document.getElementById('toggleEditBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editSection = document.getElementById('editProfileSection');

    if(toggleEditBtn && editSection) {
        toggleEditBtn.addEventListener('click', () => {
            editSection.classList.toggle('hidden');
            if(!editSection.classList.contains('hidden')) {
                editSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        cancelEditBtn.addEventListener('click', () => {
            editSection.classList.add('hidden');
        });
    }

    // KAPAK VE AVATAR CANLI ÖNİZLEME
    function setupImagePreview(inputId, previewId, formId = null) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (input && preview) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.src = event.target.result;
                        preview.classList.remove('hidden');
                        if(formId) {
                            preview.style.opacity = '0.5';
                            document.getElementById(formId).submit(); 
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }
    setupImagePreview('coverInput', 'cover-preview', 'coverForm');
    setupImagePreview('photoInput', 'avatar-preview');

    // TAKİP ET BUTONU (AJAX)
    const followBtn = document.getElementById('followBtn');
    if(followBtn) {
        followBtn.addEventListener('click', () => {
            followBtn.disabled = true;
            followBtn.style.opacity = '0.7';

            const userId = followBtn.getAttribute('data-user');
            
            fetch('actions/follow_toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    target_id: userId, 
                    csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                })
            })
            .then(res => res.json())
            .then(data => {
                followBtn.disabled = false;
                followBtn.style.opacity = '1';

                if(data.success) {
                    if (data.is_following) {
                        followBtn.innerHTML = '<i class="fa-solid fa-user-check"></i> Takip Ediliyor';
                        followBtn.className = 'w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2 bg-slate-100 text-dark hover:bg-slate-200';
                        followBtn.setAttribute('data-following', '1');
                    } else {
                        followBtn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Takip Et';
                        followBtn.className = 'w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2 bg-dark text-white hover:bg-slate-800 hover:shadow-md';
                        followBtn.setAttribute('data-following', '0');
                    }
                } else {
                    alert('Takip işlemi başarısız.');
                }
            })
            .catch(err => {
                console.error(err);
                followBtn.disabled = false;
                followBtn.style.opacity = '1';
                alert('Sunucu hatası.');
            });
        });
    }
});
</script>
</body>
</html>