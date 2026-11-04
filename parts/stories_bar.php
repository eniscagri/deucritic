<?php
include 'includes/config.php';

$user_id = $_SESSION['user_id'] ?? null;
$stories = [];

if ($user_id) {
    // İzlenme sayılarını ve verilerini de çekmek için sorguyu güncelleyebilir veya API tarafında (fetch_stories.php) halledebilirsiniz.
    $stmt = $pdo->prepare("
        SELECT s.*, u.username, u.profile_photo, u.id AS user_id
        FROM stories s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN followers f ON f.followed_id = u.id AND f.follower_id = :uid
        WHERE s.expires_at > NOW() AND (s.user_id = :uid OR f.follower_id = :uid)
        ORDER BY s.created_at DESC
    ");
    $stmt->execute(['uid' => $user_id]);
    $allStories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Her kullanıcı için en son hikaye (Ana ekranda göstermek için)
    $byUser = [];
    foreach ($allStories as $s) {
        if (!isset($byUser[$s['user_id']]) || strtotime($s['created_at']) > strtotime($byUser[$s['user_id']]['created_at'])) {
            $byUser[$s['user_id']] = $s;
        }
    }
    $stories = array_values($byUser);
}

// Güvenlik fonksiyonu
if (!function_exists('e')) {
    function e($string) { return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8'); }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Hikayeler</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link rel="stylesheet" href="/css/brand.css">

<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: { primary: '#4f46e5', secondary: '#6366f1' }
            }
        }
    }
</script>

