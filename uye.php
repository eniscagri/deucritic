<?php
require_once __DIR__ . '/includes/i18n.php';

require_once 'includes/config.php';
require_once 'includes/functions.php';

$username = $_GET['user'] ?? '';
$user = $_GET['user'] ?? '';

if (!$username) {
    http_response_code(400);
    die('<div class="text-rose-600 text-center mt-10 font-black text-xl">Kullanıcı adı belirtilmedi.</div>');
}

$stmt = $pdo->prepare("
    SELECT id, username, school, grade_level, profile_photo, cover_photo, bio, created_at, isverified 
    FROM users 
    WHERE username = :username
");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    die('<div class="text-rose-600 text-center mt-10 font-black text-xl">Böyle bir kullanıcı bulunamadı.</div>');
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM entries WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user['id']]);
$totalEntries = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE followed_id = :user_id");
$stmt->execute([':user_id' => $user['id']]);
$totalFollowers = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = :user_id");
$stmt->execute([':user_id' => $user['id']]);
$totalFollowing = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT b.name, b.icon, b.description
    FROM user_badges ub
    JOIN badges b ON ub.badge_id = b.id
    WHERE ub.user_id = :user_id
");
$stmt->execute([':user_id' => $user['id']]);
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isOwnProfile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'];

$followed = false;
if (!$isOwnProfile && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM followers 
        WHERE follower_id = :follower AND followed_id = :following
    ");
    $stmt->execute([
        ':follower' => $_SESSION['user_id'], 
        ':following' => $user['id']
    ]);
    $followed = $stmt->fetchColumn() ? true : false;
}

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

