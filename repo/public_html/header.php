<header class="bg-gradient-to-r from-primary to-cyan-500 text-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <a href="index.php" class="flex items-center gap-2">
      <img src="/gorsel/logo.png" alt="Logo" class="w-10 h-10 ">
      <span class="text-2xl font-extrabold tracking-tight">Deucritic Sözlük</span>
    </a>

    <nav class="hidden md:flex gap-5 items-center font-medium text-sm">
      <a href="index.php" class="hover:underline hover:text-yellow-100 transition">🏠 Baba Sayfa</a>
      <a href="about.php" class="hover:underline hover:text-yellow-100 transition">🧠 Bizi Kim Yaptı?</a>
      <a href="req.php" class="hover:underline hover:text-yellow-100 transition">🛠️ Geliştirelim</a>
       <a href="https://deucritic.com/sss.php" class="hover:underline hover:text-yellow-100 transition">📌️ SSS</a>
      <?php if ($is_logged_in): ?>
       
        <a href="logout.php" class="bg-white text-primary px-3 py-1 rounded-full font-semibold shadow hover:bg-gray-100 transition">🚪 Kaçış Tuşu</a>
      <?php else: ?>
        <a href="login.php" class="hover:underline hover:text-yellow-100 transition">🔐 Arkadan Giriş</a>
        <a href="register.php" class="bg-white text-primary px-3 py-1 rounded-full font-semibold shadow hover:bg-gray-100 transition">✨ Aramıza Katıl</a>
      <?php endif; ?>
    </nav>

    <div class="md:hidden">
      <button id="mobile-menu-button" class="focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24"
             stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <div id="mobile-menu" class="md:hidden hidden px-4 pb-4 bg-white text-primary shadow-inner">
    <a href="index.php" class="block py-2 font-medium hover:text-cyan-600">🏠 Baba Sayfa</a>
    <a href="about.php" class="block py-2 font-medium hover:text-cyan-600">🧠 Bizi Kim Yaptı?</a>
    <a href="req.php" class="block py-2 font-medium hover:text-cyan-600">🛠️ Geliştirelim</a>
     <a href="https://deucritic.com/sss.php" class="block py-2 font-medium hover:text-cyan-600">📌️ SSS</a>

    <?php if ($is_logged_in): ?>
    
      <a href="logout.php" class="block py-2 font-medium bg-primary text-white rounded text-center">🚪 Kaçış Tuşu</a>
    <?php else: ?>
      <a href="login.php" class="block py-2 font-medium hover:text-cyan-600">🔐 Arkadan Giriş</a>
      <a href="register.php" class="block py-2 font-medium bg-primary text-white rounded text-center">✨ Aramıza Katıl</a>
    <?php endif; ?>
  </div>
</header>

<script>
  document.getElementById('mobile-menu-button')?.addEventListener('click', () => {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
  });
</script>
