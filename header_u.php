<header class="bg-white/80 backdrop-blur-xl border-b border-gray-100 sticky top-0 z-50 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] transition-all duration-300">
  <div class="max-w-7xl mx-auto px-4 h-16 md:h-20 flex justify-between items-center">
    
    <a href="index.php" class="flex items-center gap-3 group shrink-0">
      <div class="relative">
        <img src="/gorsel/deucritic-mark.svg" alt="Deucritic" class="w-10 h-10 md:w-12 md:h-12 drop-shadow-sm transition-transform duration-500 group-hover:rotate-[360deg]">
        <div class="absolute inset-0 bg-primary/20 blur-xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
      </div>
      <span class="text-xl md:text-2xl font-[900] tracking-tighter text-gray-900 leading-none">
        Deucritic <span class="text-[#e9c65a] drop-shadow-sm transition-colors group-hover:text-primary">Sözlük</span>
      </span>
    </a>

    <nav class="hidden lg:flex items-center gap-1">
      <div class="flex items-center mr-4 bg-gray-50/50 p-1 rounded-2xl border border-gray-100/50">
        <a href="index.php" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:text-primary hover:bg-white transition-all flex items-center gap-2">
          <span>🏠</span> Baba Sayfa
        </a>
        <a href="about.php" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:text-primary hover:bg-white transition-all flex items-center gap-2">
          <span>🧠</span> Biz Kimiz?
        </a>
        <a href="req.php" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:text-primary hover:bg-white transition-all flex items-center gap-2">
          <span>🛠️</span> Geliştirelim
        </a>
        <a href="https://deucritic.com/sss.php" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-500 hover:text-primary hover:bg-white transition-all flex items-center gap-2">
          <span>📌</span> SSS
        </a>
      </div>

      <div class="flex items-center gap-3 ml-2 border-l border-gray-200 pl-4">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="logout.php" class="px-5 py-2.5 bg-red-50 text-red-600 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-sm border border-red-100">
            🚪 Kaçış Tuşu
          </a>
        <?php else: ?>
          <a href="login.php" class="text-gray-500 hover:text-primary text-xs font-black uppercase tracking-widest px-4 transition-colors">
            🔐 Giriş
          </a>
          <a href="register.php" class="px-6 py-2.5 bg-primary text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:bg-cyan-600 hover:shadow-cyan-600/30 transition-all active:scale-95 border border-white/20">
            ✨ Aramıza Katıl
          </a>
        <?php endif; ?>
      </div>
      <form action="search.php" method="get" role="search" class="ml-3 flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-2 py-1 shadow-sm focus-within:border-[#b84834] focus-within:ring-2 focus-within:ring-[#b84834]/10">
        <label class="sr-only" for="header-u-search">Deucritic'te ara</label>
        <input id="header-u-search" type="search" name="q" placeholder="Kampüste ara" class="w-28 xl:w-40 border-0 bg-transparent p-1.5 text-sm font-medium text-gray-700 outline-none placeholder:text-gray-400 focus:ring-0" autocomplete="off">
        <button type="submit" aria-label="Ara" class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#1d352f] text-white transition-colors hover:bg-[#b84834] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b84834]"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
      </form>
    </nav>

    <div class="lg:hidden flex items-center gap-3">
        <a href="search.php" aria-label="Ara" title="Ara" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-[#1d352f] hover:bg-[#b84834] hover:text-white"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></a>
        <?php if (empty($_SESSION['user_id'])): ?>
            <a href="register.php" class="text-[10px] font-black bg-primary text-white px-3 py-2 rounded-xl uppercase tracking-tighter shadow-md">✨ Katıl</a>
        <?php endif; ?>
        <button id="mobile-menu-button" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-gray-50 border border-gray-100 text-gray-900 active:scale-90 transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"/>
          </svg>
        </button>
    </div>
  </div>

  <div id="mobile-menu" class="lg:hidden hidden overflow-hidden bg-white border-t border-gray-50 animate-fadeIn">
    <div class="px-4 py-6 space-y-3">
      <a href="index.php" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 text-gray-900 font-bold hover:bg-primary/10 hover:text-primary transition-all">
        <span class="text-xl">🏠</span> Baba Sayfa
      </a>
      <a href="about.php" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 text-gray-900 font-bold hover:bg-primary/10 hover:text-primary transition-all">
        <span class="text-xl">🧠</span> Biz Kimiz?
      </a>
      <a href="req.php" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 text-gray-900 font-bold hover:bg-primary/10 hover:text-primary transition-all">
        <span class="text-xl">🛠️</span> Geliştirelim
      </a>
      
      <div class="pt-4 grid grid-cols-2 gap-3">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="logout.php" class="col-span-2 py-4 bg-red-500 text-white font-black text-center rounded-2xl shadow-lg">🚪 KAÇIŞ TUŞU</a>
        <?php else: ?>
          <a href="login.php" class="py-4 bg-gray-100 text-gray-900 font-black text-center rounded-2xl">🔐 GİRİŞ</a>
          <a href="register.php" class="py-4 bg-primary text-white font-black text-center rounded-2xl shadow-lg shadow-primary/20">✨ KATIL</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>
