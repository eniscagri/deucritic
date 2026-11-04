<form 
  action="actions/upload_story.php" 
  method="POST" 
  enctype="multipart/form-data" 
  class="dc-story-upload bg-white/80 backdrop-blur-xl p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 space-y-6 max-w-md mx-auto mt-6 transition-all duration-300 relative"
  id="storyUploadForm"
>
  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

  <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
    <div class="bg-gradient-to-br from-yellow-100 to-amber-50 p-2 rounded-2xl">
      <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16.42 7.95C18.86 10.39 18.86 14.35 16.42 16.79C13.98 19.23 10.02 19.23 7.58 16.79C5.14 14.35 5.14 10.39 7.58 7.95C10.02 5.51 13.98 5.51 16.42 7.95Z" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
      </svg>
    </div>
    <div>
      <h2 class="text-xl font-bold text-gray-800 tracking-tight">Hikaye Paylaş</h2>
      <p class="text-xs text-gray-500 font-medium mt-0.5">Takipçilerinle anını paylaş</p>
    </div>
  </div>

  <div>
    <div 
      id="dropArea"
      class="dc-story-drop group relative mt-2 border-2 border-dashed border-gray-200 hover:border-blue-400 hover:bg-blue-50/30 rounded-2xl p-4 text-center transition-all duration-300 cursor-pointer min-h-[180px] flex flex-col items-center justify-center overflow-hidden bg-gray-50/50"
    >
      <input 
        type="file" 
        name="story_media" 
        id="storyMedia" 
        accept="image/*,video/*" 
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
      >
      
      <div id="defaultState" class="flex flex-col items-center gap-3 transition-opacity duration-300">
        <div class="bg-white p-3 rounded-full shadow-sm border border-gray-100 group-hover:scale-110 transition-transform duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-700">Tıkla veya Sürükle</p>
          <p class="text-xs text-gray-400 mt-1">Fotoğraf veya Video (Max: 10MB)</p>
        </div>
      </div>

      <div id="previewContainer" class="hidden w-full h-full absolute inset-0 bg-black z-20 flex items-center justify-center">
        </div>
    </div>
  </div>

  <div class="relative">
  <textarea 
      name="text_content" 
      maxlength="300" 
      rows="2"
      placeholder="Bu anı nasıl anlatırsın? ✨" 
      class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all resize-none shadow-sm"
      id="storyText"
    ></textarea>
    <div class="absolute bottom-3 right-3 bg-white/80 backdrop-blur px-2 py-0.5 rounded-md">
      <p class="text-[10px] font-bold text-gray-400" id="charCount">0/300</p>
    </div>
  </div>

  <div class="flex items-center justify-between pt-2">
    <button type="button" id="clearBtn" class="hidden text-sm font-medium text-red-500 hover:text-red-600 transition-colors px-2 py-1">
      İptal Et
    </button>
    <div class="flex-1"></div> <button 
      type="submit" 
      id="submitBtn"
      class="bg-gray-900 text-white hover:bg-black hover:shadow-lg hover:-translate-y-0.5 px-6 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden animate-spin" id="loadingIcon" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
      </svg>
      <span id="submitText">Hikayeyi Paylaş</span>
    </button>
  </div>
</form>

<script>
const mediaInput = document.getElementById('storyMedia');
const previewContainer = document.getElementById('previewContainer');
const defaultState = document.getElementById('defaultState');
const clearBtn = document.getElementById('clearBtn');
const form = document.getElementById('storyUploadForm');
const submitBtn = document.getElementById('submitBtn');
const loadingIcon = document.getElementById('loadingIcon');
const submitText = document.getElementById('submitText');
const dropArea = document.getElementById('dropArea');
const storyText = document.getElementById('storyText');
const charCount = document.getElementById('charCount');

const MAX_SIZE_MB = 10;

// Karakter Sayacı
storyText.addEventListener('input', () => {
  charCount.textContent = `${storyText.value.length}/300`;
  charCount.classList.toggle('text-red-400', storyText.value.length >= 280);
});

// Sürükle Bırak Efektleri
['dragenter', 'dragover'].forEach(eventName => {
  dropArea.addEventListener(eventName, e => {
    e.preventDefault();
    e.stopPropagation();
    dropArea.classList.add('border-blue-500', 'bg-blue-50/50');
  }, false);
});

['dragleave', 'drop'].forEach(eventName => {
  dropArea.addEventListener(eventName, e => {
    e.preventDefault();
    e.stopPropagation();
    dropArea.classList.remove('border-blue-500', 'bg-blue-50/50');
  }, false);
});

dropArea.addEventListener('drop', e => {
  const files = e.dataTransfer.files;
  if(files.length > 0) {
    mediaInput.files = files;
    mediaInput.dispatchEvent(new Event('change'));
  }
});

// Medya İptal Etme İşlevi
const resetMedia = () => {
  mediaInput.value = '';
  previewContainer.innerHTML = '';
  previewContainer.classList.add('hidden');
  defaultState.classList.remove('hidden');
  clearBtn.classList.add('hidden');
};

clearBtn.addEventListener('click', resetMedia);

// Medya Yükleme ve Önizleme
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

  // Arayüzü Önizleme Moduna Al
  defaultState.classList.add('hidden');
  previewContainer.classList.remove('hidden');
  previewContainer.innerHTML = '';
  clearBtn.classList.remove('hidden');

  const mime = file.type;

  if (mime.startsWith('image/')) {
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.className = 'w-full h-full object-cover opacity-90';
    previewContainer.appendChild(img);
  } else if (mime.startsWith('video/')) {
    const video = document.createElement('video');
    video.src = URL.createObjectURL(file);
    video.controls = false;
    video.autoplay = true;
    video.loop = true;
    video.muted = true;
    video.className = 'w-full h-full object-cover opacity-90';
    previewContainer.appendChild(video);
  } else {
    alert('Desteklenmeyen dosya türü.');
    resetMedia();
  }
});

// Form Gönderimi Simülasyonu / Animasyonu
form.addEventListener('submit', () => {
  submitBtn.disabled = true;
  loadingIcon.classList.remove('hidden');
  submitText.textContent = 'Paylaşılıyor...';
});
</script>
