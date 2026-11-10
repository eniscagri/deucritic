<?php
require_once __DIR__ . '/includes/i18n.php';

require_once 'includes/config.php';
require_once 'includes/helpers.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$topic_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$topic_id) { header('Location: index.php'); exit; }

$topic = fetch_topic($pdo, $topic_id);
if (!$topic) { header('Location: index.php'); exit; }

$entries = fetch_entries($pdo, $topic_id);
$is_logged_in = !empty($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;

$placeholders = [
    "Bu konuda ne düşünüyorsun?",
    "Sence haklı mı? Neden?",
    "Katılıyor musun yoksa karşı mı çıkıyorsun?",
    "Kendi deneyimini paylaşmak ister misin?",
    "Farklı bir bakış açısı sunabilir misin?"
];
$randomPlaceholder = $placeholders[array_rand($placeholders)];

if($is_logged_in){
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM entries WHERE user_id=? AND anonymous=1");
    $stmt->execute([$current_user_id]);
    $forceAnon = $stmt->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= htmlspecialchars($topic['title']) ?> - Deucritic</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { brandOrange: '#f97316', brandBlue: '#3b82f6' },
                    animation: { 'heart-pop': 'pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards' },
                    keyframes: {
                        pop: {
                            '0%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.3)' },
                            '100%': { transform: 'scale(1)' },
                        }
                    }
                }
            }
        }
        window.CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? "" ?>';
        window.TOPIC_ID = <?= (int)$topic_id ?>;
    </script>

    <style>
        body {
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(249, 115, 22, 0.05) 0px, transparent 50%), 
                              radial-gradient(at 100% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px) saturate(150%);
            -webkit-backdrop-filter: blur(20px) saturate(150%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06);
        }
        .onep-card { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .onep-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -5px rgba(0,0,0,0.08); border-color: rgba(255,255,255,1); }
        .image-zoom { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: zoom-in; }
        .image-zoom:hover { transform: scale(1.02); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="text-slate-800">

<?php include 'header.php'; ?>

<main class="max-w-3xl mx-auto mt-8 mb-24 px-4">
    
    <div class="glass-panel rounded-[2rem] p-8 mb-10 relative overflow-hidden group">
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-gradient-to-bl from-orange-300/30 to-brandBlue/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
        
        <div class="relative z-10 flex flex-col gap-4">
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight tracking-tight">
                <span data-dc-user-content><?= htmlspecialchars($topic['title']) ?></span>
            </h1>

            <?php if (!empty($topic['description'])): ?>
                <p class="text-slate-600 font-medium leading-relaxed text-lg">
                    <span data-dc-user-content><?= nl2br(htmlspecialchars($topic['description'])) ?></span>
                </p>
            <?php endif; ?>

            <div class="flex items-center gap-4 mt-6 pt-5 border-t border-slate-200/60">
                <?php if ($topic['anonymous']): ?>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100/80 rounded-xl">
                        <i class="fa-solid fa-mask text-slate-400"></i>
                        <span class="text-xs font-bold text-slate-600 italic">AnY Kullanıcı</span>
                    </div>
                <?php else: ?>
                    <?php $ppPath = !empty($topic['owner_pp']) ? '/uploads/profile_photos/'.htmlspecialchars($topic['owner_pp']) : '/uploads/default-avatar.png'; ?>
                    <a href="uye.php?user=<?= urlencode($topic['owner']) ?>" class="flex items-center gap-2.5 group/user">
                        <img src="<?= $ppPath ?>" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm group-hover/user:border-brandBlue transition-colors">
                        <span class="text-sm font-bold text-slate-700 group-hover/user:text-brandBlue transition-colors">@<?= htmlspecialchars($topic['owner']) ?></span>
                    </a>
                <?php endif; ?>
                <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                    
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/60 border border-slate-200/60 shadow-sm cursor-default hover:bg-white transition-colors">
    <svg class="w-4 h-4 text-brandOrange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    
    <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mt-0.5">
        <?php 
            $ts = strtotime($topic['created_at']);
            
            // Gece yarısına göre gün hesabı (En doğru yöntem)
            $bugun = strtotime('today midnight');
            $konu_gunu = strtotime('midnight', $ts);
            $gun_farki = round(($bugun - $konu_gunu) / 86400);

            if ($gun_farki == 0) {
                echo 'Bugün';
            } elseif ($gun_farki == 1) {
                echo 'Dün';
            } elseif ($gun_farki > 1 && $gun_farki < 7) {
                echo $gun_farki . ' gün önce';
            } elseif ($gun_farki >= 7 && $gun_farki <= 14) {
                echo 'Geçen hafta';
            } else {
                // Daha eskiyse tam tarih ver (Örn: 15 Nis 2026)
                $aylar = [
                    '01' => 'Oca', '02' => 'Şub', '03' => 'Mar', '04' => 'Nis',
                    '05' => 'May', '06' => 'Haz', '07' => 'Tem', '08' => 'Ağu',
                    '09' => 'Eyl', '10' => 'Eki', '11' => 'Kas', '12' => 'Ara'
                ];
                echo date('d', $ts) . ' ' . $aylar[date('m', $ts)] . ' ' . date('Y', $ts);
            }
        ?>
    </span>
    
    <span class="text-slate-300 font-bold mx-0.5">•</span>
    
    <span class="text-[11px] font-black text-slate-500 tracking-widest mt-0.5">
        <?= date('H:i', $ts) ?>
    </span>
</div>
                    
            </div>
        </div>
    </div>

    <section>
        <div class="flex items-center gap-3 mb-6 px-2">
            <div class="w-1.5 h-6 bg-gradient-to-b from-orange-400 to-brandBlue rounded-full"></div>
            <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Onep'ler <span class="text-slate-400 font-semibold text-sm ml-1">(<?= count($entries) ?>)</span></h2>
        </div>

        <div id="entries" class="space-y-6">
            <?php if (empty($entries)): ?>
                <div class="glass-panel rounded-[2rem] p-16 text-center border-dashed border-2 border-slate-200/50">
                    <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-5 shadow-inner">
                        <i class="fa-regular fa-comment-dots text-4xl text-slate-300"></i>
                    </div>
                    <p class="text-slate-500 font-bold text-lg">Burası çok sessiz.</p>
                    <p class="text-slate-400 text-sm mt-1">İlk düşünceni paylaşarak konuşmayı başlat.</p>
                </div>
            <?php else: foreach ($entries as $e): ?>
                <?php 
                    $likeCount = fetch_entry_like_count($pdo, $e['id']);
                    $userLiked = $is_logged_in ? has_user_liked($pdo, $current_user_id, $e['id']) : false;
                    $likeIconClass = $userLiked ? 'text-pink-500 fill-current' : 'text-slate-400 group-hover/like:text-pink-400';
                ?>
                <article class="onep-card glass-panel p-6 sm:p-7 rounded-[2rem] relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-yellow-200 to-orange-300 p-0.5 shadow-sm">
                                <div class="w-full h-full bg-white rounded-full flex items-center justify-center text-lg font-black text-slate-700">
                                    <?= ($e['entry_anon'] || $topic['anonymous']) ? '?' : strtoupper(mb_substr($e['username'],0,1)) ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-[15px] font-bold text-slate-800">
                                    <?= ($e['entry_anon'] || $topic['anonymous']) ? '<span class="italic text-slate-500">AnY Kullanıcı</span>' : '<a href="uye.php?user='.urlencode($e['username']).'" class="hover:text-brandBlue transition-colors">@'.htmlspecialchars($e['username']).'</a>' ?>
                                </div>
                                <div class="text-xs font-semibold text-slate-400 mt-0.5"><?= time_ago($e['created_at']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div data-dc-user-content class="text-slate-700 text-[16px] font-medium leading-relaxed break-words pl-[3.5rem]">
                        <?= linkify_html($e['content']) ?>
                    </div>

                    <?php if (!empty($e['image'])): ?>
                        <div class="mt-4 ml-[3.5rem] rounded-2xl overflow-hidden border border-slate-100 shadow-sm inline-block">
                            <img src="<?= htmlspecialchars($e['image']) ?>" class="image-zoom max-h-56 w-auto object-cover" loading="lazy" onclick="openLightbox('<?= htmlspecialchars($e['image']) ?>')">
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 ml-[3.5rem] flex items-center gap-6">
                        <button class="entry-like-btn group/like flex items-center gap-2" data-id="<?= $e['id'] ?>">
                            <div class="p-2 rounded-full group-hover/like:bg-pink-50 transition-colors">
                                <svg class="w-5 h-5 transition-colors duration-300 <?= $likeIconClass ?> like-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <span class="like-count text-sm font-bold <?= $userLiked ? 'text-pink-500' : 'text-slate-500 group-hover/like:text-pink-500' ?> transition-colors">
                                <?= $likeCount > 0 ? $likeCount : 'Beğen' ?>
                            </span>
                        </button>
                    </div>
                </article>
            <?php endforeach; endif; ?>
        </div>
    </section>

    <?php if($is_logged_in): ?>
    <section class="mt-12" id="comments">
        <div class="glass-panel p-2 rounded-[2rem] focus-within:ring-4 focus-within:ring-orange-500/20 focus-within:border-orange-300 transition-all duration-300 relative bg-white/90">
            
            <textarea id="entryContent" maxlength="500" rows="3" placeholder="<?= $randomPlaceholder ?>" 
                      class="w-full bg-transparent border-none rounded-t-[1.5rem] p-5 text-slate-800 text-lg font-medium resize-none focus:ring-0 focus:outline-none placeholder-slate-400"></textarea>
            
            <div id="imagePreviewContainer" class="hidden px-5 pb-3 relative">
                <img id="imagePreview" src="" class="h-20 w-auto rounded-xl object-cover border border-slate-200">
                <button id="removeImageBtn" class="absolute top-1 left-[4.5rem] bg-slate-900/70 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-500 transition-colors">&times;</button>
            </div>

            <div id="emojiPanel" class="hidden absolute left-5 bottom-20 bg-white border border-slate-200 shadow-xl rounded-2xl p-3 grid grid-cols-5 gap-2 z-50">
                <?php $emojis = ['😀','😂','🥰','😎','🤔','😡','👍','🔥','💯','✨']; 
                foreach($emojis as $emoji): ?>
                    <button class="emoji-select w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-lg text-xl transition-colors"><?= $emoji ?></button>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between p-3 bg-slate-50/50 rounded-b-[1.5rem] border-t border-slate-100">
                <div class="flex items-center gap-2 mb-3 sm:mb-0 relative">
                    <button id="emojiBtn" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white hover:bg-slate-100 shadow-sm border border-slate-200 transition-colors text-lg" title="Emoji">😀</button>
                    
                    <label class="w-10 h-10 flex items-center justify-center rounded-xl bg-white hover:bg-slate-100 shadow-sm border border-slate-200 transition-colors cursor-pointer text-slate-500 hover:text-brandBlue" title="Fotoğraf Ekle">
                        <input type="file" id="imageInput" accept="image/*" class="hidden">
                        <i class="fa-solid fa-camera text-lg"></i>
                    </label>

                    <?php if($forceAnon): ?>
                        <span class="text-[10px] text-orange-600 font-bold ml-2 px-2.5 py-1 bg-orange-100 border border-orange-200 rounded-lg">Gizlilik Açık</span>
                    <?php else: ?>
                        <label class="flex items-center gap-2 ml-3 cursor-pointer group bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm hover:border-orange-300 transition-colors">
                            <input type="checkbox" id="anonCheckbox" class="w-4 h-4 text-brandOrange bg-slate-100 border-slate-300 rounded focus:ring-brandOrange focus:ring-2">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-slate-800 select-none">Anonim Paylaş</span>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                    <span id="charCount" class="text-xs font-extrabold text-slate-300">0/500</span>
                    <button id="postEntryBtn" class="px-6 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-brandOrange hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-2 shadow-md hover:shadow-orange-500/30">
                        <span>Oneple</span>
                        <svg id="loader" class="w-4 h-4 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

</main>

<div id="toast" class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl font-bold text-sm transition-all duration-300 opacity-0 translate-y-10 pointer-events-none z-50">
    Mesaj buraya gelecek
</div>

<div id="lightbox" class="fixed inset-0 bg-black/90 backdrop-blur-md hidden items-center justify-center z-[100] opacity-0 transition-opacity duration-300 cursor-zoom-out">
    <button class="absolute top-6 right-8 text-white/50 hover:text-white transition-colors p-2" onclick="closeLightbox()">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="lightbox-img" src="" class="max-h-[85vh] max-w-[90vw] rounded-2xl shadow-2xl scale-95 transition-transform duration-300 object-contain">
</div>

<script>
    function showToast(msg, isError = true) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = `fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-full shadow-2xl font-bold text-sm transition-all duration-300 z-50 ${isError ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`;
        toast.classList.remove('opacity-0', 'translate-y-10');
        setTimeout(() => toast.classList.add('opacity-0', 'translate-y-10'), 3000);
    }

    function openLightbox(src) {
        const lb = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        img.src = src;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
        setTimeout(() => { lb.classList.remove('opacity-0'); img.classList.remove('scale-95'); }, 10);
    }
    function closeLightbox() {
        const lb = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        lb.classList.add('opacity-0'); img.classList.add('scale-95');
        setTimeout(() => { lb.classList.add('hidden'); lb.classList.remove('flex'); }, 300);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('lightbox').addEventListener('click', closeLightbox);
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeLightbox(); });

        const txt = document.getElementById('entryContent');
        const postBtn = document.getElementById('postEntryBtn');
        const charCount = document.getElementById('charCount');
        const fileInput = document.getElementById('imageInput');
        
        // Emoji Seçici
        const emojiBtn = document.getElementById('emojiBtn');
        const emojiPanel = document.getElementById('emojiPanel');
        if(emojiBtn) {
            emojiBtn.addEventListener('click', (e) => { e.stopPropagation(); emojiPanel.classList.toggle('hidden'); });
            document.querySelectorAll('.emoji-select').forEach(btn => {
                btn.addEventListener('click', () => { txt.value += btn.textContent; txt.dispatchEvent(new Event('input')); emojiPanel.classList.add('hidden'); txt.focus(); });
            });
            document.addEventListener('click', (e) => { if(!emojiPanel.contains(e.target)) emojiPanel.classList.add('hidden'); });
        }

        if(fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('imagePreview').src = e.target.result;
                        document.getElementById('imagePreviewContainer').classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });
            document.getElementById('removeImageBtn').addEventListener('click', () => {
                fileInput.value = '';
                document.getElementById('imagePreviewContainer').classList.add('hidden');
            });
        }

        if(txt) {
            txt.addEventListener('input', () => {
                const len = txt.value.length;
                charCount.textContent = `${len}/500`;
                charCount.className = len > 480 ? 'text-xs font-extrabold text-red-500' : (len > 0 ? 'text-xs font-extrabold text-brandOrange' : 'text-xs font-extrabold text-slate-300');
            });
        }

        if(postBtn) {
            postBtn.addEventListener('click', async () => {
                const content = txt.value.trim();
                if(content.length < 5){ showToast('Onep çok kısa! En az 5 karakter yazmalısın.'); return; }

                const anon = document.getElementById('anonCheckbox')?.checked ? '1' : '0';
                const form = new FormData();
                form.append('topic_id', window.TOPIC_ID);
                form.append('content', content);
                form.append('anonymous', anon);
                if(fileInput.files[0]) form.append('image', fileInput.files[0]);

                postBtn.disabled = true;
                document.getElementById('loader').classList.remove('hidden');
                postBtn.querySelector('span').textContent = 'Gönderiliyor...';

                try {
                    const res = await fetch('actions/post_entry.php', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': window.CSRF_TOKEN }, body: form });
                    if (res.status === 401) {
                        window.location.href = 'login.php';
                        return;
                    }
                    const data = await res.json();
                    if(!data.success) throw new Error(data.error || 'Bilinmeyen bir hata oluştu.');
                    location.reload(); 
                } catch(err){
                    showToast(err.message);
                } finally {
                    postBtn.disabled = false;
                    document.getElementById('loader').classList.add('hidden');
                    postBtn.querySelector('span').textContent = 'Oneple';
                }
            });
        }

        document.querySelectorAll('.entry-like-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                <?php if(!$is_logged_in): ?> window.location.href = 'login.php'; return; <?php endif; ?>

                const entryId = btn.getAttribute('data-id');
                const icon = btn.querySelector('.like-icon');
                const countSpan = btn.querySelector('.like-count');

                icon.classList.add('animate-heart-pop');
                setTimeout(() => icon.classList.remove('animate-heart-pop'), 300);

                try {
                    const response = await fetch('ajax_like_entry.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ entry_id: entryId })
                    });
                    
                    const result = await response.json();
                    if (result.status === 'success') {
                        countSpan.innerText = result.new_count > 0 ? result.new_count : 'Beğen';
                        if (result.action === 'liked') {
                            icon.classList.add('fill-current', 'text-pink-500');
                            icon.classList.remove('text-slate-400');
                            countSpan.classList.add('text-pink-500');
                            countSpan.classList.remove('text-slate-500', 'group-hover/like:text-pink-500');
                        } else {
                            icon.classList.remove('fill-current', 'text-pink-500');
                            icon.classList.add('text-slate-400');
                            countSpan.classList.remove('text-pink-500');
                            countSpan.classList.add('text-slate-500', 'group-hover/like:text-pink-500');
                        }
                    } else { showToast(result.message); }
                } catch (error) { showToast("Bağlantı hatası oluştu."); }
            });
        });
    });
</script>
</body>
</html>
