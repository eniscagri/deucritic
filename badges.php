<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$new_unlocked = []; // Yeni kazanılan rozetlerin isimlerini tutmak için
$badge_progress = []; // Rozetlerin ilerleme durumları

// --- OTOMATİK ROZET KONTROL VE MESAJ SİSTEMİ ---
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // Tüm rozetleri isimleriyle eşleştirmek için önceden çekiyoruz
    $stmt = $pdo->query("SELECT id, name FROM badges");
    $badge_names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => name
    
    $stmt = $pdo->prepare("SELECT badge_id FROM user_badges WHERE user_id = ?");
    $stmt->execute([$uid]);
    $owned_badges = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // YENİ: Sistemden DM Atma Fonksiyonu
    function sendBadgeDM($pdo, $receiver_id, $badge_name) {
        try {
            $system_id = 1; // 1 Numaralı ID'nin Deucritic resmi / sistem hesabı olduğunu varsayıyoruz.
            $msg = "🎉 Harika bir haberim var! **" . $badge_name . "** rozetini başarıyla kazandın! Profilini süslemeye ve topluluğumuzda aktif olmaya devam et. 🚀";
            
            // Sistemle kullanıcı arasında önceden sohbet var mı kontrol et
            $stmt = $pdo->prepare("
                SELECT cu1.conversation_id 
                FROM conversation_users cu1 
                JOIN conversation_users cu2 ON cu1.conversation_id = cu2.conversation_id 
                WHERE cu1.user_id = ? AND cu2.user_id = ? LIMIT 1
            ");
            $stmt->execute([$system_id, $receiver_id]);
            $convo_id = $stmt->fetchColumn();
            
            if (!$convo_id) {
                // Sohbet yoksa yeni bir konuşma odası oluştur
                $pdo->exec("INSERT INTO conversations () VALUES ()");
                $convo_id = $pdo->lastInsertId();
                if ($convo_id) {
                    $stmt = $pdo->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (?, ?), (?, ?)");
                    $stmt->execute([$convo_id, $system_id, $convo_id, $receiver_id]);
                }
            }
            
            // Sohbet ID bulunduysa veya oluşturulduysa mesajı gönder
            if ($convo_id) {
                $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$convo_id, $system_id, $receiver_id, $msg]);
            }
        } catch (Exception $e) {
           
        }
    }

$awardBadge = function($badge_id) use ($pdo, $uid, &$owned_badges, &$new_unlocked, $badge_names) {
    if (!in_array($badge_id, $owned_badges)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id, unlocked_at) VALUES (?, ?, NOW())");
            $stmt->execute([$uid, $badge_id]);
            
            $b_name = $badge_names[$badge_id] ?? 'Yeni Rozet';
            
            sendBadgeDM($pdo, $uid, $b_name);
            
            $notif_msg = "Tebrikler! 🎉 '{$b_name}' rozetini kazandın.";
            $link = "badges.php";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
            $stmt->execute([$uid, $notif_msg, $link]);

            $new_unlocked[] = $b_name;
        } catch (PDOException $e) { }
    }
};

    $topic_count = $pdo->query("SELECT COUNT(*) FROM topics WHERE created_by = $uid")->fetchColumn();
    $following_count = $pdo->query("SELECT COUNT(*) FROM followers WHERE follower_id = $uid")->fetchColumn();
    $night_posts = $pdo->query("SELECT COUNT(*) FROM topics WHERE created_by = $uid AND HOUR(created_at) BETWEEN 0 AND 5")->fetchColumn();
    $isverified = $pdo->query("SELECT isverified FROM users WHERE id = $uid")->fetchColumn();
    $active_days = $pdo->query("SELECT COUNT(DISTINCT DATE(created_at)) FROM topics WHERE created_by = $uid")->fetchColumn();
    
  
    $total_likes = 0; $max_single_likes = 0;
    try {
        // Toplam beğeni
        $total_likes = $pdo->query("SELECT COUNT(*) FROM topic_likes WHERE topic_id IN (SELECT id FROM topics WHERE created_by = $uid)")->fetchColumn();
        
        $max_single_likes = $pdo->query("SELECT MAX(like_count) FROM (SELECT COUNT(*) as like_count FROM topic_likes WHERE topic_id IN (SELECT id FROM topics WHERE created_by = $uid) GROUP BY topic_id) as t")->fetchColumn() ?? 0;
    } catch (Exception $e) {}

    $awardBadge(23);
    $awardBadge(35); 

    if ($topic_count >= 1)  $awardBadge(24);
    if ($topic_count >= 5)  $awardBadge(28);
    if ($topic_count >= 10) $awardBadge(26);
    if ($topic_count >= 20) $awardBadge(31);

    if ($following_count >= 5)  $awardBadge(25);
    if ($following_count >= 10) $awardBadge(29);

    if ($night_posts > 0) $awardBadge(34);
    if ($isverified == 1) $awardBadge(33);
    if ($active_days >= 7) $awardBadge(30);

    if ($total_likes >= 10) $awardBadge(27);
    if ($max_single_likes >= 10) $awardBadge(32);

    /* --- İLERLEME (PROGRESS) VERİLERİNİ HAZIRLA --- */
    $badge_progress = [
        24 => ['current' => $topic_count, 'target' => 1, 'unit' => 'Paylaşım'],
        28 => ['current' => $topic_count, 'target' => 5, 'unit' => 'Paylaşım'],
        26 => ['current' => $topic_count, 'target' => 10, 'unit' => 'Paylaşım'],
        31 => ['current' => $topic_count, 'target' => 20, 'unit' => 'Paylaşım'],
        25 => ['current' => $following_count, 'target' => 5, 'unit' => 'Takip'],
        29 => ['current' => $following_count, 'target' => 10, 'unit' => 'Takip'],
        27 => ['current' => $total_likes, 'target' => 10, 'unit' => 'Beğeni'],
        32 => ['current' => $max_single_likes, 'target' => 10, 'unit' => 'Tek Post Beğenisi'],
        30 => ['current' => $active_days, 'target' => 7, 'unit' => 'Gün Aktiflik']
    ];
}


