<?php
// Uyarı Fonksiyonu
if (!function_exists('warnUser')) {
    function warnUser($pdo, $user_id, $reason) {
        $stmt = $pdo->prepare("UPDATE users SET warnings = IFNULL(warnings, 0) + 1 WHERE id = ?");
        $stmt->execute([$user_id]);

        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, 'warning', NOW())");
        $stmt->execute([$user_id, "Topluluk kurallarını ihlal ettiniz: $reason"]);

        $stmt = $pdo->prepare("SELECT warnings FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $warnings = (int)$stmt->fetchColumn();

        if ($warnings >= 3) {
            $stmt = $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
            $stmt->execute([$user_id]);

            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, 'error', NOW())");
            $stmt->execute([$user_id, "Hesabınız 3 kural ihlali nedeniyle otomatik olarak yasaklandı."]);
        }
    }
}

if (!function_exists('normalizeContent')) {
    function normalizeContent($text) {
        $text = mb_strtolower($text, 'UTF-8');
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'];
        $latin = ['c', 'g', 'i', 'o', 's', 'u'];
        return str_replace($turkish, $latin, $text);
    }
}

$postMessage = null; 

// Sadece Text (Onep) Post işlemi bu dosyada işleniyor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && $is_logged_in) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];
    
    $cooldown_seconds = 13; 

    $stmt = $pdo->prepare("SELECT created_at FROM topics WHERE created_by = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $last_post = $stmt->fetch(PDO::FETCH_ASSOC);

    $is_on_cooldown = false;
    if ($last_post) {
        $db_now = $pdo->query("SELECT NOW()")->fetchColumn();
        $time_diff = strtotime($db_now) - strtotime($last_post['created_at']);

        if ($time_diff < $cooldown_seconds) {
            $remaining = $cooldown_seconds - $time_diff;
            if ($remaining > 0) {
                $postMessage = ["type" => "error", "text" => "Biraz yavaş! Yeni bir Onep göndermek için {$remaining} saniye beklemelisin ⏳"];
                $is_on_cooldown = true;
            }
        }
    }

    if (!$is_on_cooldown) {
        if ($title === '') {
            $postMessage = ["type" => "error", "text" => "Başlık boş bırakılamaz!"];
        } else {
            $forbidden_terms = [
                'usame bin ladin', 'osama bin laden', 'bombalı saldırı', 'intihar saldırısı', 
                'deprem sakası', 'sel felaketi', 'irkci', 'nefret soylemi', 'homofobi', 
                'transfobi', 'alevi dusmanligi', 'kurt dusmanligi', 'yahudi dusmanligi'
            ];

            $normalized = normalizeContent($title . ' ' . $description);
            $matched_term = null;

            foreach ($forbidden_terms as $term) {
                if (strpos($normalized, $term) !== false) {
                    $matched_term = $term;
                    break;
                }
            }

            if ($matched_term) {
                warnUser($pdo, $user_id, "Uygunsuz içerik tespiti: {$matched_term}");
                
                $stmt = $pdo->prepare("INSERT INTO moderation_logs (user_id, reason, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$user_id, "Filtreye takılan kelime: $matched_term"]);
                
                $postMessage = ["type" => "error", "text" => "Paylaşım topluluk kurallarına aykırı olduğu için engellendi 🚫"];
            } else {
                $stmt = $pdo->prepare("INSERT INTO topics (title, description, anonymous, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $anonymous, $user_id]);

                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$user_id, "Yeni Onep'in başarıyla paylaşıldı! 🎉"]);
                
                $postMessage = ["type" => "success", "text" => "Harika! Onep başarıyla oluşturuldu 🚀"];
            }
        }
    }
}
?>