// Kapak fotoğrafı için yardımcı fonksiyon
function getCoverPhotoUrl($photo_path) {
    return (!empty($photo_path) && file_exists(__DIR__ . '/' . $photo_path)) ? $photo_path : '';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<title>@<?= htmlspecialchars($user['username']) ?> Profili | Deucritic</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="icon" href="/gorsel/favicon.ico?v=20261104">
<meta name="description" content="Deucritic, kullanıcıların fikirlerini özgürce paylaşabildiği, yorum yapabildiği ve çeşitli konularda eleştiri üretebildiği insan odaklı bir platformdur.">

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
    .tab-btn.active { background-color: #1d352f; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .tab-btn { color: #64748b; }
    .fade-in { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="text-slate-800 antialiased selection:bg-primary/20 selection:text-primary">

<?php include 'header_u.php'; ?>

<main class="container mx-auto px-3 sm:px-4 py-6 max-w-5xl mb-12 fade-in">
    
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden mb-6">
        
        <div class="relative w-full h-40 sm:h-56 md:h-72 bg-gradient-to-r from-primary to-cyan-300 group">
            <?php $cover_url = getCoverPhotoUrl($user['cover_photo'] ?? ''); ?>
            <img src="<?= htmlspecialchars($cover_url) ?>" class="<?= empty($cover_url) ? 'hidden' : '' ?> w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
        </div>

        <div class="relative px-6 sm:px-10 pb-8 flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6 -mt-12 sm:-mt-16">
            
            <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-xl bg-white flex-shrink-0 z-10 group inline-block">
                <img src="<?= htmlspecialchars($user['profile_photo'] ?: 'uploads/default-avatar.png') ?>" class="w-full h-full rounded-full object-cover transition-transform duration-300 group-hover:scale-105">
                
                <?php if ($isOwnProfile): ?>
                    <button onclick="document.getElementById('photoModal').classList.remove('hidden')"
                            class="absolute bottom-0 right-0 bg-dark text-white w-8 h-8 flex items-center justify-center rounded-full shadow-lg hover:bg-primary transition-colors border-2 border-white" title="Fotoğrafı değiştir">
                        <i class="fa-solid fa-camera text-xs"></i>
                    </button>
                <?php endif; ?>
            </div>

            <div class="flex-1 pt-2 sm:pt-16 sm:pb-2">
                <h1 class="text-2xl sm:text-3xl font-black text-dark tracking-tight flex items-center gap-2 flex-wrap">
                    <span data-dc-user-content><?= htmlspecialchars($user['username']) ?></span>
                    <?php if (!empty($user['isverified']) && $user['isverified'] == 1): ?>
                        <img src="https://img.icons8.com/color/48/verified-badge.png" class="w-6 h-6 inline-block" title="Doğrulanmış Kullanıcı">
                    <?php endif; ?>
                </h1>
                
                <p class="text-slate-500 text-sm mt-1 font-medium leading-relaxed max-w-2xl italic">
                    <span data-dc-user-content>"<?= nl2br(htmlspecialchars($user['bio'] ?: 'Henüz bio eklenmemiş.')) ?>"</span>
                </p>
                
                <?php
                $aylar = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
                $kayit_tarihi = strtotime($user['created_at']);
                $turkce_tarih = $aylar[(int)date('n', $kayit_tarihi)] . ' ' . date('Y', $kayit_tarihi);
                ?>
                
                <div class="flex flex-wrap items-center gap-4 mt-3 text-xs font-semibold text-slate-400">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-graduation-cap text-slate-300 text-sm"></i> <?= htmlspecialchars($user['school'] ?: 'Belirtilmedi') ?></span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-layer-group text-slate-300 text-sm"></i> <?= htmlspecialchars($user['grade_level'] ?: '-') ?>. Sınıf</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-days text-slate-300 text-sm"></i> <?= $turkce_tarih ?></span>
                </div>
            </div>

            <div class="mt-4 sm:mt-0 sm:pb-2 sm:ml-auto w-full sm:w-auto flex-shrink-0">
                <?php if($isOwnProfile): ?>
                    <a href="profile.php" class="w-full sm:w-auto px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-dark font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-pen-to-square"></i> Profili Düzenle
                    </a>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button id="followBtn" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2 <?= $followed ? 'bg-slate-100 text-dark hover:bg-slate-200' : 'bg-dark text-white hover:bg-slate-800 hover:shadow-md' ?>">
                            <?= $followed ? '<i class="fa-solid fa-user-check"></i> Takip Ediliyor' : '<i class="fa-solid fa-user-plus"></i> Takip Et' ?>
                        </button>
                        <a href="dm.php?user=<?= $user['id'] ?>" class="flex-1 sm:flex-none px-6 py-2.5 bg-primary text-white rounded-xl font-bold hover:bg-cyan-600 transition-all shadow-sm flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Mesaj
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-4 space-y-6">
            <?php if ($badges): ?>
            <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-100">
                <h3 class="font-extrabold text-dark mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-award text-secondary text-xl"></i> Rozetler
                </h3>
                <div class="flex flex-wrap gap-3">
                    <?php foreach($badges as $badge): ?>
                        <div class="group relative flex flex-col items-center">
                            <?php if($badge['icon']): ?>
                                <img src="<?= htmlspecialchars($badge['icon']) ?>" class="w-14 h-14 drop-shadow-sm hover:scale-110 transition-transform duration-300 cursor-help" title="<?= htmlspecialchars($badge['name']) . ' - ' . htmlspecialchars($badge['description']) ?>">
                            <?php else: ?>
                                <div class="w-14 h-14 bg-gradient-to-br from-primary/20 to-primary/40 border border-primary/20 rounded-full flex items-center justify-center font-black text-primary text-xl cursor-help hover:scale-110 transition-transform duration-300" title="<?= htmlspecialchars($badge['name']) ?>">
                                    <?= htmlspecialchars(mb_substr($badge['name'],0,1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 overflow-hidden">
                
                <div class="p-2 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex bg-slate-100/50 p-1 rounded-xl">
                        <button class="tab-btn active flex-1 py-2.5 text-sm font-bold rounded-lg transition-all" data-target="posts">
                            Onepler <span class="bg-white shadow-sm text-slate-600 text-[10px] px-2 py-0.5 rounded-full ml-1"><?= $totalEntries ?></span>
                        </button>
                        <button class="tab-btn flex-1 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-1" data-target="followers" onclick="loadFollowers()">
                            Takipçiler <span class="bg-white shadow-sm text-slate-600 text-[10px] px-2 py-0.5 rounded-full ml-1"><?= $totalFollowers ?></span>
                        </button>
                        <button class="tab-btn flex-1 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-1" data-target="following" onclick="loadFollowing()">
                            Takip Edilen <span class="bg-white shadow-sm text-slate-600 text-[10px] px-2 py-0.5 rounded-full ml-1"><?= $totalFollowing ?></span>
                        </button>
                    </div>
                </div>

                <div id="posts" class="tab-content divide-y divide-slate-100 fade-in">
                    <?php if(empty($entries)): ?>
                        <div class="py-20 px-6 text-center">
                            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-5 border-2 border-dashed border-slate-200">
                                <i class="fa-solid fa-pen-nib text-4xl text-slate-300"></i>
                            </div>
                            <p class="font-bold text-slate-500">Henüz hiçbir şey paylaşılmamış.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($entries as $entry): ?>
                            <article class="p-6 hover:bg-slate-50/50 transition duration-300">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-full flex-shrink-0 border border-slate-200 overflow-hidden">
                                        <img src="<?= htmlspecialchars($user['profile_photo'] ?: 'uploads/default-avatar.png') ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-sm text-dark leading-tight">
                                            @<?= htmlspecialchars($user['username']) ?>
                                        </h4>
                                        <span class="text-[11px] font-semibold text-slate-400">
                                            <i class="fa-regular fa-clock mr-0.5"></i> <?= timeAgo($entry['created_at']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 pl-14 md:pl-0 text-dark">
                                    <h5 class="font-extrabold text-base mb-1.5">
                                        <a data-dc-user-content href="topic.php?id=<?= $entry['topic_id'] ?>" class="hover:text-primary transition-colors"><?= htmlspecialchars($entry['title']) ?></a>
                                    </h5>
                                    <p data-dc-user-content class="text-sm font-medium leading-relaxed text-slate-600 break-words">
                                        <?= nl2br(htmlspecialchars(mb_strimwidth($entry['content'],0,350,'...'))) ?>
                                    </p>
                                    <a href="topic.php?id=<?= $entry['topic_id'] ?>" class="text-primary text-sm font-bold hover:underline mt-2 inline-block">Devamını Oku &rarr;</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if($totalPages > 1): ?>
                        <div class="p-5 border-t border-slate-100 flex justify-center gap-2 flex-wrap">
                            <?php for($i=1; $i<=$totalPages; $i++): ?>
                                <a href="?user=<?= urlencode($username) ?>&page=<?= $i ?>" class="w-8 h-8 flex items-center justify-center rounded-lg font-bold text-sm transition <?= $i == $page ? 'bg-dark text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-dark' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="followers" class="tab-content hidden p-6 fade-in">
                    <ul id="followersList" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></ul>
                </div>

                <div id="following" class="tab-content hidden p-6 fade-in">
                    <ul id="followingList" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if ($isOwnProfile): ?>
<div id="photoModal" class="fixed inset-0 hidden items-center justify-center bg-dark/60 backdrop-blur-sm z-50 transition-opacity">
  <div class="bg-white p-8 rounded-[2rem] shadow-2xl max-w-sm w-full text-center relative mx-4">
    <button onclick="document.getElementById('photoModal').classList.add('hidden')" class="absolute top-4 right-5 text-slate-400 hover:text-dark text-2xl font-bold transition">&times;</button>
    <h3 class="text-xl font-black text-dark mb-6">Avatarı Güncelle</h3>
    <div class="w-32 h-32 mx-auto rounded-full border-4 border-slate-50 shadow-md mb-6 overflow-hidden">
        <img id="photoPreview" src="<?= htmlspecialchars($user['profile_photo'] ?? 'uploads/default-avatar.png') ?>" class="w-full h-full object-cover">
    </div>
    <form id="photoForm" action="update_photo.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <label class="block w-full border-2 border-dashed border-slate-200 text-slate-500 font-bold hover:text-primary hover:border-primary hover:bg-primary/5 rounded-xl cursor-pointer p-3 mb-6 transition-colors">
          <i class="fa-solid fa-image mr-2"></i> Yeni Fotoğraf Seç
          <input type="file" name="profile_photo" accept="image/*" class="hidden" onchange="previewPhoto(event)">
      </label>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('photoModal').classList.add('hidden')" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition">İptal</button>
        <button type="submit" id="uploadBtn" class="w-2/3 bg-dark hover:bg-slate-800 text-white font-bold py-3 rounded-xl transition shadow-lg">Yükle 🚀</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
// Sekme Sistemi (Yeni Pilli Görünüm İçin)
const tabBtns = document.querySelectorAll('.tab-btn');
const tabContents = document.querySelectorAll('.tab-content');

tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        tabBtns.forEach(b => {
            b.classList.remove('active', 'bg-dark', 'text-white');
            b.classList.add('text-slate-500');
        });
        btn.classList.add('active', 'bg-dark', 'text-white');
        btn.classList.remove('text-slate-500');

        tabContents.forEach(content => content.classList.add('hidden'));
        document.getElementById(btn.getAttribute('data-target')).classList.remove('hidden');
    });
});

// Modern Takipçi / Takip Edilen Ajax Yüklemesi
let followersLoaded = false;
function loadFollowers() {
    if (followersLoaded) return;
    const list = document.getElementById('followersList');
    list.innerHTML = '<li class="col-span-full text-center text-slate-400 py-12"><i class="fa-solid fa-circle-notch fa-spin text-2xl text-primary"></i></li>';
    
    fetch('get_followers.php?user_id=<?= $user["id"] ?>')
        .then(res => res.json())
        .then(data => {
            list.innerHTML = '';
            if (data.error) { list.innerHTML = `<li class="col-span-full text-rose-500 text-center text-sm font-bold py-8">${data.error}</li>`; return; }
            if (data.length === 0) { list.innerHTML = `<li class="col-span-full text-slate-400 text-center text-sm font-semibold py-12">Henüz takipçisi yok.</li>`; return; }
            
            data.forEach(u => {
                list.innerHTML += `
                    <li class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-2xl hover:shadow-md hover:border-primary/20 transition-all duration-300 group">
                        <a href="uye.php?user=${encodeURIComponent(u.username)}" class="flex-shrink-0">
                            <img src="${u.profile_photo || 'uploads/default-avatar.png'}" class="w-12 h-12 rounded-full object-cover border border-slate-200 group-hover:scale-105 transition-transform">
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="uye.php?user=${encodeURIComponent(u.username)}" class="text-sm font-extrabold text-dark hover:text-primary truncate block transition-colors">${u.username}</a>
                        </div>
                    </li>`;
            });
            followersLoaded = true;
        }).catch(err => list.innerHTML = '<li class="col-span-full text-rose-500 text-sm font-bold text-center py-8">Veriler yüklenemedi.</li>');
}

let followingLoaded = false;
function loadFollowing() {
    if (followingLoaded) return;
    const list = document.getElementById('followingList');
    list.innerHTML = '<li class="col-span-full text-center text-slate-400 py-12"><i class="fa-solid fa-circle-notch fa-spin text-2xl text-primary"></i></li>';
    
    fetch('get_following.php?user_id=<?= $user["id"] ?>')
        .then(res => res.json())
        .then(data => {
            list.innerHTML = '';
            if (data.error) { list.innerHTML = `<li class="col-span-full text-rose-500 text-sm font-bold text-center py-8">${data.error}</li>`; return; }
            if (data.length === 0) { list.innerHTML = `<li class="col-span-full text-slate-400 text-center text-sm font-semibold py-12">Henüz kimseyi takip etmiyor.</li>`; return; }
            
            data.forEach(u => {
                list.innerHTML += `
                    <li class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-2xl hover:shadow-md hover:border-primary/20 transition-all duration-300 group">
                        <a href="uye.php?user=${encodeURIComponent(u.username)}" class="flex-shrink-0">
                            <img src="${u.profile_photo || 'uploads/default-avatar.png'}" class="w-12 h-12 rounded-full object-cover border border-slate-200 group-hover:scale-105 transition-transform">
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="uye.php?user=${encodeURIComponent(u.username)}" class="text-sm font-extrabold text-dark hover:text-primary truncate block transition-colors">${u.username}</a>
                        </div>
                    </li>`;
            });
            followingLoaded = true;
        }).catch(err => list.innerHTML = '<li class="col-span-full text-rose-500 text-sm font-bold text-center py-8">Veriler yüklenemedi.</li>');
}

const followBtn = document.getElementById('followBtn');
if (followBtn) {
    followBtn.addEventListener('click', () => {
        followBtn.disabled = true;
        followBtn.style.opacity = '0.7';
        fetch('follow_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: <?= $user['id'] ?> })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'followed' || data.status === 'unfollowed') location.reload(); 
            else { followBtn.disabled = false; followBtn.style.opacity = '1'; }
        }).catch(err => { followBtn.disabled = false; followBtn.style.opacity = '1'; });
    });
}

// PP Önizleme İşlemi (Modal)
function previewPhoto(event) {
    const preview = document.getElementById('photoPreview');
    const file = event.target.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){ preview.src = e.target.result; }
        reader.readAsDataURL(file);
    }
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>
