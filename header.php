
<style>

    #pwa-install-banner {
        visibility: hidden;
        opacity: 0;
        background: rgba(15, 23, 42, 0.9); /* Modern Slate-900 */
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        color: #ffffff;
        padding: 24px 24px calc(24px + env(safe-area-inset-bottom));
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translate(-50%, 100%);
        width: 100%;
        max-width: 500px;
        border-radius: 32px 32px 0 0;
        z-index: 999999;
        box-shadow: 0 -15px 50px rgba(0, 0, 0, 0.4);
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    #pwa-install-banner.show {
        visibility: visible;
        opacity: 1;
        transform: translate(-50%, 0);
    }

    @media (min-width: 640px) {
        #pwa-install-banner {
            bottom: 30px;
            border-radius: 28px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
    }

    .pwa-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .pwa-text-group { display: flex; align-items: center; gap: 16px; flex: 1; }

    .pwa-logo-wrapper { position: relative; flex-shrink: 0; }
    .pwa-logo {
        width: 60px; height: 60px; border-radius: 16px;
        border: 2px solid rgba(68, 181, 213, 0.4); /* Proje ana rengine uyarlandı */
        box-shadow: 0 8px 20px rgba(0,0,0,0.3); position: relative; z-index: 2;
    }
    .logo-glow {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 50px; height: 50px; background: #b84834; filter: blur(25px); opacity: 0.5; z-index: 1;
    }

    .pwa-info h4 { margin: 0; font-size: 18px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
    .pwa-badge {
        background: linear-gradient(90deg, #b84834, #d8785d); color: #1d352f;
        font-size: 10px; padding: 2px 8px; border-radius: 6px; text-transform: uppercase;
        font-weight: 900; margin-left: 8px; vertical-align: middle;
    }
    .pwa-info p { margin: 4px 0 0 0; font-size: 14px; color: #94a3b8; line-height: 1.5; font-weight: 500; }

    .pwa-actions { display: flex; align-items: center; gap: 12px; width: 100%; margin-top: 24px; }

    #btn-pwa-install {
        background: #ffffff; color: #1d352f; border: none; padding: 16px 28px;
        border-radius: 18px; font-weight: 800; cursor: pointer; font-size: 15px;
        flex: 1; box-shadow: 0 10px 25px rgba(255,255,255,0.15);
        transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px;
        position: relative; overflow: hidden;
    }

    #btn-pwa-install::after {
        content: ""; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.6), transparent);
        transform: rotate(45deg); animation: shine 3s infinite;
    }

    @keyframes shine { 0% { left: -100%; } 100% { left: 100%; } }

    #btn-pwa-close {
        background: rgba(255,255,255,0.08); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);
        width: 56px; height: 56px; border-radius: 18px; font-size: 22px;
        display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;
    }

    /* iOS Görsel Rehber */
    .ios-guide {
        background: rgba(255, 255, 255, 0.05); padding: 18px; border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.08); margin-top: 20px;
    }
    .ios-guide-item { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .ios-guide-item:last-child { margin-bottom: 0; }
    .ios-guide-icon {
        width: 32px; height: 32px; background: rgba(184, 72, 52, .16);
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        color: #b84834; font-weight: 800; font-size: 14px; flex-shrink: 0;
    }
    .ios-guide-text { font-size: 14px; color: #e2e8f0; font-weight: 500; line-height: 1.4; }
    .ios-icon-box { background: #334155; padding: 4px; border-radius: 6px; display: inline-flex; vertical-align: middle; }
</style>

<div id="pwa-install-banner">
    <div class="pwa-content">
        <div class="pwa-header">
            <div class="pwa-text-group">
                <div class="pwa-logo-wrapper">
                    <img src="/gorsel/deucritic-mark.svg" alt="Deucritic" class="pwa-logo">
                    <div class="logo-glow"></div>
                </div>
                <div class="pwa-info">
                    <h4>Deucritic.com <span class="pwa-badge">Tamamen  Ücretsiz</span></h4>
                    <p id="pwa-desc">Resmi uygulamayı yükleyerek en hızlı deneyime ve anlık bildirimlere sahip ol.</p>
                </div>
            </div>
        </div>

        <div class="pwa-actions" id="pwa-action-buttons">
            <button id="btn-pwa-close" aria-label="Kapat"><i class="fa-solid fa-xmark"></i></button>
            <button id="btn-pwa-install">HEMEN YÜKLE</button>
        </div>

        <div id="ios-guide-container" class="ios-guide" style="display: none;">
            <div class="ios-guide-item">
                <div class="ios-guide-icon">1</div>
                <div class="ios-guide-text">Tarayıcının altındaki <span class="ios-icon-box"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#b84834" stroke-width="2.5"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg></span> <b>Paylaş</b> butonuna dokun.</div>
            </div>
            <div class="ios-guide-item">
                <div class="ios-guide-icon">2</div>
                <div class="ios-guide-text">Listeyi yukarı kaydırıp <b>'Ana Ekrana Ekle'</b> seçeneğine tıkla.</div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let deferredPrompt;
    const pwaBanner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('btn-pwa-install');
    const closeBtn = document.getElementById('btn-pwa-close');
    const actionButtons = document.getElementById('pwa-action-buttons');
    const iosGuide = document.getElementById('ios-guide-container');

    const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    const isInStandaloneMode = ('standalone' in window.navigator) && (window.navigator.standalone);

    function showBanner() {
        if (localStorage.getItem('pwa_dismissed')) return;
        pwaBanner.style.display = 'block';
        setTimeout(() => pwaBanner.classList.add('show'), 100);
    }

    function hideBanner() {
        pwaBanner.classList.remove('show');
        setTimeout(() => pwaBanner.style.display = 'none', 600);
        localStorage.setItem('pwa_dismissed', 'true');
    }

    // 1. iOS Mantığı
    if (isIos && !isInStandaloneMode) {
        actionButtons.style.display = 'none'; // Standart butonları gizle
        iosGuide.style.display = 'block';     // Rehberi göster

        // iOS'ta sağ üst köşeye şık bir kapatma ikonu ekle
        const iosClose = document.createElement('button');
        iosClose.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        iosClose.style.cssText = 'position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.1); border:none; width:36px; height:36px; border-radius:12px; color:#94a3b8; cursor:pointer;';
        iosClose.onclick = hideBanner;
        pwaBanner.appendChild(iosClose);

        setTimeout(showBanner, 3000);
    }

    // 2. Android & Desktop Mantığı
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        setTimeout(showBanner, 2000);
    });

    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') hideBanner();
        deferredPrompt = null;
    });

    closeBtn.addEventListener('click', hideBanner);
})();
</script>

