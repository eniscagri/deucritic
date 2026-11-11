<?php
require_once __DIR__ . '/includes/i18n.php';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';

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
$forceAnon = false;

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        window.CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        window.TOPIC_ID = <?= (int)$topic_id ?>;
        window.FORCE_ANON = <?= $forceAnon ? 'true' : 'false' ?>;
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
        .topic-composer { border: 1px solid #e8dfcd; background: #fffdf8; box-shadow: 0 18px 48px -30px rgba(29,53,47,.42); }
        .topic-composer textarea { min-height: 132px; line-height: 1.7; }
        .topic-tool { width: 42px; height: 42px; border: 1px solid #e4dccd; background: #fffdf8; color: #53645a; transition: .18s ease; }
        .topic-tool:hover { border-color: #b84834; color: #b84834; background: #fbf5eb; }
        .topic-submit { background: #1d352f; transition: transform .18s ease, background .18s ease, opacity .18s ease; }
        .topic-submit:hover:not(:disabled) { background: #b84834; transform: translateY(-1px); }
        .topic-submit:disabled { opacity: .65; cursor: wait; }
        .topic-card { border: 1px solid #e8dfcd; background: #fffdf8; box-shadow: 0 12px 35px -28px rgba(29,53,47,.5); }
        .topic-card:hover { border-color: #d7cdbb; transform: translateY(-2px); }
        @media (max-width: 640px) {
            .topic-composer textarea { min-height: 118px; font-size: 16px; }
            .topic-card-content { padding-left: 0; }
            .topic-card-media { margin-left: 0; }
        }
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
                        <span class="text-xs font-bold text-slate-600 italic">Anonim kullanıcı</span>
                    </div>
                <?php else: ?>
                    <?php $ppPath = !empty($topic['owner_pp']) ? ''.htmlspecialchars($topic['owner_pp']) : '/uploads/default-avatar.png'; ?>
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
                <a href="#comments" class="ml-auto inline-flex min-h-10 items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-orange-700">
                    <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                    Yanıtla
                </a>
                    
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
                <article class="onep-card topic-card p-5 sm:p-7 rounded-[1.5rem] relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-yellow-200 to-orange-300 p-0.5 shadow-sm">
                                <div class="w-full h-full bg-white rounded-full flex items-center justify-center text-lg font-black text-slate-700">
                                    <?= ($e['entry_anon'] || $topic['anonymous']) ? '?' : strtoupper(mb_substr($e['username'],0,1)) ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-[15px] font-bold text-slate-800">
                                    <?= ($e['entry_anon'] || $topic['anonymous']) ? '<span class="italic text-slate-500">Anonim kullanıcı</span>' : '<a href="uye.php?user='.urlencode($e['username']).'" class="hover:text-brandBlue transition-colors">@'.htmlspecialchars($e['username'], ENT_QUOTES, 'UTF-8').'</a>' ?>
                                </div>
                                <div class="text-xs font-semibold text-slate-400 mt-0.5"><?= time_ago($e['created_at']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div data-dc-user-content class="topic-card-content text-slate-700 text-[16px] font-medium leading-relaxed break-words pl-[3.5rem]">
                        <?= linkify_html($e['content']) ?>
                    </div>

                    <?php if (!empty($e['image'])): ?>
                        <div class="topic-card-media mt-4 ml-[3.5rem] rounded-2xl overflow-hidden border border-slate-100 shadow-sm inline-block">
                            <img src="<?= htmlspecialchars($e['image'], ENT_QUOTES, 'UTF-8') ?>" alt="Onep görseli" class="image-zoom max-h-72 max-w-full w-auto object-cover" loading="lazy" onclick="openLightbox(this.src)">
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 ml-[3.5rem] flex items-center gap-6">
                        <button type="button" class="entry-like-btn group/like flex items-center gap-2" data-id="<?= (int) $e['id'] ?>" aria-label="Onep'i beğen">
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

    <section class="mt-12" id="comments">
        <?php if($is_logged_in): ?>
        <form id="entryForm" class="topic-composer rounded-3xl p-3 sm:p-5 relative" enctype="multipart/form-data">
            <div class="flex items-start gap-3 px-2 pt-2">
                <div class="hidden sm:flex w-10 h-10 shrink-0 rounded-full bg-[#e9e1d1] items-center justify-center text-[#1d352f] font-bold" aria-hidden="true">
                    <?= htmlspecialchars(strtoupper(mb_substr($_SESSION['username'] ?? 'D', 0, 1, 'UTF-8')), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="min-w-0 flex-1">
                    <label for="entryContent" class="block text-sm font-bold text-slate-800">Düşünceni paylaş</label>
                    <p class="mt-1 text-xs text-slate-500">Saygılı, konuya katkı sağlayan bir yanıt yaz. En fazla 500 karakter.</p>
                </div>
            </div>
            <textarea id="entryContent" name="content" maxlength="500" rows="4" placeholder="<?= htmlspecialchars($randomPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                      class="mt-3 w-full rounded-2xl border border-slate-200 bg-white/70 p-4 text-base text-slate-800 resize-y focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                      aria-describedby="composerHelp charCount" required></textarea>
            <div class="flex flex-wrap items-center justify-between gap-2 px-1 pb-3">
                <span id="composerHelp" class="text-xs text-slate-500">Küfür, hakaret ve +18 içerik paylaşımına izin verilmez.</span>
                <span id="charCount" class="text-xs font-bold text-slate-500" aria-live="polite">0 / 500</span>
            </div>

            <div id="imagePreviewContainer" class="hidden px-1 pb-4">
                <div class="relative inline-flex rounded-2xl border border-slate-200 bg-white p-2">
                    <img id="imagePreview" src="" alt="Seçilen görsel önizlemesi" class="max-h-40 max-w-full rounded-xl object-contain">
                    <button id="removeImageBtn" type="button" class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-red-600" aria-label="Görseli kaldır">&times;</button>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200/80 pt-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="topic-tool flex cursor-pointer items-center justify-center rounded-xl" title="Görsel ekle">
                        <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        <i class="fa-regular fa-image" aria-hidden="true"></i>
                        <span class="sr-only">Görsel ekle</span>
                    </label>
                    <?php if($forceAnon): ?>
                        <span class="rounded-full bg-[#f7eadb] px-3 py-2 text-xs font-bold text-[#9b482f]"><i class="fa-solid fa-user-secret mr-1" aria-hidden="true"></i>Anonim paylaşım açık</span>
                    <?php else: ?>
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                            <input type="checkbox" id="anonCheckbox" class="h-4 w-4 accent-[#b84834]">
                            <span>Anonim paylaş</span>
                        </label>
                    <?php endif; ?>
                </div>
                <button id="postEntryBtn" type="submit" class="topic-submit inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white" aria-live="polite">
                    <svg id="loader" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <span id="submitLabel">Yanıtı paylaş</span>
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="topic-composer rounded-3xl p-6 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-xl font-bold">Söz sende</h3>
                    <p class="mt-1 text-sm text-slate-600">Bu konuya yanıt vermek ve sohbete katılmak için giriş yap.</p>
                </div>
                <a href="login.php?redirect=<?= urlencode('topic.php?id=' . $topic_id . '#comments') ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-orange-700">
                    Giriş yap <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </section>

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
    let toastTimer;

    function showToast(msg, isError = true) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className = `fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-full shadow-2xl font-bold text-sm transition-all duration-300 z-50 ${isError ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`;
        toast.classList.remove('opacity-0', 'translate-y-10');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => toast.classList.add('opacity-0', 'translate-y-10'), 3200);
    }

    function openLightbox(src) {
        const lb = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        img.src = src;
        img.alt = 'Büyütülmüş görsel';
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
        document.getElementById('lightbox').addEventListener('click', (event) => {
            if (event.target.id === 'lightbox') closeLightbox();
        });
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeLightbox(); });

        const txt = document.getElementById('entryContent');
        const entryForm = document.getElementById('entryForm');
        const postBtn = document.getElementById('postEntryBtn');
        const charCount = document.getElementById('charCount');
        const fileInput = document.getElementById('imageInput');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showToast('Yalnızca JPEG, PNG veya WEBP görseli ekleyebilirsin.');
                    this.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    showToast('Görsel boyutu en fazla 2 MB olabilir.');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (event) => {
                    document.getElementById('imagePreview').src = event.target.result;
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                };
                reader.onerror = () => showToast('Görsel önizlemesi oluşturulamadı.');
                reader.readAsDataURL(file);
            });
            document.getElementById('removeImageBtn').addEventListener('click', () => {
                fileInput.value = '';
                document.getElementById('imagePreview').src = '';
                document.getElementById('imagePreviewContainer').classList.add('hidden');
            });
        }

        if (txt && charCount) {
            txt.addEventListener('input', () => {
                const len = txt.value.length;
                charCount.textContent = `${len} / 500`;
                charCount.className = len > 480 ? 'text-xs font-bold text-red-600' : 'text-xs font-bold text-slate-500';
            });
            txt.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key === 'Enter' && entryForm) {
                    event.preventDefault();
                    entryForm.requestSubmit();
                }
            });
        }

        if (entryForm && postBtn) {
            entryForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const content = txt.value.trim();
                if (content.length < 5) {
                    showToast('Yanıtın en az 5 karakter olmalı.');
                    txt.focus();
                    return;
                }

                const anonCheckbox = document.getElementById('anonCheckbox');
                const form = new FormData();
                form.append('topic_id', window.TOPIC_ID);
                form.append('content', content);
                form.append('anonymous', window.FORCE_ANON || (anonCheckbox && anonCheckbox.checked) ? '1' : '0');
                if (fileInput && fileInput.files[0]) form.append('image', fileInput.files[0]);

                postBtn.disabled = true;
                document.getElementById('loader').classList.remove('hidden');
                document.getElementById('submitLabel').textContent = 'Paylaşılıyor...';

                try {
                    const res = await fetch('/actions/post_entry.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: { 'X-CSRF-Token': window.CSRF_TOKEN },
                        body: form
                    });
                    if (res.status === 401) {
                        window.location.href = `login.php?redirect=${encodeURIComponent(`topic.php?id=${window.TOPIC_ID}#comments`)}`;
                        return;
                    }
                    const data = await res.json().catch(() => null);
                    if (!res.ok || !data || !data.success) {
                        throw new Error(data && data.error ? data.error : 'Yanıt gönderilemedi. Lütfen tekrar dene.');
                    }
                    showToast('Yanıtın paylaşıldı.', false);
                    window.location.reload();
                } catch (error) {
                    showToast(error.message || 'Bağlantı hatası oluştu.');
                    postBtn.disabled = false;
                    document.getElementById('loader').classList.add('hidden');
                    document.getElementById('submitLabel').textContent = 'Yanıtı paylaş';
                }
            });
        }

        document.querySelectorAll('.entry-like-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                <?php if(!$is_logged_in): ?> window.location.href = `login.php?redirect=${encodeURIComponent(`topic.php?id=${window.TOPIC_ID}#comments`)}`; return; <?php endif; ?>

                const entryId = btn.getAttribute('data-id');
                const icon = btn.querySelector('.like-icon');
                const countSpan = btn.querySelector('.like-count');

                icon.classList.add('animate-heart-pop');
                setTimeout(() => icon.classList.remove('animate-heart-pop'), 300);

                try {
                    const response = await fetch('ajax_like_entry.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
                        body: JSON.stringify({ entry_id: entryId })
                    });
                    
                    const result = await response.json();
                    if (response.ok && result.status === 'success') {
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
                    } else { showToast(result.message || 'Beğeni kaydedilemedi.'); }
                } catch (error) { showToast('Bağlantı hatası oluştu.'); }
            });
        });
    });
</script>
</body>
</html>
