<?php if ($is_logged_in): ?>
  <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
      $title = trim($_POST['title']);
      $description = trim($_POST['description'] ?? '');
      $anonymous = isset($_POST['anonymous']) ? 1 : 0;

      if ($title !== '') {
        $stmt = $pdo->prepare("
          INSERT INTO topics (title, description, anonymous, created_by, created_at)
          VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $description, $anonymous, $user_id]);
       
      }
    }
  ?>

  <section class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 transition hover:shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-primary flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Yeni Onep Oluştur
      </h3>

      <button id="info-btn" class="text-gray-400 hover:text-primary focus:outline-none transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"/>
        </svg>
      </button>
    </div>

    <div id="info-tooltip" class="hidden bg-gray-50 border border-gray-200 p-3 rounded-lg text-sm text-gray-600 mb-3">
      Eğer onep paylaşamıyorsan mobil tarayıcında <span class="font-medium text-gray-800">“Masaüstü Görünümü”</span> seçeneğini aktif et.
    </div>

    <form method="POST" class="space-y-4">
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Onep Başlığı</label>
        <input 
          type="text"
          id="title"
          name="title"
          maxlength="100"
          placeholder="Mesela: Kampüs otomatlarındaki fiyatlar..."
          class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary transition placeholder-gray-400"
          required>
      </div>

      <div>
<label for="description" class="block text-sm font-medium text-gray-700 mb-1">
              Onep hakkında ne düşünüyorsun <span class="text-gray-400">(isteğe bağlı)</span>
            </label>
            <textarea id="description" name="description" maxlength="1000" rows="4" placeholder="<?php
              $desc_placeholder = "Bi iki kelam çakabilirsin buraya, kafana göre...";
              $last_desc_stmt = $pdo->query("SELECT description FROM topics WHERE description IS NOT NULL AND description != '' ORDER BY created_at DESC LIMIT 3");
              $last_desc = $last_desc_stmt->fetch(PDO::FETCH_ASSOC);
              if ($last_desc && !empty($last_desc['description'])) {
                $desc_placeholder = mb_strimwidth(strip_tags($last_desc['description']), 0, 80, '...');
              }
              echo htmlspecialchars($desc_placeholder);
            ?>" 
            class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-primary transition resize-y"
            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>


<script>
  const textarea = document.getElementById('description');
  const counter = document.getElementById('desc-count');

  counter.textContent = `${textarea.value.length}/500`;

  textarea.addEventListener('input', () => {
    counter.textContent = `${textarea.value.length}/500`;
  });
</script>


      <label class="flex items-center text-sm text-gray-700">
        <input type="checkbox" name="anonymous" class="mr-2 rounded border-gray-300 focus:ring-primary">
        <span>AnY (Anonim) olarak paylaş</span>
      </label>

      <button 
        type="submit" 
        class="w-full bg-secondary text-gray-900 py-2.5 rounded-lg font-semibold hover:bg-yellow-300 transition">
        Yolla Gitsin
      </button>
    </form>
  </section>

  <script>
    const infoBtn = document.getElementById('info-btn');
    const tooltip = document.getElementById('info-tooltip');
    infoBtn.addEventListener('click', () => tooltip.classList.toggle('hidden'));

    const desc = document.getElementById('description');
    const counter = document.getElementById('desc-count');
    desc.addEventListener('input', () => {
      counter.textContent = `${desc.value.length}/500`;
    });
  </script>
<?php endif; ?>