// SAYFA VERİLERİNİ ÇEKME
$stmt = $pdo->query("SELECT id, name, description, icon, created_at FROM badges ORDER BY created_at ASC");
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

$badge_users_stmt = $pdo->query("SELECT badge_id, COUNT(user_id) as user_count FROM user_badges GROUP BY badge_id");
$badge_user_counts = [];
while($row = $badge_users_stmt->fetch(PDO::FETCH_ASSOC)) {
    $badge_user_counts[$row['badge_id']] = $row['user_count'];
}

$user_badges = [];
if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT badge_id, unlocked_at FROM user_badges WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_badges = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rozetler | Deucritic</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" href="/gorsel/favicon.ico">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: { primary: '#b84834', secondary: '#e9c65a' }
            }
        }
    }
</script>

<style>
body {
    background-color: #f8fafc;
    min-height: 100vh;
}

header {
    background: linear-gradient(135deg, #b84834, #d8785d, #e9c65a);
    background-size: 200% 200%;
    animation: gradientShift 8s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Kilitli Rozet Efektleri */
.badge-locked img { filter: grayscale(100%) opacity(0.4); }
.badge-locked h2 { color: #94a3b8; }
.progress-bar-striped {
    background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
}
</style>

    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="text-slate-800 pb-20">

<header class="relative text-white shadow-xl overflow-hidden">
    <div class="absolute inset-0 bg-black/10 mix-blend-overlay"></div>
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between p-8 md:p-10 relative z-10">
        <a href="index.php" class="flex items-center gap-5">
            <div class="bg-white/20 p-3 rounded-3xl backdrop-blur-md shadow-inner border border-white/30">
                <img src="/gorsel/badge_deucritic.png" alt="Rozetler" class="w-14 h-14 drop-shadow-xl">
            </div>
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-md mb-1">Rozetlerim</h1>
                <p class="text-[15px] text-white/90 font-medium">Topluluğun bir parçası ol, başarımları topla ve seviyeni göster!</p>
            </div>
        </a>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary/10 text-primary flex items-center justify-center rounded-xl">
                <i class="fa-solid fa-medal text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800">Tüm Başarımlar</h2>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="badgeSearch" placeholder="Rozet ismine göre ara..." 
                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all font-medium text-sm" onkeyup="searchBadges()">
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
            <label class="flex items-center gap-2 text-slate-600 font-bold text-sm cursor-pointer bg-slate-50 hover:bg-slate-100 px-4 py-2.5 rounded-xl border border-slate-200 transition-colors shrink-0 select-none">
                <input type="checkbox" id="myBadgesOnly" onchange="searchBadges()" class="w-4 h-4 text-primary rounded border-slate-300 focus:ring-primary accent-primary">
                Kazanılanlar
            </label>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!$badges): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-8 rounded-3xl shadow-sm text-center">
            <i class="fa-solid fa-box-open text-4xl mb-4 opacity-50"></i>
            <h3 class="text-lg font-bold">Görünüşe Göre Buralar Boş</h3>
            <p class="font-medium mt-1">Sisteme henüz hiç rozet eklenmemiş.</p>
        </div>
    <?php else: ?>
        <div id="badgesGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
            <?php foreach($badges as $badge): 
                $b_id = $badge['id'];
                $count = $badge_user_counts[$b_id] ?? 0;
                $owned = isset($user_badges[$b_id]);
                $unlocked_at = $owned ? $user_badges[$b_id] : null;
                
                // İlerleme Verileri
                $has_progress = isset($badge_progress[$b_id]) && !$owned;
                $p_current = $has_progress ? $badge_progress[$b_id]['current'] : 0;
                $p_target = $has_progress ? $badge_progress[$b_id]['target'] : 1;
                $p_unit = $has_progress ? $badge_progress[$b_id]['unit'] : '';
                $percent = $has_progress ? min(100, ($p_current / $p_target) * 100) : 0;
            ?>
                <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 p-5 flex flex-col items-center text-center relative group hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer overflow-hidden <?= $owned ? 'badge-unlocked' : 'badge-locked' ?>" 
                     data-name="<?= htmlspecialchars(mb_strtolower($badge['name'], 'UTF-8')) ?>" 
                     data-owned="<?= $owned ? '1' : '0' ?>"
                     data-description="<?= htmlspecialchars($badge['description']) ?>"
                     data-count="<?= $count ?>"
                     data-unlocked="<?= $unlocked_at ?>"
                     onclick="openBadgeModal(this)">
                    
                    <?php if($badge['icon']): ?>
                        <div class="relative w-16 h-16 sm:w-20 sm:h-20 mb-4 transition-transform duration-500 group-hover:scale-110">
                            <?php if($owned): ?>
                                <div class="absolute inset-0 bg-primary/20 blur-xl rounded-full"></div>
                            <?php endif; ?>
                            
                            <img src="<?= htmlspecialchars($badge['icon']) ?>" alt="<?= htmlspecialchars($badge['name']) ?>" class="w-full h-full object-contain relative z-10 drop-shadow-md">
                            
                            <?php if($owned): ?>
                                <div class="absolute -bottom-1 -right-1 bg-gradient-to-tr from-emerald-500 to-green-400 text-white w-7 h-7 rounded-full border-[3px] border-white flex items-center justify-center shadow-lg z-20">
                                    <i class="fa-solid fa-check text-[11px]"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="font-extrabold text-[15px] sm:text-[16px] leading-tight mb-1 text-slate-800"><?= htmlspecialchars($badge['name']) ?></h2>
                    
                    <?php if($has_progress): ?>
                        <div class="w-full mt-3">
                            <div class="flex justify-between text-[10px] font-bold text-slate-400 mb-1.5 px-1">
                                <span><?= $p_current ?></span>
                                <span><?= $p_target ?> <?= $p_unit ?></span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                                <div class="h-full bg-slate-400 transition-all duration-1000 ease-out" style="width: <?= $percent ?>%"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-[11px] font-bold text-slate-400 mt-2 uppercase tracking-wider bg-slate-50 px-2.5 py-1 rounded-lg">
                            <i class="fa-solid fa-users mr-0.5"></i> <?= $count ?> Kişide
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<div id="badgeModal" class="hidden fixed inset-0 items-center justify-center z-[100] bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full mx-4 relative shadow-2xl transform scale-95 transition-transform" id="badgeModalInner">
        <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 rounded-full transition-colors z-20">
            <i class="fa-solid fa-xmark"></i>
        </button>
        
        <div class="w-28 h-28 mx-auto mb-6 relative">
            <div id="modalGlow" class="absolute inset-0 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full blur-2xl transition-all"></div>
            <img id="modalIcon" class="w-full h-full relative z-10 drop-shadow-2xl object-contain transition-all" src="">
        </div>
        
        <div class="text-center">
            <h2 id="modalName" class="text-2xl font-black text-slate-800 mb-2 tracking-tight"></h2>
            <p id="modalDescription" class="text-slate-500 font-medium text-[15px] mb-6 leading-relaxed"></p>
            
            <div class="inline-flex flex-col items-center justify-center bg-slate-50 px-5 py-3.5 rounded-2xl w-full border border-slate-100 shadow-inner">
                <p id="modalUnlocked" class="text-sm text-emerald-600 font-extrabold hidden flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-check text-lg"></i> <span id="modalDateText"></span>
                </p>
                <p id="modalLocked" class="text-sm text-slate-400 font-bold hidden flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock text-lg opacity-50"></i> Henüz kazanılmadı
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($new_unlocked)): ?>
<div id="badgeToast" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white px-6 py-4 rounded-[2rem] shadow-2xl z-[100] flex items-center gap-4 translate-y-32 opacity-0 transition-all duration-500 min-w-[300px] border border-white/10">
    <div class="bg-gradient-to-tr from-yellow-400 to-orange-500 p-2.5 rounded-full shadow-lg shadow-orange-500/30">
        <i class="fa-solid fa-trophy text-xl text-white"></i>
    </div>
    <div>
        <h4 class="font-black text-[15px] text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-amber-400">Yeni Rozet!</h4>
        <p class="font-medium text-sm text-slate-300">
            <?= count($new_unlocked) > 1 ? count($new_unlocked) . " adet yeni rozet kazandın!" : htmlspecialchars($new_unlocked[0]) . " rozetini kazandın!" ?>
        </p>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php';?>

<script>
// Arama ve Filtreleme
function searchBadges() {
    let input = document.getElementById('badgeSearch').value.toLowerCase();
    let myOnly = document.getElementById('myBadgesOnly')?.checked;
    let badges = document.querySelectorAll('#badgesGrid > div');

    badges.forEach(badge => {
        let name = badge.dataset.name;
        let owned = badge.dataset.owned === '1';
        let show = name.includes(input) && (!myOnly || owned);
        badge.style.display = show ? 'flex' : 'none';
    });
}

// Modal İşlemleri
const modal = document.getElementById('badgeModal');
const modalInner = document.getElementById('badgeModalInner');

function openBadgeModal(element) {
    const owned = element.dataset.owned === '1';
    const icon = document.getElementById('modalIcon');
    
    icon.src = element.querySelector('img')?.src || '';
    icon.style.filter = owned ? 'none' : 'grayscale(100%) opacity(0.5)';
    
    document.getElementById('modalGlow').style.opacity = owned ? '1' : '0';
    document.getElementById('modalName').innerText = element.querySelector('h2').innerText;
    document.getElementById('modalDescription').innerText = element.dataset.description || '';
   
    const unlocked = element.dataset.unlocked;
    const txtUnlocked = document.getElementById('modalUnlocked');
    const txtLocked = document.getElementById('modalLocked');

    if(unlocked) {
        const date = new Date(unlocked.replace(/-/g, "/") + " UTC");
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        document.getElementById('modalDateText').innerText = date.toLocaleDateString('tr-TR', options);
        
        txtUnlocked.classList.remove('hidden');
        txtUnlocked.classList.add('flex');
        txtLocked.classList.add('hidden');
        txtLocked.classList.remove('flex');
    } else {
        txtLocked.classList.remove('hidden');
        txtLocked.classList.add('flex');
        txtUnlocked.classList.add('hidden');
        txtUnlocked.classList.remove('flex');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Animasyon Gecikmesi
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalInner.classList.remove('scale-95');
        modalInner.classList.add('scale-100');
    }, 10);
}

function closeModal() {
    modal.classList.add('opacity-0');
    modalInner.classList.remove('scale-100');
    modalInner.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

// YENİ ROZET KAZANILDIĞINDA (KUTLAMA / CONFETTİ EFEKTİ VE TOAST)
<?php if (!empty($new_unlocked)): ?>
document.addEventListener('DOMContentLoaded', () => {
    // Confetti Patlaması
    var duration = 3 * 1000;
    var end = Date.now() + duration;

    (function frame() {
        confetti({
            particleCount: 5,
            angle: 60,
            spread: 55,
            origin: { x: 0 },
            colors: ['#b84834', '#e9c65a', '#ffbe0b', '#fb5607']
        });
        confetti({
            particleCount: 5,
            angle: 120,
            spread: 55,
            origin: { x: 1 },
            colors: ['#b84834', '#e9c65a', '#ffbe0b', '#fb5607']
        });

        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    }());

    // Toast Bildirimini Çıkar
    const toast = document.getElementById('badgeToast');
    setTimeout(() => {
        toast.classList.remove('translate-y-32', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    }, 300); 

    // 6 Saniye sonra Toast'ı gizle
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-32', 'opacity-0');
    }, 6000);
});
<?php endif; ?>
</script>

</body>
</html>