<style>
/* Akıcı Kaydırma Çubuğu */
.story-scroll-container { scroll-snap-type: x mandatory; scroll-behavior: smooth; }
.story-item { scroll-snap-align: start; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Animasyonlar */
.fade-in { animation: fadeIn 0.4s ease-out forwards; }
.scale-up { animation: scaleUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
@keyframes scaleUp { from {opacity:0; transform:scale(0.95);} to {opacity:1; transform:scale(1);} }

/* Hikaye Halkası Gradienti */
.story-ring { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }

body, html { height: 100%; overflow-x: hidden; background-color: #f8fafc; }
</style>
</head>
<body class="font-sans text-slate-900 bg-slate-50 dc-stories-page">

<div class="py-5 px-4 bg-white/80 backdrop-blur-xl shadow-[0_4px_40px_rgba(0,0,0,0.03)] border-b border-slate-100">
  <div class="flex space-x-4 overflow-x-auto no-scrollbar pb-2 story-scroll-container">
    
    <?php if($user_id): ?>
    <div class="flex-shrink-0 text-center relative group cursor-pointer story-item">
      <a href="#" class="block">
        <div class="relative w-[72px] h-[72px] rounded-full p-[2px] border-2 border-dashed border-slate-300 group-hover:border-primary transition-colors duration-300 mx-auto">
          <div class="w-full h-full rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-primary transition-colors relative overflow-hidden shadow-inner">
             <i class="fa-solid fa-plus text-2xl group-hover:scale-110 transition-transform"></i>
          </div>
        </div>
        <span class="text-[13px] mt-2 block font-bold text-slate-600 group-hover:text-primary transition-colors">Hikayen</span>
      </a>
    </div>
    <?php endif; ?>

    <?php foreach ($stories as $s): 
        $photo = (!empty($s['profile_photo']) && file_exists(__DIR__.'/../uploads/profile_photos/'.basename($s['profile_photo'])))
                 ? '/uploads/profile_photos/'.basename($s['profile_photo'])
                 : '/uploads/default-avatar.png';
    ?>
    <div class="flex-shrink-0 text-center story-item">
      <a href="#" class="story-trigger block group" data-user="<?= e($s['user_id']) ?>">
        <div class="relative w-[72px] h-[72px] rounded-full p-[3px] story-ring group-hover:scale-105 transition-transform duration-300 overflow-hidden shadow-md mx-auto">
          <img src="<?= e($photo) ?>" class="w-full h-full object-cover rounded-full border-[3px] border-white">
        </div>
        <span class="text-[13px] mt-2 block truncate w-[76px] font-bold text-slate-700"><?= e($s['username']) ?></span>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div id="storyModal" class="fixed inset-0 z-[9999] hidden bg-black/95 backdrop-blur-2xl flex items-center justify-center transition-opacity duration-300">
  <div class="relative w-full h-full max-w-md mx-auto bg-black overflow-hidden shadow-2xl scale-up" id="storyWrapper">
    
    <div id="storyProgressContainer" class="absolute top-4 left-3 right-3 flex gap-1.5 z-50 drop-shadow-md transition-opacity duration-300"></div>

    <div class="absolute top-0 left-0 right-0 flex items-center justify-between px-4 z-40 bg-gradient-to-b from-black/80 via-black/40 to-transparent pb-10 pt-8 transition-opacity duration-300" id="storyHeaderInfo">
      <div class="flex items-center gap-3 bg-black/20 backdrop-blur-md pl-1.5 pr-4 py-1.5 rounded-full border border-white/10">
        <img id="storyUserPhoto" src="" class="w-8 h-8 rounded-full border border-white/20 object-cover">
        <div class="flex flex-col leading-tight">
          <span id="storyUsername" class="font-extrabold text-white text-[13px] tracking-tight"></span>
          <span id="storyTime" class="text-[11px] text-white/70 font-semibold"></span>
        </div>
      </div>
      
      <div class="flex gap-2">
        <button id="deleteStoryBtn" class="w-9 h-9 flex items-center justify-center rounded-full bg-black/20 backdrop-blur-md text-white/80 hover:bg-red-500 hover:text-white hidden transition-all border border-white/10">
          <i class="fa-solid fa-trash text-sm"></i>
        </button>
        <button id="storyClose" class="w-9 h-9 flex items-center justify-center rounded-full bg-black/20 backdrop-blur-md text-white/80 hover:bg-white/30 hover:text-white transition-all border border-white/10">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
    </div>

    <div id="storyContainer" class="w-full h-full relative flex flex-col justify-center bg-zinc-900">
      <div id="storyContent" class="w-full h-full flex items-center justify-center relative"></div>
      
      <div class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-black/80 to-transparent z-30 pointer-events-none transition-opacity duration-300" id="storyBottomGradient"></div>

      <div id="storyTextContent" class="absolute bottom-20 left-4 right-4 text-center z-40 hidden transition-opacity duration-300">
        <span id="storyTextInner" class="text-white px-4 py-2 drop-shadow-lg text-[15px] font-semibold inline-block max-w-[95%] break-words leading-relaxed"></span>
      </div>

      <div id="viewersBtnWrapper" class="absolute bottom-6 left-4 z-50 hidden transition-opacity duration-300">
        <button id="viewersBtn" class="flex flex-col items-start justify-center group">
            <div class="flex items-center gap-2 text-white drop-shadow-md">
                <i class="fa-solid fa-eye text-lg group-hover:scale-110 transition-transform"></i>
                <span id="viewersCountText" class="text-sm font-bold">0 İzlenme</span>
            </div>
            <i class="fa-solid fa-chevron-up text-[10px] text-white/70 ml-2 mt-1 animate-bounce"></i>
        </button>
      </div>
    </div>

    <div class="absolute inset-0 flex z-30 mt-20 mb-32">
      <div id="navLeft" class="w-1/3 h-full cursor-pointer"></div>
      <div id="navRight" class="w-2/3 h-full cursor-pointer"></div>
    </div>

    <div id="viewersSheet" class="absolute bottom-0 left-0 right-0 bg-white rounded-t-[2rem] transform translate-y-full transition-transform duration-300 z-[60] flex flex-col max-h-[70%] shadow-[0_-10px_40px_rgba(0,0,0,0.3)]">
        <div class="w-full pt-4 pb-2 flex justify-center cursor-pointer" id="closeViewersSheetBtn">
            <div class="w-12 h-1.5 bg-slate-300 rounded-full"></div>
        </div>
        
        <div class="px-6 pb-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-eye text-primary"></i> Görüntüleyenler
            </h3>
            <span id="sheetViewersCount" class="bg-indigo-50 text-primary px-3 py-1 rounded-full text-xs font-bold">0</span>
        </div>

        <div id="viewersListContainer" class="overflow-y-auto p-4 flex-1 space-y-3 no-scrollbar pb-10">
            </div>
    </div>

  </div>
</div>

<script>
const storyModal = document.getElementById('storyModal');
const storyContent = document.getElementById('storyContent');
const progressContainer = document.getElementById('storyProgressContainer');
const storyUserPhoto = document.getElementById('storyUserPhoto');
const storyUsername = document.getElementById('storyUsername');
const storyTime = document.getElementById('storyTime');
const storyTextContent = document.getElementById('storyTextContent');
const storyTextInner = document.getElementById('storyTextInner');
const deleteBtn = document.getElementById('deleteStoryBtn');
const storyHeaderInfo = document.getElementById('storyHeaderInfo');
const storyBottomGradient = document.getElementById('storyBottomGradient');

// Viewers Elementleri
const viewersBtnWrapper = document.getElementById('viewersBtnWrapper');
const viewersBtn = document.getElementById('viewersBtn');
const viewersCountText = document.getElementById('viewersCountText');
const viewersSheet = document.getElementById('viewersSheet');
const closeViewersSheetBtn = document.getElementById('closeViewersSheetBtn');
const viewersListContainer = document.getElementById('viewersListContainer');
const sheetViewersCount = document.getElementById('sheetViewersCount');

const navLeft = document.getElementById('navLeft');
const navRight = document.getElementById('navRight');

let activeStories = [], activeIndex = 0;
let progressTimer = null, startTime = null, remainingTime = 0, duration = 5000;
let isPaused = false;
let currentVideo = null;
const myUserId = <?= $user_id ?: 0 ?>;

// Açma İşlemi
document.querySelectorAll('.story-trigger').forEach(el => {
  el.addEventListener('click', async e => {
    e.preventDefault();
    const uid = el.dataset.user;
    
    try {
        const res = await fetch('actions/fetch_stories.php?user_id='+uid);
        const data = await res.json();
        if(!data || !data.stories || !data.stories.length) return;
        
        activeStories = data.stories;
        activeIndex = 0;
        
        setupProgressBars();
        storyModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; 
        renderStory(activeIndex);
    } catch(err) {
        console.error("Hikayeler yüklenemedi:", err);
    }
  });
});

document.getElementById('storyClose').addEventListener('click', closeStory);

function closeStory() {
  storyModal.classList.add('hidden');
  document.body.style.overflow = ''; 
  closeViewersSheet(); // Varsa paneli kapat
  clearTimer();
  if(currentVideo) { currentVideo.pause(); currentVideo = null; }
  storyContent.innerHTML = '';
}

function setupProgressBars() {
  progressContainer.innerHTML = '';
  activeStories.forEach((_, idx) => {
    const bar = document.createElement('div');
    bar.className = 'h-1 flex-1 bg-white/30 backdrop-blur-sm rounded-full overflow-hidden';
    const fill = document.createElement('div');
    fill.className = 'h-full bg-white w-0 rounded-full shadow-[0_0_8px_rgba(255,255,255,0.8)]';
    fill.id = `progress-fill-${idx}`;
    bar.appendChild(fill);
    progressContainer.appendChild(bar);
  });
}

function renderStory(i) {
  clearTimer();
  isPaused = false;
  currentVideo = null;
  const s = activeStories[i];
  storyContent.innerHTML = '';
  closeViewersSheet(); // Yeni hikayeye geçerken paneli kapa
  
  // Önceki barları %100, sonrakileri %0 yap
  activeStories.forEach((_, idx) => {
    const fill = document.getElementById(`progress-fill-${idx}`);
    if(idx < i) fill.style.width = '100%';
    else fill.style.width = '0%';
  });

  // Açıklama Metni
  if (s.text_content && s.text_content.trim() !== '') {
    storyTextInner.textContent = s.text_content;
    storyTextContent.classList.remove('hidden');
  } else {
    storyTextContent.classList.add('hidden');
  }

  // Medya Yükleme
  if (s.media_path) {
    const ext = s.media_path.split('.').pop().toLowerCase();
    if (['mp4','webm','ogg'].includes(ext)) {
      const v = document.createElement('video');
      v.src = s.media_path; 
      v.autoplay = true; 
      v.muted = false; 
      v.playsInline = true;
      v.className = 'w-full h-full object-contain fade-in'; 
      storyContent.appendChild(v);
      currentVideo = v;
      
      v.onloadedmetadata = () => {
        duration = (v.duration * 1000) || 7000;
        startTimer(duration);
      };
      v.onended = nextStory;
    } else {
      const img = document.createElement('img');
      img.src = s.media_path; 
      img.className = 'w-full h-full object-contain fade-in'; 
      storyContent.appendChild(img);
      duration = 5000;
      startTimer(duration);
    }
  }

  // Meta Bilgileri
  storyUserPhoto.src = s.profile_photo ? '/uploads/profile_photos/'+s.profile_photo : '/uploads/default-avatar.png';
  storyUsername.textContent = s.username;
  storyTime.textContent = timeAgo(s.created_at);

  // KENDİ HİKAYESİ KONTROLÜ
  if (s.user_id == myUserId) {
    deleteBtn.classList.remove('hidden');
    viewersBtnWrapper.classList.remove('hidden');
    
    // İzlenme sayısını yazdır (Backend 'view_count' dönmeli)
    const vCount = s.view_count || (s.viewers ? s.viewers.length : 0);
    viewersCountText.textContent = vCount + ' İzlenme';
    sheetViewersCount.textContent = vCount;

    // İzleyici Paneli İçeriğini Doldur (Backend 'viewers' array'i dönmeli)
    viewersListContainer.innerHTML = '';
    if(s.viewers && s.viewers.length > 0) {
        s.viewers.forEach(viewer => {
            const vPhoto = viewer.profile_photo ? '/uploads/profile_photos/'+viewer.profile_photo : '/uploads/default-avatar.png';
            viewersListContainer.innerHTML += `
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <img src="${vPhoto}" class="w-10 h-10 rounded-full object-cover border border-slate-100">
                    <span class="font-bold text-slate-700 text-sm">${viewer.username}</span>
                </div>
            `;
        });
    } else {
        viewersListContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                <i class="fa-regular fa-eye-slash text-3xl mb-3 opacity-50"></i>
                <p class="text-sm font-semibold">Henüz kimse izlemedi.</p>
            </div>
        `;
    }

    // Silme İşlemi
    deleteBtn.onclick = async () => {
      isPaused = true; 
      if(currentVideo) currentVideo.pause();
      
      if(!confirm("Hikayeyi silmek istediğinizden emin misiniz?")) {
        isPaused = false;
        if(currentVideo) currentVideo.play();
        return;
      }
      
      const res = await fetch('actions/delete_story.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'story_id='+encodeURIComponent(s.id)
      });
      const data = await res.json();
      if(data.success) { location.reload(); } 
      else { alert(data.msg || 'Hata oluştu!'); isPaused = false; if(currentVideo) currentVideo.play(); }
    };

  } else {
    // BAŞKASININ HİKAYESİ (İzleme kaydedilir)
    deleteBtn.classList.add('hidden');
    viewersBtnWrapper.classList.add('hidden');
    
    // İzlenmeyi sunucuya bildir (actions/view_story.php adlı bir dosyanız olmalı)
    fetch('actions/view_story.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'story_id='+encodeURIComponent(s.id)
    }).catch(e => console.log('İzlenme kaydedilemedi'));
  }
}

// İzleyenler Panelini (Bottom Sheet) Aç/Kapat
viewersBtn.addEventListener('click', (e) => {
    e.stopPropagation(); // Hikayenin duraklamasını engellemek için
    isPaused = true;
    if(currentVideo) currentVideo.pause();
    
    viewersSheet.classList.remove('translate-y-full');
    storyHeaderInfo.classList.add('opacity-0');
    progressContainer.classList.add('opacity-0');
    storyTextContent.classList.add('opacity-0');
    viewersBtnWrapper.classList.add('opacity-0');
    storyBottomGradient.classList.add('opacity-0');
});

function closeViewersSheet() {
    viewersSheet.classList.add('translate-y-full');
    if(storyModal.classList.contains('hidden') === false) {
        isPaused = false;
        if(currentVideo) currentVideo.play();
        storyHeaderInfo.classList.remove('opacity-0');
        progressContainer.classList.remove('opacity-0');
        storyTextContent.classList.remove('opacity-0');
        viewersBtnWrapper.classList.remove('opacity-0');
        storyBottomGradient.classList.remove('opacity-0');
    }
}

closeViewersSheetBtn.addEventListener('click', closeViewersSheet);

// Timer İşlemleri
function startTimer(ms) {
  remainingTime = ms;
  startTime = Date.now();
  
  function updateProgress() {
    if (isPaused) {
      startTime = Date.now() - (duration - remainingTime);
    } else {
      const elapsed = Date.now() - startTime;
      remainingTime = duration - elapsed;
      const percent = (elapsed / duration) * 100;
      
      const currentFill = document.getElementById(`progress-fill-${activeIndex}`);
      if (currentFill) currentFill.style.width = `${Math.min(percent, 100)}%`;

      if (remainingTime <= 0) {
        nextStory();
        return;
      }
    }
    progressTimer = requestAnimationFrame(updateProgress);
  }
  progressTimer = requestAnimationFrame(updateProgress);
}

function clearTimer() {
  if (progressTimer) cancelAnimationFrame(progressTimer);
}

// Navigasyon (Sonraki - Önceki)
function nextStory() {
  if (activeIndex < activeStories.length - 1) {
    activeIndex++;
    renderStory(activeIndex);
  } else {
    closeStory();
  }
}

function prevStory() {
  if (activeIndex > 0) {
    activeIndex--;
    renderStory(activeIndex);
  } else {
    renderStory(0);
  }
}

navRight.addEventListener('click', (e) => {
    if(!viewersSheet.classList.contains('translate-y-full')) return closeViewersSheet();
    nextStory();
});

navLeft.addEventListener('click', (e) => {
    if(!viewersSheet.classList.contains('translate-y-full')) return closeViewersSheet();
    prevStory();
});

// Basılı Tutarak Duraklatma (Hold to Pause)
['mousedown', 'touchstart'].forEach(evt => {
  document.getElementById('storyWrapper').addEventListener(evt, (e) => {
    if(e.target.closest('button') || e.target.closest('#viewersSheet')) return; 
    
    isPaused = true;
    if(currentVideo) currentVideo.pause();
    progressContainer.classList.add('opacity-0');
    storyHeaderInfo.classList.add('opacity-0');
    storyTextContent.classList.add('opacity-0');
    viewersBtnWrapper.classList.add('opacity-0');
    storyBottomGradient.classList.add('opacity-0');
  });
});

['mouseup', 'touchend', 'mouseleave'].forEach(evt => {
  document.getElementById('storyWrapper').addEventListener(evt, (e) => {
    if(!viewersSheet.classList.contains('translate-y-full')) return; // Sheet açıksa başlatma
    
    isPaused = false;
    if(currentVideo) currentVideo.play();
    progressContainer.classList.remove('opacity-0');
    storyHeaderInfo.classList.remove('opacity-0');
    storyTextContent.classList.remove('opacity-0');
    viewersBtnWrapper.classList.remove('opacity-0');
    storyBottomGradient.classList.remove('opacity-0');
  });
});

function timeAgo(dateString) {
  const diff = Math.floor((new Date() - new Date(dateString.replace(/-/g, "/"))) / 1000); 
  if (diff < 60) return diff + 's';
  if (diff < 3600) return Math.floor(diff / 60) + 'd';
  if (diff < 86400) return Math.floor(diff / 3600) + 's';
  return Math.floor(diff / 86400) + 'g';
}
</script>
</body>
</html>
