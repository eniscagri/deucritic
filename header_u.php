<?php
include_once __DIR__ . '/includes/config.php';

$headerUserId = $_SESSION['user_id'] ?? null;
$headerUser = null;

if ($headerUserId) {
    $headerUserQuery = $pdo->prepare('SELECT username, profile_photo, isverified FROM users WHERE id = ?');
    $headerUserQuery->execute([$headerUserId]);
    $headerUser = $headerUserQuery->fetch(PDO::FETCH_ASSOC);
}

$headerUsername = $headerUser ? htmlspecialchars($headerUser['username'], ENT_QUOTES, 'UTF-8') : '';
$headerPhoto = $headerUser && !empty($headerUser['profile_photo'])
    ? htmlspecialchars($headerUser['profile_photo'], ENT_QUOTES, 'UTF-8')
    : '/uploads/default-avatar.png';
?>
<header class="dc-site-header sticky top-0 z-[1000] w-full bg-white/90 backdrop-blur-xl border-b border-slate-200/60 shadow-sm font-sans">
    <div class="dc-site-header__inner max-w-6xl mx-auto px-4 md:px-6 flex justify-between items-center py-2">
        <a href="index.php" class="flex items-center gap-3 group select-none">
            <img src="/gorsel/deucritic-mark.svg" alt="Deucritic" class="w-10 h-10 md:w-11 md:h-11 transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110 drop-shadow-sm">
            <div class="flex flex-col justify-center">
                <span class="text-xl md:text-2xl font-black text-[#1d352f] tracking-tight leading-none">Deucritic</span>
                <span class="text-[9px] font-black text-[#b84834] tracking-[0.25em] uppercase leading-none mt-0.5">Sözlük</span>
            </div>
        </a>

        <nav class="hidden lg:flex items-center p-1.5 bg-slate-100/50 backdrop-blur-md rounded-2xl border border-slate-200/50" aria-label="Ana menü">
            <a href="index.php" class="nav-pill">Ana Sayfa</a>
            <a href="tanitim.php" class="nav-pill">Tanıtım</a>
            <a href="about.php" class="nav-pill">Biz Kimiz?</a>
            <a href="req.php" class="nav-pill">Geliştirin</a>
            <a href="sss.php" class="nav-pill">S.S.S</a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <button id="header-search-button" type="button" aria-label="Aramayı aç" aria-controls="header-search-panel" aria-expanded="false"
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500 border border-slate-200 hover:bg-white hover:text-[#b84834] hover:shadow-sm transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b84834]">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            </button>

            <?php if ($headerUser): ?>
                <div class="relative">
                    <button id="header-profile-button" type="button" aria-label="Hesap menüsünü aç" aria-controls="header-profile-menu" aria-expanded="false"
                        class="flex items-center gap-2 p-1.5 md:pr-4 bg-white border border-slate-200 rounded-[1.25rem] hover:shadow-md hover:border-slate-300 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b84834]">
                        <span class="w-8 h-8 rounded-full overflow-hidden relative border border-slate-100 flex-shrink-0">
                            <img src="<?= $headerPhoto ?>" alt="" class="w-full h-full object-cover">
                            <?php if (!empty($headerUser['isverified'])): ?>
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-blue-500 border-2 border-white rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-check text-[6px] text-white" aria-hidden="true"></i>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="hidden md:block text-sm font-extrabold text-slate-700 truncate max-w-[100px]">@<?= $headerUsername ?></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 hidden md:block transition-transform" aria-hidden="true"></i>
                    </button>

                    <div id="header-profile-menu" class="dc-profile-menu absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-[1100]" aria-hidden="true">
                        <div class="px-4 py-3 bg-slate-50 rounded-xl mb-2 border border-slate-100/50">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Oturum açık</span>
                            <span class="block font-black text-slate-800 truncate text-sm">@<?= $headerUsername ?></span>
                        </div>
                        <a href="profile.php" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-user text-sm" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900">Profilim</span>
                        </a>
                        <a href="dm.php" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-paper-plane text-sm" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900">Mesajlar</span>
                        </a>
                        <div class="h-px bg-slate-100 my-2 mx-2"></div>
                        <a href="logout.php" class="flex items-center gap-3 p-2 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50 transition-colors">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-500">
                                <i class="fa-solid fa-power-off" aria-hidden="true"></i>
                            </span>
                            Çıkış yap
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="hidden sm:flex items-center text-sm font-extrabold text-slate-500 hover:text-slate-800 transition-colors px-3">Giriş</a>
                <a href="register.php" class="hidden sm:flex px-5 py-2.5 bg-[#1d352f] hover:bg-[#b84834] text-white rounded-xl text-sm font-extrabold shadow-md hover:shadow-lg transition-all active:scale-95 items-center gap-2">
                    Katıl <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <button id="mobile-menu-button" type="button" aria-label="Menüyü aç" aria-controls="header-mobile-menu" aria-expanded="false"
                class="lg:hidden w-10 h-10 flex items-center justify-center bg-[#1d352f] text-white rounded-xl active:scale-95 transition-transform focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b84834]">
                <i class="fa-solid fa-bars-staggered text-lg" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div id="header-search-panel" class="dc-search-panel absolute top-full left-0 w-full bg-white/95 backdrop-blur-2xl border-b border-slate-200 shadow-[0_20px_40px_rgba(0,0,0,0.06)] p-6 md:p-10 z-[999]" aria-hidden="true">
        <div class="max-w-3xl mx-auto">
            <form action="search.php" method="GET" role="search" class="relative group">
                <label class="sr-only" for="header-search-input">Başlık veya kullanıcı ara</label>
                <div class="absolute left-6 top-1/2 -translate-y-1/2 flex items-center text-slate-400 group-focus-within:text-[#b84834] transition-colors pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-xl" aria-hidden="true"></i>
                </div>
                <input id="header-search-input" name="q" type="search" maxlength="100" autocomplete="off" placeholder="Başlık veya kullanıcı ara"
                    class="w-full pl-16 pr-28 py-5 rounded-[2rem] bg-slate-50 border-2 border-slate-100 focus:border-[#b84834]/30 focus:bg-white focus:shadow-xl focus:shadow-[#b84834]/5 outline-none transition-all font-bold text-slate-800 placeholder:text-slate-400 text-lg">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 px-6 py-3 bg-[#1d352f] text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-[#b84834] transition-all shadow-md active:scale-95">Ara</button>
            </form>
        </div>
    </div>

    <div id="header-mobile-menu" class="dc-mobile-menu fixed inset-0 bg-white/95 backdrop-blur-2xl z-[1001] lg:hidden flex flex-col p-6 pt-24 overflow-y-auto" aria-hidden="true">
        <div class="absolute top-6 left-6 flex items-center gap-4">
            <button type="button" data-mobile-menu-close aria-label="Menüyü kapat" class="w-12 h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 active:scale-90 transition-transform">
                <i class="fa-solid fa-xmark text-xl" aria-hidden="true"></i>
            </button>
            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Kapat</span>
        </div>

        <nav class="space-y-6 mt-6 flex-1" aria-label="Mobil menü">
            <a href="index.php" class="mobile-nav-link">Ana Sayfa</a>
            <a href="tanitim.php" class="mobile-nav-link">Tanıtım</a>
            <a href="about.php" class="mobile-nav-link">Biz Kimiz?</a>
            <a href="req.php" class="mobile-nav-link">Geliştirin</a>
            <a href="sss.php" class="mobile-nav-link">S.S.S</a>
        </nav>

        <div class="mt-auto space-y-4 pb-8">
            <?php if ($headerUser): ?>
                <div class="bg-slate-50 p-4 rounded-3xl border border-slate-100 mb-4 flex items-center gap-4">
                    <img src="<?= $headerPhoto ?>" alt="" class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hoş geldin</div>
                        <div class="font-black text-[#1d352f]">@<?= $headerUsername ?></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="profile.php" class="py-4 bg-slate-100 text-center rounded-2xl font-bold text-slate-700 text-xs uppercase tracking-widest active:scale-95 transition-transform">
                        <i class="fa-solid fa-user mr-1" aria-hidden="true"></i> Profil
                    </a>
                    <a href="dm.php" class="py-4 bg-emerald-50 text-center rounded-2xl font-bold text-emerald-700 text-xs uppercase tracking-widest active:scale-95 transition-transform">
                        <i class="fa-solid fa-paper-plane mr-1" aria-hidden="true"></i> Mesajlar
                    </a>
                    <a href="logout.php" class="col-span-2 py-4 bg-rose-50 text-rose-600 text-center rounded-2xl font-bold text-xs uppercase tracking-widest active:scale-95 transition-transform">
                        <i class="fa-solid fa-power-off mr-1" aria-hidden="true"></i> Çıkış yap
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-4">
                    <a href="login.php" class="py-5 bg-slate-100 hover:bg-slate-200 text-center rounded-3xl font-black text-slate-700 text-sm uppercase tracking-widest transition-colors active:scale-95">Giriş</a>
                    <a href="register.php" class="py-5 bg-[#b84834] hover:bg-[#963926] text-white text-center rounded-3xl font-black shadow-xl shadow-[#b84834]/30 uppercase tracking-widest text-sm transition-all active:scale-95">Katıl</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<style>
.dc-site-header__inner {
    min-height: 64px;
}
.nav-pill {
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    transition: color .2s ease, background-color .2s ease;
}
.nav-pill:hover {
    color: #1e293b;
    background: rgba(255,255,255,.7);
}
.mobile-nav-link {
    display: block;
    font-size: 2.5rem;
    line-height: 1.1;
    font-weight: 900;
    letter-spacing: -.02em;
    color: #cbd5e1;
    transition: color .2s ease;
}
.mobile-nav-link:hover {
    color: #1d352f;
}
.dc-profile-menu,
.dc-search-panel,
.dc-mobile-menu {
    display: none;
}
.dc-profile-menu.is-open,
.dc-search-panel.is-open {
    display: block;
}
.dc-mobile-menu.is-open {
    display: flex;
}
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
    .dc-site-header__inner > div:last-child > button {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
    }
    #header-search-panel {
        padding: 16px max(16px, env(safe-area-inset-right)) 16px max(16px, env(safe-area-inset-left));
    }
    #header-search-panel input {
        padding: 14px 88px 14px 48px;
        border-radius: 16px;
        font-size: 16px;
    }
    #header-search-panel form > div {
        left: 16px;
    }
    #header-search-panel form > button {
        right: 6px;
        padding: 10px 12px;
        border-radius: 12px;
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
    }
}
@media (prefers-reduced-motion: reduce) {
    .dc-site-header *,
    .dc-site-header *::before,
    .dc-site-header *::after {
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>

<script>
(() => {
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('header-mobile-menu');
    const searchButton = document.getElementById('header-search-button');
    const searchPanel = document.getElementById('header-search-panel');
    const profileButton = document.getElementById('header-profile-button');
    const profileMenu = document.getElementById('header-profile-menu');
    const searchInput = document.getElementById('header-search-input');

    const setMenuOpen = (open) => {
        if (!menuButton || !mobileMenu) return;
        mobileMenu.classList.toggle('is-open', open);
        mobileMenu.setAttribute('aria-hidden', String(!open));
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
        document.body.style.overflow = open ? 'hidden' : '';
    };

    const setSearchOpen = (open) => {
        if (!searchButton || !searchPanel) return;
        searchPanel.classList.toggle('is-open', open);
        searchPanel.setAttribute('aria-hidden', String(!open));
        searchButton.setAttribute('aria-expanded', String(open));
        searchButton.setAttribute('aria-label', open ? 'Aramayı kapat' : 'Aramayı aç');
        const icon = searchButton.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-magnifying-glass', !open);
            icon.classList.toggle('fa-xmark', open);
        }
        if (open && searchInput) searchInput.focus();
        if (!open && searchInput) searchInput.blur();
    };

    const setProfileOpen = (open) => {
        if (!profileButton || !profileMenu) return;
        profileMenu.classList.toggle('is-open', open);
        profileMenu.setAttribute('aria-hidden', String(!open));
        profileButton.setAttribute('aria-expanded', String(open));
        profileButton.setAttribute('aria-label', open ? 'Hesap menüsünü kapat' : 'Hesap menüsünü aç');
        const icon = profileButton.querySelector('.fa-chevron-down');
        if (icon) icon.classList.toggle('rotate-180', open);
    };

    if (menuButton) {
        menuButton.addEventListener('click', () => {
            const open = menuButton.getAttribute('aria-expanded') !== 'true';
            setSearchOpen(false);
            setProfileOpen(false);
            setMenuOpen(open);
        });
    }
    if (mobileMenu) {
        const closeButton = mobileMenu.querySelector('[data-mobile-menu-close]');
        if (closeButton) closeButton.addEventListener('click', () => setMenuOpen(false));
        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMenuOpen(false));
        });
    }
    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const open = searchButton.getAttribute('aria-expanded') !== 'true';
            setMenuOpen(false);
            setProfileOpen(false);
            setSearchOpen(open);
        });
    }
    if (profileButton) {
        profileButton.addEventListener('click', () => {
            setProfileOpen(profileButton.getAttribute('aria-expanded') !== 'true');
        });
    }
    document.addEventListener('click', (event) => {
        if (profileMenu && !profileMenu.parentElement.contains(event.target)) setProfileOpen(false);
        if (searchPanel && !searchPanel.contains(event.target) && (!searchButton || !searchButton.contains(event.target))) setSearchOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
            setSearchOpen(false);
            setProfileOpen(false);
        }
    });
})();
</script>
