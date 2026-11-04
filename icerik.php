<?php
$perPage = 18;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $perPage;

$totalTopics = $pdo->query("SELECT COUNT(*) FROM topics")->fetchColumn();
$totalPages = ceil($totalTopics / $perPage);

$current_user_id = $_SESSION['user_id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT t.id, t.title, t.description, t.anonymous, t.created_at, u.username,
           COUNT(l.id) as like_count,
           MAX(CASE WHEN l.user_id = :uid THEN 1 ELSE 0 END) as is_liked
    FROM topics t
    JOIN users u ON t.created_by = u.id
    LEFT JOIN topic_likes l ON t.id = l.topic_id
    GROUP BY t.id
    ORDER BY t.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':uid', $current_user_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $start, PDO::PARAM_INT);
$stmt->execute();
$paginatedTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);

function timeAgoTr($datetime) {
    $now = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
    $past = new DateTime($datetime, new DateTimeZone('Europe/Istanbul'));
    $diff = $now->diff($past);
    if ($diff->y > 0) return $diff->y . ' yıl';
    if ($diff->m > 0) return $diff->m . ' ay';
    if ($diff->d > 0) return $diff->d . ' g';
    if ($diff->h > 0) return $diff->h . ' sa';
    if ($diff->i > 0) return $diff->i . ' dk';
    return 'şimdi';
}
?>

<section id="gundem" class="relative w-full max-w-6xl mx-auto mb-12">
    <div class="flex items-center justify-between mb-8 px-2 relative z-40">
        <div class="relative inline-block" id="gundemDropdownWrapper">
            <h2 id="gundemToggleBtn" class="flex items-center gap-3 text-3xl font-extrabold text-slate-800 tracking-tight cursor-pointer select-none group">
                <div class="relative">
                    <div class="absolute inset-0 bg-orange-400 rounded-xl blur-md opacity-40 animate-pulse"></div>
                    <div class="relative p-2.5 bg-gradient-to-tr from-yellow-500 via-orange-500 to-blue-500 rounded-xl shadow-md border border-white/30 flex items-center justify-center">
                        <i class="fa-solid fa-compass text-white text-xl"></i>
                    </div>
                </div>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-500 transition-colors group-hover:from-orange-500 group-hover:to-blue-500">
                    Son Onep'ler
                </span>
                <div class="ml-1 p-1.5 rounded-full bg-white/60 shadow-sm border border-slate-200 transition-all group-hover:bg-white group-active:scale-95">
                    <svg id="gundemChevron" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-blue-500 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </h2>

            <a href="kesfet.php" id="discoverMenu" class="absolute top-full left-12 mt-4 px-5 py-3 bg-white/90 backdrop-blur-xl text-slate-700 font-bold rounded-2xl shadow-[0_20px_40px_-15px_rgba(249,115,22,0.2)] border border-white/50 opacity-0 -translate-y-3 pointer-events-none transition-all duration-300 hover:bg-orange-50 hover:text-orange-600 flex items-center gap-3">
                <div class="p-1.5 bg-gradient-to-tr from-yellow-100 to-orange-100 rounded-lg text-orange-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                Keşfet
            </a>
        </div>
        </div>
     <?php include 'streaks.php'; ?>