<?php if ($is_logged_in): ?>
<style>
    .glass-create-panel {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(24px) saturate(150%);
        -webkit-backdrop-filter: blur(24px) saturate(150%);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
    }
    
    /* Hikaye Önizleme Container'ı için */
    .story-preview-bg {
        background-image: radial-gradient(circle at center, #1d352f 0%, #1d352f 100%);
    }
</style>

<section class="w-full max-w-2xl mx-auto mt-4 mb-8 px-4 sm:px-0">
    <div class="glass-create-panel p-5 sm:p-8 rounded-[2rem] relative overflow-hidden transition-all duration-300">
        
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-bl from-orange-400/20 to-yellow-300/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-gradient-to-tr from-blue-400/20 to-indigo-300/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex bg-slate-100/80 backdrop-blur-md p-1.5 rounded-2xl mb-6 shadow-inner">
            <button type="button" id="tabBtnOnep" class="flex-1 flex items-center justify-center gap-2 py-2.5 text-[15px] font-bold rounded-xl bg-white shadow-sm text-slate-800 transition-all duration-200">
                <i class="fa-solid fa-pen-nib text-orange-500"></i> Yazı Paylaş
            </button>
            <button type="button" id="tabBtnStory" class="flex-1 flex items-center justify-center gap-2 py-2.5 text-[15px] font-bold rounded-xl text-slate-500 hover:text-slate-700 transition-all duration-200">
                <i class="fa-solid fa-camera text-blue-500"></i> Hikaye Paylaş
            </button>
        </div>

        <div id="tabContentOnep" class="relative z-10 animate-[fadeIn_0.3s_ease-in-out]">
            <form id="onepForm" method="POST" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-500 uppercase tracking-widest ml-1">Ana Fikir (Başlık)</label>
                    <div class="relative">
                        <input type="text" name="title" maxlength="100" id="titleInput"
                            class="w-full bg-white/70 border border-slate-200/80 p-4 pr-16 rounded-2xl text-slate-800 font-bold focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-400 transition-all placeholder-slate-400 shadow-sm"
                            required>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 tracking-widest" id="titleCount">0/100</div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-500 uppercase tracking-widest ml-1">Detaylar</label>
                    <div class="relative">
                        <textarea name="description" maxlength="1000" rows="3" id="descInput"
                            class="w-full bg-white/70 border border-slate-200/80 p-4 rounded-2xl text-slate-700 font-medium focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-400 transition-all resize-none placeholder-slate-400 shadow-sm"></textarea>
                        <div class="absolute right-4 bottom-4 text-[10px] font-black text-slate-400 tracking-widest" id="descCount">0/1000</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                    <label class="flex items-center justify-center sm:justify-start gap-3 cursor-pointer group bg-white/70 px-4 py-3 rounded-xl border border-slate-200 w-full sm:w-auto hover:border-orange-300 transition-colors shadow-sm">
                        <div class="relative">
                            <input type="checkbox" name="anonymous" class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500 shadow-inner"></div>
                        </div>
                        <span class="text-[15px] font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Anonim (AnY) Paylaş</span>
                    </label>

                    <button type="submit" id="submitOnepBtn"
                        class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 text-white text-[15px] font-bold rounded-xl shadow-lg hover:bg-orange-500 hover:shadow-orange-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2">
                        <span id="btnTextOnep">Yolla Gitsin!</span>
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </div>
            </form>
        </div>

        <div id="tabContentStory" class="relative z-10 hidden animate-[fadeIn_0.3s_ease-in-out]">
            <form action="actions/upload_story.php" method="POST" enctype="multipart/form-data" id="storyUploadForm" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">

                <div id="dropArea" class="group relative border-2 border-dashed border-slate-300 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-2xl p-4 text-center transition-all duration-300 cursor-pointer min-h-[220px] sm:min-h-[250px] flex flex-col items-center justify-center overflow-hidden shadow-inner">
                    
                    <input type="file" name="story_media" id="storyMedia" accept="image/*,video/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    
                    <div id="defaultState" class="flex flex-col items-center gap-3 transition-opacity duration-300">
                        <div class="bg-white p-4 rounded-full shadow-md border border-slate-100 group-hover:scale-110 group-hover:shadow-blue-100 transition-all duration-300">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-blue-500"></i>
                        </div>
                        <div>
                            <p class="text-[15px] font-bold text-slate-700">Tıkla veya Sürükle Bırak</p>
                            <p class="text-xs font-semibold text-slate-400 mt-1">Fotoğraf veya Video (Max: 10MB)</p>
                        </div>
                    </div>

                    <div id="previewContainer" class="hidden w-full h-full absolute inset-0 story-preview-bg z-20 flex items-center justify-center">
                        </div>
                </div>

                <div class="space-y-1.5">
                    <div class="relative">
                        <textarea name="text_content" maxlength="300" rows="2" id="storyText"
                            placeholder="Bu anı nasıl anlatırsın? ✨" 
                            class="w-full bg-white/70 border border-slate-200/80 rounded-2xl px-4 py-3.5 text-[15px] font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400 transition-all resize-none shadow-sm"></textarea>
                        <div class="absolute right-4 bottom-3 text-[10px] font-black text-slate-400 tracking-widest" id="storyCharCount">0/300</div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 pt-2">
                    <button type="button" id="clearStoryBtn" class="hidden text-[14px] font-bold text-red-500 hover:text-white hover:bg-red-500 px-4 py-2.5 rounded-xl border border-transparent hover:border-red-600 transition-all shadow-sm">
                        <i class="fa-solid fa-trash-can mr-1"></i> İptal
                    </button>
                    
                    <div class="flex-1"></div> 

                    <button type="submit" id="submitStoryBtn" class="w-full sm:w-auto bg-slate-900 text-white hover:bg-blue-600 hover:shadow-blue-500/30 px-8 py-3.5 rounded-xl font-bold text-[15px] shadow-lg transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-circle-notch fa-spin hidden text-sm" id="loadingStoryIcon"></i>
                        <span id="btnTextStory">Hikayeyi Paylaş</span>
                        <i class="fa-solid fa-paper-plane text-sm" id="sendStoryIcon"></i>
                    </button>
                </div>
            </form>
        </div>

    </div>
</section>

<div id="npostToast" class="fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-3.5 rounded-full shadow-2xl font-bold text-[15px] transition-all duration-500 opacity-0 translate-y-10 pointer-events-none z-[100] flex items-center gap-2.5">
    <span id="npostToastIcon"></span>
    <span id="npostToastMsg"></span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        /* ----- SEKME (TAB) YÖNETİMİ ----- */
        const tabBtnOnep = document.getElementById('tabBtnOnep');
        const tabBtnStory = document.getElementById('tabBtnStory');
        const tabContentOnep = document.getElementById('tabContentOnep');
        const tabContentStory = document.getElementById('tabContentStory');

        function switchTab(activeBtn, inactiveBtn, activeContent, inactiveContent, activeIconColor) {
            // Aktif Buton
            activeBtn.classList.add('bg-white', 'shadow-sm', 'text-slate-800');
            activeBtn.classList.remove('text-slate-500', 'hover:text-slate-700');
            
            // Pasif Buton
            inactiveBtn.classList.remove('bg-white', 'shadow-sm', 'text-slate-800');
            inactiveBtn.classList.add('text-slate-500', 'hover:text-slate-700');

            // İçerikler
            activeContent.classList.remove('hidden');
            inactiveContent.classList.add('hidden');
        }

        tabBtnOnep.addEventListener('click', () => {
            switchTab(tabBtnOnep, tabBtnStory, tabContentOnep, tabContentStory);
        });

        tabBtnStory.addEventListener('click', () => {
            switchTab(tabBtnStory, tabBtnOnep, tabContentStory, tabContentOnep);
        });

        /* ----- KARAKTER SAYACI ----- */
        const setupCounter = (inputId, countId, max) => {
            const input = document.getElementById(inputId);
            const count = document.getElementById(countId);
            if(input && count) {
                input.addEventListener('input', () => {
                    const len = input.value.length;
                    count.textContent = `${len}/${max}`;
                    if (len >= max - 10) {
                        count.classList.add('text-red-500');
                        count.classList.remove('text-slate-400');
                    } else {
                        count.classList.remove('text-red-500');
                        count.classList.add('text-slate-400');
                    }
                });
            }
        };

        setupCounter('titleInput', 'titleCount', 100);
        setupCounter('descInput', 'descCount', 1000);
        setupCounter('storyText', 'storyCharCount', 300);

        /* ----- ONEP FORMU SUBMİT ----- */
        document.getElementById('onepForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitOnepBtn');
            const btnText = document.getElementById('btnTextOnep');
            const icon = btn.querySelector('i');

            if (btn.disabled) return false;

            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed', 'bg-slate-700');
            btn.classList.remove('hover:-translate-y-0.5', 'hover:bg-orange-500');
            btnText.innerText = "Gönderiliyor...";
            if(icon) {
                icon.className = "fa-solid fa-circle-notch fa-spin text-sm";
            }
        });

        // Dinamik Placeholder (Sadece Onep için)
        const titles = [
            "Bugün kampüste ne fark ettin?", 
            "Sence de yemekhane sıraları...", 
            "Finaller hakkında bir itirafın var mı?", 
            "İzmir'de öğrenci olmak..."
        ];
        const titleInput = document.getElementById('titleInput');
        if(titleInput) {
            titleInput.placeholder = titles[Math.floor(Math.random() * titles.length)];
        }

        /* ----- HİKAYE (STORY) FORMU İŞLEMLERİ ----- */
        const mediaInput = document.getElementById('storyMedia');
        const previewContainer = document.getElementById('previewContainer');
        const defaultState = document.getElementById('defaultState');
        const clearStoryBtn = document.getElementById('clearStoryBtn');
        const dropArea = document.getElementById('dropArea');
        const storyForm = document.getElementById('storyUploadForm');
        
        const MAX_SIZE_MB = 10;

        // Sürükle Bırak Efektleri
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, e => {
                e.preventDefault(); e.stopPropagation();
                dropArea.classList.add('border-blue-500', 'bg-blue-50/80');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, e => {
                e.preventDefault(); e.stopPropagation();
                dropArea.classList.remove('border-blue-500', 'bg-blue-50/80');
            }, false);
        });

        dropArea.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if(files.length > 0) {
                mediaInput.files = files;
                mediaInput.dispatchEvent(new Event('change'));
            }
        });

        // İptal Etme İşlevi
        const resetMedia = () => {
            mediaInput.value = '';
            previewContainer.innerHTML = '';
            previewContainer.classList.add('hidden');
            defaultState.classList.remove('hidden');
            clearStoryBtn.classList.add('hidden');
        };

        clearStoryBtn.addEventListener('click', resetMedia);

        // Dosya Seçimi ve Önizleme
        mediaInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) {
                resetMedia();
                return;
            }

            if(file.size > MAX_SIZE_MB * 1024 * 1024){
                alert(`Dosya boyutu ${MAX_SIZE_MB}MB'tan büyük olamaz.`);
                resetMedia();
                return;
            }

            defaultState.classList.add('hidden');
            previewContainer.classList.remove('hidden');
            previewContainer.innerHTML = '';
            clearStoryBtn.classList.remove('hidden');
            clearStoryBtn.classList.add('flex'); // Mobile uyumlu görünüm için

            const mime = file.type;

            if (mime.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'w-full h-full object-contain';
                previewContainer.appendChild(img);
            } else if (mime.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.controls = false;
                video.autoplay = true;
                video.loop = true;
                video.muted = true;
                video.className = 'w-full h-full object-contain';
                previewContainer.appendChild(video);
            } else {
                alert('Desteklenmeyen dosya türü.');
                resetMedia();
            }
        });

        // Hikaye Formu Submit
        storyForm.addEventListener('submit', () => {
            const btn = document.getElementById('submitStoryBtn');
            const loadingIcon = document.getElementById('loadingStoryIcon');
            const btnText = document.getElementById('btnTextStory');
            const sendIcon = document.getElementById('sendStoryIcon');
            
            btn.disabled = true;
            loadingIcon.classList.remove('hidden');
            sendIcon.classList.add('hidden');
            btnText.textContent = 'Paylaşılıyor...';
        });

        /* ----- TOAST BİLDİRİM (PHP'den gelen) ----- */
        <?php if ($postMessage): ?>
            const toast = document.getElementById('npostToast');
            const toastMsg = document.getElementById('npostToastMsg');
            const toastIcon = document.getElementById('npostToastIcon');
            
            const isSuccess = '<?= $postMessage['type'] ?>' === 'success';
            
            toastMsg.innerText = "<?= addslashes($postMessage['text']) ?>";
            toastIcon.innerHTML = isSuccess ? '<i class="fa-solid fa-check-circle text-lg"></i>' : '<i class="fa-solid fa-circle-exclamation text-lg"></i>';
            
            toast.className = `fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-4 rounded-full shadow-[0_10px_40px_rgba(0,0,0,0.15)] font-bold text-[15px] transition-all duration-500 z-[100] flex items-center gap-2.5 ${isSuccess ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'}`;
            
            toast.classList.remove('opacity-0', 'translate-y-10');
            setTimeout(() => toast.classList.add('opacity-0', 'translate-y-10'), 4000);
        <?php endif; ?>
    });
</script>
<?php endif; ?>