<?php
include_once 'includes/config.php';

$active = $active ?? '';
$user_id = $_SESSION['user_id'] ?? null;
$u = null;

if($user_id){
    $stmt = $pdo->prepare("SELECT username, profile_photo, isverified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if($u){
        $username = htmlspecialchars($u['username']);
        $photo = !empty($u['profile_photo'])
            ? htmlspecialchars($u['profile_photo'])
            : 'uploads/default-avatar.png';
        $isverified = (bool)$u['isverified'];
    }
}
?>

<header
    x-data="{
        profileOpen: false,
        scrolled: false
    }"
    @scroll.window="scrolled = window.pageYOffset > 10"
    :class="scrolled ? 'bg-white/85 backdrop-blur-xl border-b border-slate-200/50 shadow-sm py-2' : 'bg-transparent border-b border-transparent py-4'"
    class="dc-site-header sticky top-0 z-[1000] w-full transition-all duration-300 font-sans"
>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <div class="dc-site-header__inner max-w-6xl mx-auto px-4 md:px-6 flex justify-between items-center">

        <a href="index.php" class="flex items-center gap-3 group select-none">
            <img src="/gorsel/deucritic-mark.svg" alt="Deucritic" class="w-10 h-10 md:w-11 md:h-11 transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110 drop-shadow-sm">
            <div class="flex flex-col justify-center">
                <span class="text-xl md:text-2xl dc-wordmark font-black text-[#1d352f] tracking-tight leading-none">Deucritic</span>
                <span class="text-[9px] font-black text-[#b84834] tracking-[0.25em] uppercase leading-none mt-0.5">Sözlük</span>
            </div>
        </a>

        <nav class="hidden lg:flex items-center p-1.5 bg-slate-100/50 backdrop-blur-md rounded-2xl border border-slate-200/50">
            <a href="index.php" class="nav-pill <?= $active=='home' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-white/50' ?>">Ana Sayfa</a>
            <a href="tanitim.php" class="nav-pill <?= $active=='promo' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-white/50' ?>">Tanıtım</a>
            <a href="about.php" class="nav-pill <?= $active=='about' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-white/50' ?>">Biz Kimiz?</a>
            <a href="req.php" class="nav-pill <?= $active=='req' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-white/50' ?>">Geliştirin</a>
            <a href="sss.php" class="nav-pill <?= $active=='sss' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-white/50' ?>">S.S.S</a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">

            <button id="header-search-button" type="button" aria-label="Aramayı aç" aria-controls="header-search-panel" aria-expanded="false"
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500 border border-slate-200 hover:bg-white hover:text-[#b84834] hover:shadow-sm transition-all focus:outline-none">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            </button>

            <?php if($u): ?>
                <div class="relative" @click.away="profileOpen = false">
                    <button @click="profileOpen = !profileOpen"
                        class="flex items-center gap-2 p-1.5 pr-4 bg-white border border-slate-200 rounded-[1.25rem] hover:shadow-md hover:border-slate-300 transition-all focus:outline-none">

                        <div class="w-8 h-8 rounded-full overflow-hidden relative border border-slate-100 flex-shrink-0">
                            <img src="<?= $photo ?>" class="w-full h-full object-cover">
                            <?php if($isverified): ?>
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-blue-500 border-2 border-white rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-check text-[6px] text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="hidden md:block text-sm font-extrabold text-slate-700 truncate max-w-[100px]">@<?= $username ?></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 hidden md:block transition-transform" :class="profileOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="profileOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-[1100]" x-cloak>

                        <div class="px-4 py-3 bg-slate-50 rounded-xl mb-2 flex items-center gap-3 border border-slate-100/50">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Oturum Açık</span>
                                <span class="font-black text-slate-800 truncate text-sm">@<?= $username ?></span>
                            </div>
                        </div>
<a href="profile.php" class="group flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 active:bg-slate-100 transition-colors">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white group-hover:scale-105 transition-all duration-300 shadow-sm group-hover:shadow-blue-200">
            <i class="fa-solid fa-user text-sm"></i>
        </div>
        <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Profilim</span>
    </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"></i>
        </a>

        <a href="dm.php" class="group flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 active:bg-slate-100 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white group-hover:scale-105 transition-all duration-300 shadow-sm group-hover:shadow-emerald-200">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </div>
                <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Mesajlar</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-sm">3</span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"></i>
            </div>
        </a>


                        <div class="h-px bg-slate-100 my-2 mx-2"></div>

                        <a href="logout.php" class="menu-item group !text-rose-600 hover:!bg-rose-50"><div class="menu-icon bg-rose-50 text-rose-500 group-hover:bg-rose-500 group-hover:text-white"><i class="fa-solid fa-power-off"></i></div> Çıkış Yap</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="login.php" class="hidden sm:flex items-center text-sm font-extrabold text-slate-500 hover:text-slate-800 transition-colors px-3">Giriş</a>
                <a href="register.php" class="hidden sm:flex px-5 py-2.5 bg-[#1d352f] hover:bg-[#b84834] text-white rounded-xl text-sm font-extrabold shadow-md hover:shadow-lg transition-all active:scale-95 items-center gap-2">
                    Katıl <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            <?php endif; ?>

            <button id="mobile-menu-button" type="button" aria-label="Menüyü aç" aria-controls="header-mobile-menu" aria-expanded="false" class="lg:hidden w-10 h-10 flex items-center justify-center bg-[#1d352f] text-white rounded-xl active:scale-95 transition-transform">
                <i class="fa-solid fa-bars-staggered text-lg" aria-hidden="true"></i>
            </button>

        </div>
    </div>

    <div id="header-search-panel" class="dc-search-panel absolute top-full left-0 w-full bg-white/95 backdrop-blur-2xl border-b border-slate-200 shadow-[0_20px_40px_rgba(0,0,0,0.06)] p-6 md:p-10 z-[999]" aria-hidden="true">

        <div class="max-w-3xl mx-auto">
            <form action="search.php" method="GET" class="relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 flex items-center text-slate-400 group-focus-within:text-[#b84834] transition-colors pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-xl"></i>
                </div>

                <input id="header-search-input" name="q" type="search" maxlength="100" autocomplete="off" placeholder="Başlık veya kullanıcı ara"
                       class="w-full pl-16 pr-28 py-5 rounded-[2rem] bg-slate-50 border-2 border-slate-100 focus:border-[#b84834]/30 focus:bg-white focus:shadow-xl focus:shadow-[#b84834]/5 outline-none transition-all font-bold text-slate-800 placeholder:text-slate-400 text-lg"
                       />

                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 px-6 py-3 bg-[#1d352f] text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-[#b84834] transition-all shadow-md active:scale-95">
                    Ara
                </button>
            </form>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mr-2"><i class="fa-solid fa-bolt text-secondary mr-1"></i> Trend Başlıklar:</span>
                <a href="search.php?q=yemekhane" class="tag-pill"><i class="fa-solid fa-utensils text-[#b84834]"></i> Yemekhane</a>
                <a href="search.php?q=vizeler" class="tag-pill"><i class="fa-solid fa-fire text-rose-500"></i> Vizeler</a>
                <a href="search.php?q=itiraf" class="tag-pill"><i class="fa-solid fa-mask text-purple-500"></i> İtiraf</a>
            </div>
        </div>
    </div>

    <div id="header-mobile-menu" class="dc-mobile-menu fixed inset-0 bg-white/95 backdrop-blur-2xl z-[1001] lg:hidden flex flex-col p-6 pt-24 overflow-y-auto" aria-hidden="true">

        <div class="absolute top-6 left-6 flex items-center gap-4">
            <button type="button" data-mobile-menu-close aria-label="Menüyü kapat" class="w-12 h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 active:scale-90 transition-transform">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Kapat</span>
        </div>

        <div class="space-y-6 mt-6 flex-1">
            <a href="index.php" class="mobile-nav-link <?= $active=='home' ? 'text-dark' : 'text-slate-300' ?>">Ana Sayfa</a>
            <a href="tanitim.php" class="mobile-nav-link <?= $active=='promo' ? 'text-dark' : 'text-slate-300' ?>">Tanıtım</a>
            <a href="about.php" class="mobile-nav-link <?= $active=='about' ? 'text-dark' : 'text-slate-300' ?>">Biz Kimiz?</a>
            <a href="req.php" class="mobile-nav-link <?= $active=='req' ? 'text-dark' : 'text-slate-300' ?>">Geliştirin</a>
            <a href="sss.php" class="mobile-nav-link <?= $active=='sss' ? 'text-dark' : 'text-slate-300' ?>">S.S.S</a>
        </div>

        <div class="mt-auto space-y-4 pb-8">
            <?php if(!$user_id): ?>
                <div class="grid grid-cols-2 gap-4">
                    <a href="login.php" class="py-5 bg-slate-100 hover:bg-slate-200 text-center rounded-3xl font-black text-slate-700 text-sm uppercase tracking-widest transition-colors active:scale-95">Giriş</a>
                    <a href="register.php" class="py-5 bg-[#b84834] hover:bg-[#963926] text-white text-center rounded-3xl font-black shadow-xl shadow-[#b84834]/30 uppercase tracking-widest text-sm transition-all active:scale-95">Katıl 🚀</a>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 p-4 rounded-3xl border border-slate-100 mb-4 flex items-center gap-4">
                    <img src="<?= $photo ?>" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hoş Geldin</div>
                        <div class="font-black text-dark">@<?= $username ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="profile.php" class="py-4 bg-slate-100 text-center rounded-2xl font-bold text-slate-700 text-xs uppercase tracking-widest active:scale-95 transition-transform"><i class="fa-solid fa-user mr-1"></i> Profil</a>
                    <a href="logout.php" class="py-4 bg-rose-50 text-rose-600 text-center rounded-2xl font-bold text-xs uppercase tracking-widest active:scale-95 transition-transform"><i class="fa-solid fa-power-off mr-1"></i> Çıkış</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
(() => {
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('header-mobile-menu');
    const searchButton = document.getElementById('header-search-button');
    const searchPanel = document.getElementById('header-search-panel');

    const menuIcon = menuButton ? menuButton.querySelector('i') : null;
    const closeButton = mobileMenu ? mobileMenu.querySelector('[data-mobile-menu-close]') : null;
    const searchIcon = searchButton ? searchButton.querySelector('i') : null;
    const searchInput = document.getElementById('header-search-input');

    const setMenuOpen = (open) => {
        if (!menuButton || !mobileMenu) return;
        mobileMenu.classList.toggle('is-open', open);
        mobileMenu.setAttribute('aria-hidden', String(!open));
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
        document.body.style.overflow = open ? 'hidden' : '';

        if (menuIcon) {
            menuIcon.classList.toggle('fa-bars-staggered', !open);
            menuIcon.classList.toggle('fa-xmark', open);
        }
    };

    const setSearchOpen = (open) => {
        if (!searchButton || !searchPanel) return;
        searchPanel.classList.toggle('is-open', open);
        searchPanel.setAttribute('aria-hidden', String(!open));
        searchButton.setAttribute('aria-expanded', String(open));
        searchButton.setAttribute('aria-label', open ? 'Aramayı kapat' : 'Aramayı aç');
        if (searchIcon) {
            searchIcon.classList.toggle('fa-magnifying-glass', !open);
            searchIcon.classList.toggle('fa-xmark', open);
        }
        if (open && searchInput) searchInput.focus();
        if (!open && searchInput) searchInput.blur();
    };

    if (menuButton) {
        menuButton.addEventListener('click', () => {
            const open = menuButton.getAttribute('aria-expanded') !== 'true';
            setSearchOpen(false);
            setMenuOpen(open);
        });
    }
    if (closeButton) closeButton.addEventListener('click', () => setMenuOpen(false));
    if (mobileMenu) {
        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMenuOpen(false));
        });
    }
    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const open = searchButton.getAttribute('aria-expanded') !== 'true';
            setMenuOpen(false);
            setSearchOpen(open);
        });
    }
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
            setSearchOpen(false);
        }
    });
})();
</script>