<section id="badgeAnnouncement" class="relative max-w-5xl mx-auto px-4 py-12 transition-all duration-500 origin-center">
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-cyan-100/40 blur-3xl rounded-full -z-10"></div>

    <div class="bg-white/70 backdrop-blur-xl border border-white/80 rounded-[40px] p-8 md:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col md:flex-row items-center justify-between gap-8 transition-all hover:shadow-xl relative overflow-hidden">
        
        <button id="closeBadgePromo" class="absolute top-6 right-6 w-9 h-9 flex items-center justify-center bg-slate-100 text-slate-400 hover:bg-slate-200 hover:text-slate-700 rounded-full transition-colors z-20">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-amber-300/20 blur-3xl rounded-full pointer-events-none"></div>

        <div class="flex-1 space-y-5 text-center md:text-left relative z-10 mt-4 md:mt-0">
            
            <div class="inline-flex items-center gap-2 bg-cyan-50 px-4 py-1.5 rounded-full border border-cyan-200">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan-500"></span>
                </span>
                <span class="text-[11px] font-extrabold text-cyan-600 uppercase tracking-widest">YENİ ÖZELLİK</span>
            </div>

            <h2 class="text-3xl md:text-4xl font-black text-slate-800 leading-tight tracking-tight">
                Deucritic'te <br> 
                <span class="text-amber-500 underline decoration-amber-200 decoration-4 underline-offset-4">Rozet</span> Dönemi Başladı!
            </h2>
            
            <p class="text-slate-500 text-[15px] max-w-md leading-relaxed font-medium mx-auto md:mx-0">
                Kampüste ne kadar aktifsin? Gönderi paylaş, etkileşim kazan ve birbirinden özel rozetleri toplayarak profilini güçlendir. İlk rozetin seni bekliyor olabilir!
            </p>

            <div class="pt-2">
                <a href="badges.php" class="inline-flex items-center justify-center gap-3 bg-gradient-to-r from-cyan-500 to-blue-500 text-white px-8 py-3.5 rounded-2xl font-bold text-[15px] transition-all hover:shadow-lg hover:shadow-cyan-500/30 hover:-translate-y-1 active:translate-y-0 w-full sm:w-auto">
                    <i class="fa-solid fa-medal text-lg"></i>
                    Rozetlerini Keşfet
                </a>
            </div>
        </div>

        <div class="relative w-full md:w-1/3 flex justify-center relative z-10 mt-8 md:mt-0">
            <div class="relative w-48 h-48 flex items-center justify-center">
                <div class="absolute inset-0 border-[3px] border-dashed border-amber-300 rounded-full animate-[spin_15s_linear_infinite] opacity-70"></div>
                <div class="absolute inset-3 border border-amber-100 rounded-full"></div>
                <div class="absolute inset-8 border-2 border-dashed border-cyan-200 rounded-full animate-[spin_10s_linear_infinite_reverse] opacity-60"></div>
                
                <div class="w-28 h-28 bg-gradient-to-tr from-amber-400 to-yellow-300 rounded-[2rem] shadow-2xl flex items-center justify-center text-white text-5xl shadow-amber-300/50 rotate-12 transition-transform duration-500 hover:rotate-0 hover:scale-110 cursor-pointer">
                    <i class="fa-solid fa-trophy drop-shadow-md"></i>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('badgeAnnouncement');
    const closeBtn = document.getElementById('closeBadgePromo');
    
    // Eğer kullanıcı daha önce "Kapat" butonuna bastıysa, HTML render edilse bile anında gizlenir
    if(localStorage.getItem('badge_promo_closed') === 'true') {
        banner.style.display = 'none';
    }

    // Kapat butonuna basıldığında yumuşak bir animasyonla gizle ve localStorage'a kaydet
    closeBtn.addEventListener('click', () => {
        banner.style.opacity = '0';
        banner.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            banner.style.display = 'none';
            // Tarayıcıya bu duyurunun kapatıldığını söylüyoruz (1 kerelik gösterme mantığı)
            localStorage.setItem('badge_promo_closed', 'true');
        }, 300); // CSS transition süresi kadar bekler
    });
});
</script>

    <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($paginatedTopics as $topic): ?>
            <?php 
                $isBlur = !isset($_SESSION['user_id']) && (crc32($topic['id']) % 2 === 0); 
                $targetLink = $isBlur ? "egg.php" : "topic.php?id=" . $topic['id'];
                
                $likeIconClass = $topic['is_liked'] ? 'text-pink-500 fill-current' : 'text-slate-400 group-hover/btn:text-pink-500';
                $likeTextClass = $topic['is_liked'] ? 'text-pink-500' : 'text-slate-400 group-hover/btn:text-pink-500';
            ?>
            
            <li class="group relative flex flex-col justify-between bg-white/60 backdrop-blur-xl border border-white/80 rounded-[2rem] p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden cursor-pointer" onclick="window.location.href='<?= $targetLink ?>'">
                
                <?php if ($isBlur): ?>
                    <div class="absolute inset-0 z-20 backdrop-blur-md bg-slate-50/40 flex flex-col items-center justify-center p-6 text-center transition-all group-hover:bg-slate-50/20">
                        <div class="w-14 h-14 bg-white rounded-full shadow-md flex items-center justify-center mb-3 transform group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <span class="text-xs font-extrabold tracking-widest uppercase text-slate-800 mb-1">Özel İçerik</span>
                        <span class="text-sm font-medium text-slate-500">Devamını okumak için katıl</span>
                    </div>
                <?php endif; ?>

                <div class="<?= $isBlur ? 'opacity-40 blur-[2px] pointer-events-none' : '' ?> transition-all duration-300 flex flex-col h-full">
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-yellow-400 to-blue-500 p-[2px]">
                                <div class="w-full h-full bg-white rounded-full flex items-center justify-center">
                                    <span class="text-[10px] font-black text-slate-700">
                                        <?= $topic['anonymous'] ? '?' : strtoupper(substr($topic['username'], 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <?php if ($topic['anonymous']): ?>
                                    <span class="text-xs font-bold text-slate-700 italic">AnY</span>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-slate-800">@<?= htmlspecialchars($topic['username']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                            <?= timeAgoTr($topic['created_at']) ?>
                        </span>
                    </div>

                   <div class="mb-5 flex-grow">
                <h3 class="text-xl font-extrabold text-slate-900 leading-snug tracking-tight mb-2.5 transition-all duration-300 group-hover:bg-clip-text group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-orange-500 group-hover:to-yellow-500 line-clamp-2">
                    <?= htmlspecialchars($topic['title']) ?>
                </h3>
                
                <?php if (!empty($topic['description'])): ?>
                    <p class="text-[15px] text-slate-500 font-medium leading-relaxed line-clamp-2 opacity-90 group-hover:opacity-100 transition-opacity duration-300">
                        <?= htmlspecialchars($topic['description']) ?>
                    </p>
                <?php endif; ?>
            </div>

                    <div class="pt-4 border-t border-slate-100/50 flex items-center justify-between">
                        <div class="flex gap-4">
                            <button class="like-btn group/btn flex items-center gap-1.5 transition-all active:scale-90" data-id="<?= $topic['id'] ?>">
                                <svg class="like-icon w-5 h-5 transition-colors duration-300 <?= $likeIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="like-count text-xs font-bold transition-colors duration-300 <?= $likeTextClass ?>">
                                    <?= $topic['like_count'] > 0 ? $topic['like_count'] : 'Beğen' ?>
                                </span>
                            </button>
                            
                            <button class="flex items-center gap-1.5 hover:text-blue-500 text-slate-400 transition-colors" onclick="window.location.href='<?= $targetLink ?>#comments'; event.stopPropagation();">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span class="text-xs font-bold">Yanıtla</span>
                            </button>
                        </div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($totalPages > 1): ?>
    <div class="mt-12 flex justify-center items-center gap-2">
        <a href="?page=<?= max(1, $page-1) ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/60 backdrop-blur-sm border border-slate-200 text-slate-500 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>

        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold transition-all shadow-sm <?= $i === $page ? 'bg-orange-500 text-white shadow-orange-200 shadow-lg scale-110' : 'bg-white/60 backdrop-blur-sm border border-slate-200 text-slate-600 hover:bg-orange-50' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <a href="?page=<?= min($totalPages, $page+1) ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/60 backdrop-blur-sm border border-slate-200 text-slate-500 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    <?php endif; ?>

</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Dropdown Mantığı
        const toggleBtn = document.getElementById('gundemToggleBtn');
        const discoverMenu = document.getElementById('discoverMenu');
        const chevron = document.getElementById('gundemChevron');
        const wrapper = document.getElementById('gundemDropdownWrapper');

        if(toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation(); 
                const isOpen = !discoverMenu.classList.contains('opacity-0');
                if (isOpen) {
                    discoverMenu.classList.add('opacity-0', '-translate-y-3', 'pointer-events-none');
                    chevron.classList.remove('rotate-180');
                } else {
                    discoverMenu.classList.remove('opacity-0', '-translate-y-3', 'pointer-events-none');
                    chevron.classList.add('rotate-180');
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (wrapper && !wrapper.contains(e.target) && !discoverMenu.classList.contains('opacity-0')) {
                discoverMenu.classList.add('opacity-0', '-translate-y-3', 'pointer-events-none');
                chevron.classList.remove('rotate-180');
            }
        });

        // AJAX Beğeni Mantığı
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation(); // Kartın içine girilmesini engeller

                // Kullanıcı giriş yapmamışsa yönlendir
                <?php if(!isset($_SESSION['user_id'])): ?>
                    window.location.href = 'login.php';
                    return;
                <?php endif; ?>

                const topicId = btn.getAttribute('data-id');
                const icon = btn.querySelector('.like-icon');
                const countSpan = btn.querySelector('.like-count');

                try {
                    const response = await fetch('ajax_like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ topic_id: topicId })
                    });
                    
                    const result = await response.json();

                    if (result.status === 'success') {
                        // Rakamı güncelle
                        countSpan.innerText = result.new_count > 0 ? result.new_count : 'Beğen';
                        
                        // İkon stili güncelle
                        if (result.action === 'liked') {
                            icon.classList.add('fill-current', 'text-pink-500');
                            icon.classList.remove('text-slate-400');
                            countSpan.classList.add('text-pink-500');
                            countSpan.classList.remove('text-slate-400');
                        } else {
                            icon.classList.remove('fill-current', 'text-pink-500');
                            icon.classList.add('text-slate-400');
                            countSpan.classList.remove('text-pink-500');
                            countSpan.classList.add('text-slate-400');
                        }
                    } else {
                        console.error(result.message);
                    }
                } catch (error) {
                    console.error("Beğeni işlemi sırasında hata oluştu.", error);
                }
            });
        });
    });
</script>