<style>

.nav-pill {
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    transition: all 0.2s ease-in-out;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    color: #475569;
    transition: all 0.2s;
}
.menu-item:hover {
    background-color: #f8fafc;
    color: #1d352f;
}
.menu-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s;
}

.tag-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    color: #64748b;
    transition: all 0.2s;
}
.tag-pill:hover {
    border-color: rgba(68,181,213,0.3);
    color: #b84834;
    background-color: rgba(68,181,213,0.05);
}

.mobile-nav-link {
    display: block;
    font-size: 2.5rem;
    line-height: 1.1;
    font-weight: 900;
    letter-spacing: -0.02em;
    transition: color 0.3s;
}
.mobile-nav-link:hover { color: #1d352f; }

[x-cloak] { display: none !important; }

.dc-search-panel { display: none; }
.dc-search-panel.is-open { display: block; }

.dc-mobile-menu {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1001;
}
.dc-mobile-menu.is-open { display: flex; }

@media (max-width: 640px) {
    .dc-site-header__inner {
        min-height: 60px;
        gap: 8px;
        padding-left: max(12px, env(safe-area-inset-left));
        padding-right: max(12px, env(safe-area-inset-right));
    }

    .dc-site-header__inner > a:first-child {
        min-width: 0;
        gap: 8px;
    }

    .dc-site-header__inner > a:first-child img {
        width: 36px;
        height: 36px;
    }

    .dc-site-header__inner > a:first-child .dc-wordmark {
        font-size: 18px;
    }

    .dc-site-header__inner > div:last-child > button {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
    }

    #header-search-panel {
        padding: 16px;
        padding-left: max(16px, env(safe-area-inset-left));
        padding-right: max(16px, env(safe-area-inset-right));
    }

    #header-search-panel form > div:first-child {
        left: 16px;
    }

    #header-search-panel input {
        padding: 14px 88px 14px 48px;
        border-radius: 16px;
        font-size: 16px;
    }

    #header-search-panel form > button {
        right: 6px;
        padding: 10px 12px;
        border-radius: 12px;
        letter-spacing: .06em;
    }

    #header-search-panel > div > div {
        gap: 8px;
    }

    #header-mobile-menu {
        padding-top: max(84px, calc(env(safe-area-inset-top) + 64px));
        padding-left: max(20px, env(safe-area-inset-left));
        padding-right: max(20px, env(safe-area-inset-right));
        padding-bottom: max(20px, env(safe-area-inset-bottom));
    }

    #header-mobile-menu > div:first-child {
        top: max(16px, env(safe-area-inset-top));
        left: max(20px, env(safe-area-inset-left));
    }

    .mobile-nav-link {
        font-size: clamp(1.75rem, 8vw, 2.25rem);
        line-height: 1.08;
    }

    #header-mobile-menu > div:nth-child(2) {
        margin-top: 12px;
        gap: 20px;
    }
}

/* Input Search Kapatma İkonlarını Gizleme (Tarayıcı Varsayılanı) */
input[type="text"]::-webkit-search-decoration,
input[type="text"]::-webkit-search-cancel-button,
input[type="text"]::-webkit-search-results-button,
input[type="text"]::-webkit-search-results-decoration { display: none; }
</style>
