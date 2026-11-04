<?php
require 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Kendi Bilgilerimiz */
$stmt = $pdo->prepare("SELECT username, profile_photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$me = $stmt->fetch();

/* Sohbet listesi ve Okunmamış Mesaj Sayacı */
$stmt = $pdo->prepare("
    SELECT 
        u.id, u.username, u.profile_photo, u.isverified,
        m.message, m.created_at, m.sender_id, m.viewed_on,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND viewed_on = 0) as unread
    FROM conversation_users cu1
    JOIN conversation_users cu2 ON cu1.conversation_id = cu2.conversation_id
    JOIN users u ON u.id = cu1.user_id
    LEFT JOIN messages m ON m.id = (
        SELECT id FROM messages 
        WHERE conversation_id = cu1.conversation_id
        ORDER BY id DESC LIMIT 1
    )
    WHERE cu2.user_id = ? AND u.id != ?
    ORDER BY COALESCE(m.id, 0) DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$users = $stmt->fetchAll();

/* Aktif Chat Kontrolü */
$active = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT) ?? null;
$current_convo = null;
$chatUser = null;

if ($active) {
    // Mesajları okundu olarak işaretle
    $pdo->prepare("UPDATE messages SET viewed_on=1 WHERE sender_id=? AND receiver_id=? AND viewed_on=0")
        ->execute([$active, $user_id]);

    $stmt = $pdo->prepare("SELECT id, username, profile_photo, isverified, last_seen FROM users WHERE id=?");
    $stmt->execute([$active]);
    $chatUser = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT cu1.conversation_id FROM conversation_users cu1
        JOIN conversation_users cu2 ON cu1.conversation_id = cu2.conversation_id
        WHERE cu1.user_id=? AND cu2.user_id=? LIMIT 1
    ");
    $stmt->execute([$user_id, $active]);
    $current_convo = $stmt->fetchColumn();
}

$my_avatar = !empty($me['profile_photo']) 
    ? $me['profile_photo'] 
    : 'https://ui-avatars.com/api/?background=6366f1&color=fff&bold=true&name='.urlencode($me['username']);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#f8fafc">
    
    <title><?= $active ? 'Sohbet | Deucritic' : 'Mesajlar | Deucritic' ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <script src="https://unpkg.com/peerjs@1.5.1/dist/peerjs.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        inter: ['Inter', 'sans-serif']
                    },
                    colors: { primary: '#4f46e5', secondary: '#6366f1' },
                    boxShadow: { 'soft': '0 4px 40px rgba(0, 0, 0, 0.03)', 'glow': '0 0 20px rgba(79, 70, 229, 0.15)' }
                }
            }
        }
    </script>
    
    <style>
        /* CSS Değişkenleri ile Safe Area Yönetimi */
        :root {
            --sat: env(safe-area-inset-top);
            --sab: env(safe-area-inset-bottom);
        }

        body { 
            touch-action: manipulation; 
            background-color: #f1f5f9; 
            height: 100dvh; 
            overflow: hidden; 
            -webkit-tap-highlight-color: transparent; 
        }

        /* IOS ZOOM FIX */
        textarea, input[type="text"] { font-size: 16px !important; }
        
        /* SAFE AREA PADDINGS */
        .pt-safe { padding-top: max(var(--sat), 1rem) !important; }
        .pb-safe { padding-bottom: max(var(--sab), 1rem) !important; }
        .mb-safe { margin-bottom: var(--sab) !important; }

        .chat-scroll { scroll-behavior: smooth; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; }
        .chat-scroll::-webkit-scrollbar { width: 5px; }
        .chat-scroll::-webkit-scrollbar-track { background: transparent; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        @keyframes fadeInRise { from { opacity: 0; transform: translateY(10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .msg-animate { animation: fadeInRise 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        
        .typing-dot { animation: typing 1.4s infinite ease-in-out both; }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing { 0%, 80%, 100% { transform: scale(0); opacity: 0.5; } 40% { transform: scale(1); opacity: 1; } }

        @keyframes ringShake { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(-15deg); } 75% { transform: rotate(15deg); } }
        .ringing-icon { animation: ringShake 0.5s infinite; }

        emoji-picker { width: 100%; height: 320px; --background: #ffffff; --indicator-color: #4f46e5; --border-radius: 1.5rem; border: none; }
        
        /* KUSURSUZ WHATSAPP BALON TASARIMI (Overlap Fix) */
        .message-bubble {
            position: relative;
            padding: 6px 12px 8px 12px;
            border-radius: 16px;
            font-size: 15px;
            line-height: 1.4;
            display: inline-block;
            max-width: 100%;
            word-wrap: break-word;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
        }
        .message-text {
            display: inline;
        }
        .message-time {
            float: right;
            margin: 8px -4px -4px 12px;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
            line-height: 1;
        }
        .message-time::before {
            content: '\200E'; /* Zero-width space to force float alignment */
            display: inline-block;
        }

        /* Sistem Mesajları (Arama Logları vb.) */
        .system-bubble {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            margin: 10px auto;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        
        .tick-icon { transition: color 0.3s ease; font-size: 11px; }
    </style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="flex flex-col md:flex-row h-[100dvh] w-full relative selection:bg-indigo-100 selection:text-indigo-900 dc-dm-page">

    <audio id="remoteAudio" autoplay class="hidden"></audio>
    <audio id="ringtoneAudio" src="https://actions.google.com/sounds/v1/alarms/phone_ringing.ogg" loop class="hidden"></audio>
    <audio id="msgInAudio" src="https://actions.google.com/sounds/v1/cartoon/pop_hollow_reverb.ogg" class="hidden"></audio>

    <aside class="hidden md:flex flex-col w-[85px] lg:w-[280px] h-full border-r border-slate-200/60 bg-white z-50 shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        <div class="p-8 pt-safe flex items-center justify-center lg:justify-start gap-4">
            <a href="index.php" class="hidden lg:flex flex-col select-none">
                <span class="font-black text-2xl tracking-tighter text-slate-900 leading-none">Deucritic</span>
                <span class="text-[10px] font-bold text-primary tracking-[0.3em] uppercase mt-1">Sohbet</span>
            </a>
        </div>
        <nav class="flex-1 px-4 space-y-2">
            <a href="index.php" class="group flex items-center gap-4 p-3.5 rounded-2xl transition-all <?= $current_page == 'index.php' ? 'bg-indigo-50 text-indigo-600 shadow-sm' : 'text-slate-500 hover:bg-slate-50' ?>">
                <div class="w-6 flex justify-center"><i class="fa-solid fa-house text-lg group-hover:scale-110 transition-transform"></i></div>
                <span class="hidden lg:block font-bold tracking-tight text-[15px]">Ana Sayfa</span>
            </a>
            <a href="dm.php" class="group relative flex items-center gap-4 p-3.5 rounded-2xl transition-all <?= $current_page == 'dm.php' ? 'bg-gradient-to-br from-indigo-600 to-primary text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50' ?>">
                <div class="w-6 flex justify-center"><i class="fa-solid fa-paper-plane text-lg group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i></div>
                <span class="hidden lg:block font-bold tracking-tight text-[15px]">Mesajlar</span>
            </a>
            <a href="profile.php" class="group flex items-center gap-4 p-3.5 rounded-2xl transition-all <?= $current_page == 'profile.php' ? 'bg-indigo-50 text-indigo-600' : 'text-slate-500 hover:bg-slate-50' ?>">
                <div class="w-6 flex justify-center">
                    <img src="<?= $my_avatar ?>" class="w-7 h-7 rounded-full object-cover border-2 <?= $current_page == 'profile.php' ? 'border-indigo-600' : 'border-transparent group-hover:border-indigo-200' ?> transition-all">
                </div>
                <span class="hidden lg:block font-bold tracking-tight text-[15px]">Profilim</span>
            </a>
        </nav>
    </aside>

    <nav class="<?= $active ? 'hidden' : 'flex' ?> md:hidden fixed bottom-0 left-0 w-full bg-white/95 backdrop-blur-xl border-t border-slate-200 z-[1000] justify-around items-center px-6 pb-safe shadow-[0_-10px_40px_rgba(0,0,0,0.05)]" style="height: calc(65px + var(--sab));">
        <a href="index.php" class="flex flex-col items-center justify-center gap-1.5 transition-all text-slate-400"><i class="fa-solid fa-house text-xl"></i></a>
        <a href="dm.php" class="relative flex flex-col items-center justify-center gap-1.5 transition-all text-primary">
            <i class="fa-solid fa-paper-plane text-xl"></i>
            <span class="absolute -bottom-2 w-1.5 h-1.5 rounded-full bg-primary"></span>
        </a>
        <a href="profile.php" class="flex flex-col items-center justify-center gap-1.5">
            <img src="<?= $my_avatar ?>" class="w-7 h-7 rounded-full object-cover border-2 border-transparent">
        </a>
    </nav>

    <main class="flex-1 w-full h-full relative flex justify-center bg-white md:bg-[#f8fafc] md:p-6 lg:p-8">
        
        <div class="w-full h-full md:rounded-[2rem] md:shadow-soft flex bg-white md:overflow-hidden md:border border-slate-200/60 max-w-[1400px]">

            <aside class="<?= $active ? 'hidden md:flex' : 'flex' ?> flex-col w-full md:w-[350px] lg:w-[400px] bg-white/50 border-r border-slate-100 h-full shrink-0 relative z-20">
                
                <div class="px-4 md:px-6 pb-4 border-b border-slate-100/80 bg-white/95 backdrop-blur-xl sticky top-0 z-10 shrink-0 pt-safe">
                    <div class="flex justify-between items-center mb-5 md:mb-6 mt-2 md:mt-0">
                        <h1 class="font-black text-2xl md:text-[26px] tracking-tight text-slate-900">Sohbetler</h1>
                        <a href="search_users.php" class="w-10 h-10 flex items-center justify-center bg-indigo-50 hover:bg-primary text-primary hover:text-white rounded-full transition-all duration-300 shadow-sm active:scale-95" title="Yeni Mesaj">
                            <i class="fa-solid fa-pen-to-square text-[15px]"></i>
                        </a>
                    </div>
                    <div class="relative group">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400 text-[14px] group-focus-within:text-primary transition-colors"></i>
                        <input type="text" id="userFilterInput" placeholder="Kişi veya mesaj ara..." class="w-full bg-slate-50/80 rounded-2xl pl-11 pr-4 py-3.5 outline-none focus:bg-white focus:ring-[3px] focus:ring-primary/15 transition-all border border-slate-200/80 font-medium placeholder-slate-400">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto chat-scroll p-2 md:p-3 space-y-1 pb-[calc(80px+var(--sab))]" id="userListContainer">
                    <?php if(empty($users)): ?>
                        <div class="flex flex-col items-center justify-center h-full text-slate-400 opacity-80 mt-10">
                            <i class="fa-regular fa-comments text-5xl mb-4 text-slate-200"></i>
                            <span class="text-sm font-bold text-slate-400">Henüz mesajınız yok.</span>
                        </div>
                    <?php endif; ?>

                    <?php foreach($users as $u): ?>
                        <a href="?user=<?= $u['id'] ?>" data-name="<?= strtolower($u['username']) ?>"
                           class="user-list-item group flex items-center gap-3 md:gap-4 p-3 rounded-2xl hover:bg-slate-50 transition-all duration-300 <?= $active == $u['id'] ? 'bg-slate-50 md:bg-white md:shadow-glow border border-transparent md:border-indigo-100/50' : 'border border-transparent' ?>">
                            
                            <div class="relative shrink-0">
                                <img src="<?= !empty($u['profile_photo']) ? $u['profile_photo'] : 'https://ui-avatars.com/api/?background=random&color=fff&name='.urlencode($u['username']) ?>" 
                                     class="w-12 h-12 md:w-14 md:h-14 rounded-[1.2rem] object-cover shadow-sm transition-transform group-hover:scale-105">
                                <?php if($u['unread'] > 0): ?>
                                    <span class="absolute -top-1 -right-1 bg-rose-500 min-w-[20px] h-5 px-1 flex items-center justify-center rounded-full text-[10px] text-white font-black ring-[3px] ring-white shadow-md">
                                        <?= $u['unread'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-[14px] md:text-[15px] text-slate-900 truncate <?= $active == $u['id'] ? 'text-primary' : '' ?>">
                                        <?= htmlspecialchars($u['username']) ?>
                                        <?php if($u['isverified']): ?>
                                            <i class="fa-solid fa-circle-check text-blue-500 text-[12px] ml-1"></i>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-[11px] md:text-[12px] text-slate-400 font-bold whitespace-nowrap ml-2">
                                        <?= $u['created_at'] ? date('H:i', strtotime($u['created_at'])) : '' ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 text-[13px] md:text-[14px] leading-tight truncate <?= $u['unread'] > 0 ? 'text-slate-900 font-bold' : 'text-slate-500 font-medium' ?>">
                                    <?php 
                                    if(isset($u['sender_id']) && $u['sender_id'] == $user_id) {
                                        if($u['viewed_on'] == 1) {
                                            echo '<i class="fa-solid fa-check-double text-blue-500 text-[10px]"></i>';
                                        } else {
                                            echo '<i class="fa-solid fa-check-double text-slate-300 text-[10px]"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="truncate"><?= htmlspecialchars($u['message'] ?? 'Sohbeti başlatın...') ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <main class="<?= !$active ? 'hidden md:flex' : 'flex' ?> flex-1 flex-col bg-white md:bg-[#fafafa] h-[100dvh] md:h-full relative w-full overflow-hidden">
                
                <?php if($chatUser): ?>
                    <header x-data="{ chatMenuOpen: false }" class="px-3 md:px-8 pb-3 md:pb-4 border-b border-slate-200/60 bg-white/95 backdrop-blur-2xl flex items-center justify-between sticky top-0 z-[50] shrink-0 shadow-sm transition-all pt-safe">
                        <div class="flex items-center gap-2 md:gap-4">
                            <a href="dm.php" class="md:hidden w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 active:scale-90 transition-all shrink-0">
                                <i class="fa-solid fa-chevron-left text-[18px]"></i>
                            </a>
                            
                            <a href="profile.php?user=<?= $chatUser['id'] ?>" class="flex items-center gap-3 group p-1 md:p-1.5 -ml-1 md:-ml-1.5 rounded-2xl hover:bg-slate-50 transition-colors">
                                <div class="relative shrink-0">
                                    <img src="<?= !empty($chatUser['profile_photo']) ? $chatUser['profile_photo'] : 'https://ui-avatars.com/api/?background=random&color=fff&name='.urlencode($chatUser['username']) ?>" 
                                         class="w-10 h-10 md:w-12 md:h-12 rounded-full md:rounded-[1rem] object-cover ring-2 ring-transparent group-hover:ring-indigo-100 transition-all shadow-sm">
                                    <?php 
                                    if (!empty($chatUser['last_seen']) && round((time() - strtotime($chatUser['last_seen'])) / 60) < 3): ?>
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-[2.5px] border-white rounded-full"></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex flex-col min-w-0">
                                    <div class="font-extrabold text-slate-900 text-[15px] md:text-[16px] leading-tight truncate flex items-center gap-1.5 group-hover:text-primary transition-colors">
                                        <?= htmlspecialchars($chatUser['username']) ?>
                                        <?php if($chatUser['isverified']): ?>
                                            <i class="fa-solid fa-circle-check text-blue-500 text-[12px]"></i>
                                        <?php endif; ?>
                                    </div>
                                    <?php 
                                    $status_text = "Çevrimdışı";
                                    $status_color = "text-slate-400";
                                    if (!empty($chatUser['last_seen'])) {
                                        $last_seen_timestamp = strtotime($chatUser['last_seen']);
                                        $diff_minutes = round((time() - $last_seen_timestamp) / 60);
                                        if ($diff_minutes < 3) { 
                                            $status_text = "Çevrimiçi";
                                            $status_color = "text-emerald-500"; 
                                        } else if ($last_seen_timestamp >= strtotime("today")) {
                                            $status_text = "Bugün " . date("H:i", $last_seen_timestamp);
                                        } else { 
                                            $status_text = date("d M H:i", $last_seen_timestamp);
                                        }
                                    }
                                    ?>
                                    <div class="text-[11px] md:text-[12px] <?= $status_color ?> font-semibold tracking-wide mt-0.5 truncate">
                                        <?= $status_text ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="flex items-center gap-1 md:gap-2">
                            <button onclick="startRealCall('audio')" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center text-primary bg-indigo-50/80 hover:bg-primary hover:text-white rounded-full transition-all duration-300 shadow-sm active:scale-90" title="Sesli Ara">
                                <i class="fa-solid fa-phone text-[14px]"></i>
                            </button>

                            <div class="relative">
                                <button @click="chatMenuOpen = !chatMenuOpen" 
                                        class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-full transition-all duration-300 active:scale-90" 
                                        :class="chatMenuOpen ? 'bg-slate-100 text-slate-800' : ''">
                                    <i class="fa-solid fa-ellipsis-vertical text-[18px]"></i>
                                </button>
                                <div x-show="chatMenuOpen" @click.away="chatMenuOpen = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 p-1.5 z-50 overflow-hidden" x-cloak>
                                    <a href="profile.php?user=<?= $chatUser['id'] ?>" class="flex items-center gap-3 w-full text-left px-3 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition-colors">
                                        <i class="fa-regular fa-user text-slate-400 w-4 text-center"></i> Profili Gör
                                    </a>
                                    <div class="h-px bg-slate-100 my-1 mx-2"></div>
                                    <button class="flex items-center gap-3 w-full text-left px-3 py-2.5 text-sm font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors">
                                        <i class="fa-solid fa-ban text-rose-400 w-4 text-center"></i> Engelle
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div id="chat" class="flex-1 overflow-y-auto p-4 md:p-8 space-y-3 chat-scroll font-inter relative bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-white md:bg-[#fafafa]">
                        <div id="typingIndicator" class="hidden flex justify-start msg-animate mt-2">
                            <div class="bg-white border border-slate-100 px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm flex gap-1.5 items-center">
                                <div class="w-2 h-2 bg-slate-300 rounded-full typing-dot"></div>
                                <div class="w-2 h-2 bg-slate-300 rounded-full typing-dot"></div>
                                <div class="w-2 h-2 bg-slate-300 rounded-full typing-dot"></div>
                            </div>
                        </div>
                    </div>

                    <button id="scrollToBottomBtn" class="hidden absolute bottom-[90px] md:bottom-[100px] right-4 md:right-8 bg-white text-slate-600 border border-slate-200 w-10 h-10 rounded-full shadow-lg items-center justify-center z-30 hover:bg-slate-50 transition-transform transform translate-y-2 opacity-0">
                        <i class="fa-solid fa-chevron-down text-[14px]"></i>
                    </button>

                    <footer class="relative bg-white border-t border-slate-100 shrink-0 pb-safe z-50 px-3 md:px-6 pt-2 pb-2 md:pt-4 md:pb-4">
                        
                        <div id="emojiContainer" class="hidden absolute bottom-full left-0 w-full md:w-auto md:min-w-[320px] md:left-6 z-[100] bg-white shadow-[0_-15px_40px_rgba(0,0,0,0.12)] rounded-t-[2rem] md:rounded-3xl border border-slate-100 msg-animate mb-1 overflow-hidden">
                            <div class="w-full flex justify-center py-3 md:hidden bg-slate-50/80 border-b border-slate-100">
                                <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
                            </div>
                            <emoji-picker class="light"></emoji-picker>
                        </div>
                        
                        <div id="imagePreviewContainer" class="hidden absolute bottom-[100%] left-0 w-full p-3 md:p-4 bg-white/95 backdrop-blur-md border-t border-slate-100 flex items-center gap-4 transition-all z-10 shadow-[0_-10px_20px_rgba(0,0,0,0.03)]">
                            <div class="relative group">
                                <img id="imagePreview" src="" class="h-16 w-16 md:h-20 md:w-20 object-cover rounded-xl md:rounded-2xl border border-slate-200 shadow-sm">
                                <button type="button" onclick="clearImage()" class="absolute -top-2 -right-2 bg-rose-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[13px] md:text-[14px] text-slate-800 font-bold truncate" id="fileName">Fotoğraf seçildi</div>
                                <div class="text-[11px] md:text-[12px] text-slate-500 font-medium">Göndermek için bir şeyler yazın.</div>
                            </div>
                        </div>

                        <form id="form" class="flex items-end gap-2 md:gap-3 max-w-5xl mx-auto bg-slate-50/80 p-1.5 md:p-2.5 rounded-[1.5rem] md:rounded-[2rem] border border-slate-200 focus-within:bg-white focus-within:ring-[3px] focus-within:ring-primary/15 transition-all shadow-sm relative">
                            <input type="hidden" name="conversation_id" value="<?= $current_convo ?>">
                            <input type="hidden" name="receiver_id" value="<?= $active ?>">
                            
                            <input type="file" id="fileInput" name="photo" class="hidden" accept="image/*">
                            <button type="button" onclick="document.getElementById('fileInput').click()" class="w-10 h-10 md:w-11 md:h-11 flex items-center justify-center text-slate-400 hover:text-primary hover:bg-indigo-50 rounded-full transition-colors shrink-0 active:scale-95">
                                <i class="fa-solid fa-paperclip text-[16px] md:text-[18px]"></i>
                            </button>

                            <div class="flex-1 relative pb-0.5 md:pb-1">
                                <textarea id="msg" name="message" autocomplete="off" rows="1"
                                    class="w-full bg-transparent border-none px-2 py-2 text-[16px] font-medium outline-none text-slate-800 placeholder-slate-400 font-inter resize-none max-h-28 overflow-y-auto block leading-tight"
                                    placeholder="Mesaj yaz..."></textarea>
                            </div>

                            <button type="button" id="emojiBtn" class="w-10 h-10 md:w-11 md:h-11 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-full transition-colors shrink-0 active:scale-95">
                                <i class="fa-regular fa-face-smile text-[18px] md:text-[20px]"></i>
                            </button>

                            <button type="submit" id="submitBtn" class="bg-primary hover:bg-indigo-600 disabled:opacity-50 text-white w-10 h-10 md:w-11 md:h-11 flex items-center justify-center rounded-full shadow-md transition-all shrink-0 group active:scale-90">
                                <i class="fa-solid fa-paper-plane text-[14px] md:text-[15px] -ml-0.5 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" id="sendIcon"></i>
                                <i class="fa-solid fa-circle-notch animate-spin hidden text-[14px]" id="loadingIcon"></i>
                            </button>
                        </form>
                    </footer>

                <?php else: ?>
                    <div class="hidden md:flex flex-col items-center justify-center h-full text-center p-8 bg-slate-50/50">
                        <div class="w-28 h-28 bg-white shadow-lg shadow-indigo-100/50 text-primary rounded-[2rem] rotate-12 flex items-center justify-center mb-6 relative">
                            <div class="absolute inset-0 bg-primary/5 rounded-[2rem] -rotate-12 scale-105"></div>
                            <i class="fa-solid fa-paper-plane text-4xl -rotate-12"></i>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Deucritic Mesajlar</h2>
                        <p class="text-slate-500 max-w-sm mt-3 text-[15px] leading-relaxed font-medium">Soldaki listeden bir sohbet seç veya yeni bir mesaj göndererek sohbete başla.</p>
                        <a href="search_users.php" class="mt-8 px-6 py-3.5 bg-slate-900 text-white font-bold text-sm rounded-full shadow-lg shadow-slate-900/20 hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-300">
                            Yeni Mesaj Gönder
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </main>

    <div id="callOverlay" class="hidden fixed inset-0 z-[9999] bg-slate-900/90 backdrop-blur-2xl flex-col items-center justify-center text-white transition-opacity duration-300">
        
        <div class="absolute top-[max(env(safe-area-inset-top),1.5rem)] mt-4 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full text-slate-200 font-medium tracking-wide text-[11px] sm:text-xs flex items-center gap-2 border border-white/10 shadow-lg">
            <i class="fa-solid fa-lock text-[10px] text-emerald-400"></i> Uçtan Uca Şifreli
        </div>
        
        <div class="relative mb-8 md:mb-12 mt-12 flex justify-center items-center">
            <div id="rippleEffect" class="absolute w-32 h-32 md:w-40 md:h-40 rounded-full bg-indigo-500/30 animate-ping"></div>
            <div class="absolute w-48 h-48 md:w-60 md:h-60 rounded-full border border-indigo-500/20 animate-pulse"></div>
            
            <img src="<?= $chatUser ? (!empty($chatUser['profile_photo']) ? $chatUser['profile_photo'] : 'https://ui-avatars.com/api/?background=random&color=fff&name='.urlencode($chatUser['username'])) : '' ?>" 
                 class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover ring-[4px] ring-white/20 z-10 relative shadow-[0_0_50px_rgba(99,102,241,0.5)]">
        </div>
        
        <div class="flex flex-col items-center z-10 mb-12 md:mb-16">
            <h3 class="text-3xl md:text-4xl font-black mb-2 tracking-tight text-center px-4 drop-shadow-md">
                <?= $chatUser ? htmlspecialchars($chatUser['username']) : 'Arama Geliyor' ?>
            </h3>
            <p id="callTimerText" class="text-indigo-200 font-medium text-base md:text-lg tracking-wide hidden">00:00</p>
            <p id="callStatusText" class="text-indigo-200 font-medium text-base md:text-lg tracking-wide">Aranıyor...</p>
        </div>
        
        <div class="flex gap-6 md:gap-10 items-center h-24 pb-[max(env(safe-area-inset-bottom),1.5rem)] z-10">
            
            <div id="incomingCallActions" class="hidden gap-8 md:gap-12">
                <div class="flex flex-col items-center gap-3">
                    <button onclick="rejectCall()" class="w-16 h-16 md:w-20 md:h-20 bg-rose-500 hover:bg-rose-600 rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(244,63,94,0.3)] transition-all active:scale-90 border border-rose-400/50">
                        <i class="fa-solid fa-phone-slash text-2xl md:text-3xl"></i>
                    </button>
                    <span class="text-[11px] font-bold text-slate-300 tracking-wider">Reddet</span>
                </div>
                
                <div class="flex flex-col items-center gap-3">
                    <button onclick="acceptCall()" class="w-16 h-16 md:w-20 md:h-20 bg-emerald-500 hover:bg-emerald-600 rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(16,185,129,0.4)] transition-all active:scale-90 border border-emerald-400/50 ringing-icon">
                        <i class="fa-solid fa-phone text-2xl md:text-3xl"></i>
                    </button>
                    <span class="text-[11px] font-bold text-slate-300 tracking-wider">Cevapla</span>
                </div>
            </div>

            <div id="activeCallActions" class="hidden gap-5 md:gap-8 items-start" x-data="{ isMuted: false, isSpeaker: false }">
                
                <div class="flex flex-col items-center gap-2 group">

    <button id="muteBtn"
        @click="isMuted = !isMuted; toggleMute(isMuted);"
        :class="isMuted
            ? 'bg-red-500 hover:bg-red-600 shadow-[0_0_30px_rgba(239,68,68,0.45)] border border-red-400/50'
            : 'bg-slate-700 hover:bg-slate-600 shadow-[0_0_25px_rgba(255,255,255,0.08)] border border-white/10'"
        class="relative overflow-hidden w-16 h-16 md:w-20 md:h-20 rounded-full flex items-center justify-center transition-all duration-300 active:scale-90 ringing-icon">

        <!-- Pulse -->
        <div x-show="isMuted"
             class="absolute inset-0 rounded-full bg-red-400/20 animate-ping">
        </div>

        <!-- Shine -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent rounded-full"></div>

        <!-- Icon -->
        <i class="fa-solid relative z-10 text-2xl md:text-3xl transition-all duration-300"
           :class="isMuted 
                ? 'fa-microphone-slash text-white rotate-6' 
                : 'fa-microphone text-white group-hover:scale-110'">
        </i>

    </button>

    <span class="text-[11px] font-bold tracking-wider transition-all duration-300"
          :class="isMuted ? 'text-red-300' : 'text-slate-300'"
          x-text="isMuted ? 'Sessizde' : 'Mikrofon'">
    </span>

</div>

                <div class="flex flex-col items-center gap-2 mt-2">
                    <button onclick="endRealCall()" class="w-16 h-16 md:w-20 md:h-20 bg-rose-500 hover:bg-rose-600 rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(244,63,94,0.4)] transition-all active:scale-90 border border-rose-400/50">
                        <i class="fa-solid fa-phone-slash text-2xl md:text-3xl"></i>
                    </button>
                    <span class="text-[11px] font-bold text-rose-400 tracking-wider">Bitir</span>
                </div>

                <div class="flex flex-col items-center gap-2">
                    <button id="speakerBtn" 
                            @click="isSpeaker = !isSpeaker" 
                            :class="isSpeaker ? 'bg-white text-slate-900 shadow-[0_0_20px_rgba(255,255,255,0.5)]' : 'bg-white/10 text-white border border-white/10 hover:bg-white/20'"
                            class="w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center backdrop-blur-md transition-all active:scale-90">
                        <i class="fa-solid fa-volume-high text-xl md:text-2xl"></i>
                    </button>
                    <span class="text-[10px] font-medium text-slate-300" x-text="isSpeaker ? 'Açık' : 'Hoparlör'"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="lightbox" class="hidden fixed inset-0 z-[10000] bg-slate-900/98 backdrop-blur-lg flex items-center justify-center transition-opacity duration-300 opacity-0" onclick="closeLightbox()">
        <button class="absolute top-[max(env(safe-area-inset-top),1rem)] right-4 w-10 h-10 md:w-12 md:h-12 bg-white/10 hover:bg-rose-500 text-white rounded-full flex items-center justify-center backdrop-blur-sm transition-colors z-50">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <img id="lightboxImg" src="" class="max-w-[100vw] md:max-w-[90%] max-h-[100dvh] md:max-h-[90dvh] object-contain rounded-none md:rounded-xl shadow-2xl scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    });

    // SISTEM MESAJI GÖNDERME FONKSIYONU (ARAMA LOGLARI İÇİN)
    function sendSystemMessage(text) {
        const formData = new FormData();
        formData.append('conversation_id', '<?= $current_convo ?>');
        formData.append('receiver_id', '<?= $active ?>');
        formData.append('message', text);
        fetch("send_message.php", { method: "POST", body: formData }).then(() => load());
    }

    const searchInput = document.getElementById('userFilterInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const filter = e.target.value.toLowerCase();
            document.querySelectorAll('.user-list-item').forEach(el => {
                const name = el.getAttribute('data-name');
                if (name.includes(filter)) {
                    el.classList.remove('hidden'); el.classList.add('flex');
                } else {
                    el.classList.add('hidden'); el.classList.remove('flex');
                }
            });
        });
    }

    const chat = document.getElementById("chat");
    const msgInput = document.getElementById("msg");
    const scrollBtn = document.getElementById("scrollToBottomBtn");
    const submitBtn = document.getElementById("submitBtn");
    const sendIcon = document.getElementById("sendIcon");
    const loadingIcon = document.getElementById("loadingIcon");
    let lastId = 0;
    
    function escapeHTML(str) {
        if (!str) return '';
        return str.replace(/[&<>'"]/g, tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag]));
    }

    function isUserReadingOldMessages() {
        if (!chat) return false;
        return chat.scrollHeight - chat.scrollTop - chat.clientHeight > 200;
    }

    function openLightbox(src) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        lightboxImg.src = src;
        lightbox.classList.remove('hidden');
        setTimeout(() => { lightbox.classList.remove('opacity-0'); lightboxImg.classList.remove('scale-95'); lightboxImg.classList.add('scale-100'); }, 10);
    }
    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightboxImg');
        lightbox.classList.add('opacity-0');
        lightboxImg.classList.remove('scale-100'); lightboxImg.classList.add('scale-95');
        setTimeout(() => { lightbox.classList.add('hidden'); lightboxImg.src = ''; }, 300);
    }

    function load() {
        if(!<?= $active ?: 0 ?>) return;
        
        fetch(`get_messages.php?user=<?= $active ?>&last_id=${lastId}`)
        .then(r => r.json())
        .then(d => {
            if(d.length > 0) {
                const isReadingHistory = isUserReadingOldMessages();

                d.forEach(m => {
                    const isMe = m.sender_id == <?= $user_id ?>;
                    const safeMessage = escapeHTML(m.message);
                    const time = new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    const isSystemMessage = safeMessage.includes('📞') || safeMessage.includes('❌');
                    
                    let statusIcon = '';
                    if (isMe) {
                        if(m.viewed_on == 1) {
                            statusIcon = '<i class="fa-solid fa-check-double text-blue-300 tick-icon"></i>';
                        } else {
                            statusIcon = '<i class="fa-solid fa-check text-indigo-300 tick-icon"></i>';
                        }
                    }

                    let messageContent = safeMessage.replace(
                        /\[IMAGE\](.*?)\[\/IMAGE\]/g, 
                        '<div class="overflow-hidden rounded-xl mt-1 mb-1"><img src="$1" onload="if(!isUserReadingOldMessages()) chat.scrollTo({ top: chat.scrollHeight, behavior: \'smooth\' })" class="w-full md:w-64 h-auto max-h-64 object-cover cursor-zoom-in active:opacity-70 transition-opacity border border-black/5 bg-slate-100" alt="Fotoğraf" onclick="openLightbox(this.src); event.stopPropagation();"></div>'
                    );
                    
                    const div = document.createElement("div");
                    
                    // SİSTEM MESAJLARI
                    if (isSystemMessage) {
                        div.className = `flex justify-center msg-animate mb-3`;
                        div.innerHTML = `<div class="system-bubble">${messageContent} <span class="opacity-60 ml-1 font-normal">${time}</span></div>`;
                    } 
                    // NORMAL MESAJLAR
                    else {
                        div.className = `flex ${isMe ? 'justify-end' : 'justify-start'} msg-animate group mb-2`;

                        // "Kusursuz WhatsApp Balonu" - Saat her zaman en sağda durur. Metin ile üst üste binmez.
                        if (isMe) {
                            div.innerHTML = `
                                <div class="max-w-[85%] md:max-w-[70%] relative flex flex-col items-end">
                                    <div class="message-bubble bg-gradient-to-br from-indigo-600 to-primary text-white rounded-tr-sm">
                                        <span class="message-text">${messageContent}</span>
                                        <div class="message-time text-indigo-100/90">
                                            ${time} <span class="flex items-center justify-center ml-0.5">${statusIcon}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            const partnerAvatar = `<?= $chatUser ? (!empty($chatUser['profile_photo']) ? $chatUser['profile_photo'] : 'https://ui-avatars.com/api/?background=random&color=fff&name='.urlencode($chatUser['username'])) : '' ?>`;
                            div.innerHTML = `
                                <div class="max-w-[85%] md:max-w-[70%] relative flex items-end gap-2.5">
                                    <img src="${partnerAvatar}" class="w-8 h-8 rounded-full object-cover shadow-sm shrink-0 hidden sm:block mb-1 border border-slate-100">
                                    <div class="message-bubble bg-white border border-slate-100 text-slate-800 rounded-tl-sm">
                                        <span class="message-text">${messageContent}</span>
                                        <div class="message-time text-slate-400">
                                            ${time}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    }

                    const typingIndicator = document.getElementById('typingIndicator');
                    chat.insertBefore(div, typingIndicator);
                    lastId = m.id;
                });

                if (!isReadingHistory) {
                    chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
                } else if (d[d.length - 1].sender_id != <?= $user_id ?>) {
                    scrollBtn.classList.remove('hidden', 'opacity-0', 'translate-y-2');
                    scrollBtn.classList.add('flex', 'opacity-100', 'translate-y-0');
                }
            }
        });
    }

    <?php if($active): ?>
        // EMOJI İŞLEMLERİ (Mobil Klavye ile Uyumlu)
        const emojiBtn = document.getElementById("emojiBtn");
        const emojiContainer = document.getElementById("emojiContainer");
        const picker = document.querySelector('emoji-picker');
        let lastCursorPos = 0;

        msgInput.addEventListener('blur', () => { lastCursorPos = msgInput.selectionStart; });
        msgInput.addEventListener('keyup', () => { lastCursorPos = msgInput.selectionStart; });
        msgInput.addEventListener('click', () => { lastCursorPos = msgInput.selectionStart; });

        emojiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            msgInput.blur(); 
            emojiContainer.classList.toggle('hidden');
        });

        picker.addEventListener('emoji-click', event => {
            const text = msgInput.value;
            msgInput.value = text.slice(0, lastCursorPos) + event.detail.unicode + text.slice(lastCursorPos);
            lastCursorPos += event.detail.unicode.length; 
            msgInput.setSelectionRange(lastCursorPos, lastCursorPos);
            autoResizeTextarea();
        });

        document.addEventListener('click', (e) => {
            if (!emojiBtn.contains(e.target) && !emojiContainer.contains(e.target)) {
                emojiContainer.classList.add('hidden');
            }
        });

        const fileInput = document.getElementById('fileInput');
        const imgPreviewContainer = document.getElementById('imagePreviewContainer');
        const imgPreview = document.getElementById('imagePreview');
        const fileName = document.getElementById('fileName');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if(file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        imgPreview.src = e.target.result;
                        imgPreviewContainer.classList.remove('hidden'); imgPreviewContainer.classList.add('flex');
                        fileName.textContent = file.name;
                        msgInput.focus();
                    }
                    reader.readAsDataURL(file);
                } else {
                    alert("Lütfen sadece fotoğraf formatında bir dosya seçin."); clearImage();
                }
            }
        });

        window.clearImage = function() {
            fileInput.value = '';
            imgPreviewContainer.classList.add('hidden'); imgPreviewContainer.classList.remove('flex');
            imgPreview.src = '';
        }

        function autoResizeTextarea() {
            msgInput.style.height = 'auto';
            msgInput.style.height = (msgInput.scrollHeight > 112 ? 112 : msgInput.scrollHeight) + 'px';
        }
        msgInput.addEventListener('input', autoResizeTextarea);

        scrollBtn.addEventListener('click', () => {
            chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
            scrollBtn.classList.add('hidden', 'opacity-0', 'translate-y-2');
        });

        chat.addEventListener('scroll', () => {
            if (!isUserReadingOldMessages()) scrollBtn.classList.add('hidden', 'opacity-0', 'translate-y-2');
        });

        msgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!submitBtn.disabled) document.getElementById('form').dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });

        document.getElementById("form").addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            if(!msgInput.value.trim() && fileInput.files.length === 0) return;

            submitBtn.disabled = true;
            sendIcon.classList.add('hidden'); loadingIcon.classList.remove('hidden');
            
            const val = msgInput.value;
            msgInput.value = ""; msgInput.style.height = 'auto'; 
            emojiContainer.classList.add('hidden');
            if(fileInput.files.length > 0) clearImage();

            chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });

            fetch("send_message.php", { method: "POST", body: formData })
            .then(() => load())
            .catch(() => {
                msgInput.value = val;
                alert("İnternet bağlantınızı kontrol edip tekrar deneyin.");
            }).finally(() => {
                submitBtn.disabled = false;
                sendIcon.classList.remove('hidden'); loadingIcon.classList.add('hidden');
                msgInput.focus();
            });
        });

        setInterval(load, 2000);
        load();

        /* ----- SESLİ ARAMA (WEBRTC) VE LOG SİSTEMİ ----- */
        const myPeerId = 'deucritic_user_' + <?= $user_id ?>;
        const friendPeerId = 'deucritic_user_' + <?= $active ?>;
        const ringtone = document.getElementById('ringtoneAudio');
        
        let peer = new Peer(myPeerId);
        let currentCall, localStream, pendingIncomingCall;
        let callTimerInterval = null;
        let callSeconds = 0;

        window.toggleMute = function(isMuted) {
            if (localStream) {
                localStream.getAudioTracks()[0].enabled = !isMuted;
            }
        }

        // CANLI ÇAĞRI SÜRESİ (TIMER) FONKSİYONLARI
        function startCallTimer() {
            callSeconds = 0;
            const timerEl = document.getElementById('callTimerText');
            const statusEl = document.getElementById('callStatusText');
            
            statusEl.classList.add('hidden');
            timerEl.classList.remove('hidden');
            timerEl.textContent = '00:00';

            callTimerInterval = setInterval(() => {
                callSeconds++;
                const mins = String(Math.floor(callSeconds / 60)).padStart(2, '0');
                const secs = String(callSeconds % 60).padStart(2, '0');
                timerEl.textContent = `${mins}:${secs}`;
            }, 1000);
        }

        function stopCallTimer() {
            clearInterval(callTimerInterval);
            document.getElementById('callTimerText').classList.add('hidden');
            document.getElementById('callStatusText').classList.remove('hidden');
            document.getElementById('callStatusText').textContent = 'Aranıyor...';
        }

        peer.on('call', function(call) {
            pendingIncomingCall = call;
            ringtone.play().catch(e=>console.log('Oto-çalma engellendi.'));

            if (Notification.permission === 'granted') {
                new Notification("Gelen Arama", { body: "<?= htmlspecialchars($chatUser['username']) ?> sesli arıyor!", icon: "/gorsel/favicon.ico" });
            }

            document.getElementById('callOverlay').classList.remove('hidden'); document.getElementById('callOverlay').classList.add('flex');
            document.getElementById('callStatusText').textContent = 'Sesli Arama Geliyor...';
            document.getElementById('callStatusText').classList.add('animate-pulse');

            document.getElementById('incomingCallActions').classList.remove('hidden'); document.getElementById('incomingCallActions').classList.add('flex');
            document.getElementById('activeCallActions').classList.add('hidden'); document.getElementById('activeCallActions').classList.remove('flex');
        });

        window.acceptCall = function() {
            if(!pendingIncomingCall) return;
            ringtone.pause(); ringtone.currentTime = 0;

            document.getElementById('callStatusText').textContent = 'Bağlanıyor...';
            document.getElementById('callStatusText').classList.remove('animate-pulse');
            
            document.getElementById('incomingCallActions').classList.add('hidden'); document.getElementById('incomingCallActions').classList.remove('flex');
            document.getElementById('activeCallActions').classList.remove('hidden'); document.getElementById('activeCallActions').classList.add('flex');

            navigator.mediaDevices.getUserMedia({video: false, audio: true})
            .then(function(stream) {
                localStream = stream;
                pendingIncomingCall.answer(stream); 
                currentCall = pendingIncomingCall;
                
                // Zamanlayıcıyı Başlat
                startCallTimer();
                
                currentCall.on('stream', function(remoteStream) {
                    const remoteAudio = document.getElementById('remoteAudio');
                    remoteAudio.srcObject = remoteStream; remoteAudio.play();
                });
                
                currentCall.on('close', function() { closeCallUI(); });
            })
            .catch(function(err) {
                document.getElementById('callStatusText').textContent = 'Mikrofon İzni Gerekli!';
                setTimeout(closeCallUI, 2000);
            });
        }

        window.rejectCall = function() {
            ringtone.pause(); ringtone.currentTime = 0;
            if(pendingIncomingCall) { pendingIncomingCall.close(); pendingIncomingCall = null; }
            
            sendSystemMessage('❌ Cevapsız Sesli Arama');
            closeCallUI();
        }

        window.startRealCall = function(type) {
            document.getElementById('callOverlay').classList.remove('hidden'); document.getElementById('callOverlay').classList.add('flex');
            document.getElementById('callStatusText').textContent = 'Aranıyor...';
            document.getElementById('callStatusText').classList.add('animate-pulse');

            document.getElementById('incomingCallActions').classList.add('hidden'); document.getElementById('incomingCallActions').classList.remove('flex');
            document.getElementById('activeCallActions').classList.remove('hidden'); document.getElementById('activeCallActions').classList.add('flex');
            
            sendSystemMessage('📞 Sesli Arama Başlatıldı');

            navigator.mediaDevices.getUserMedia({video: false, audio: true})
            .then(function(stream) {
                localStream = stream;
                const call = peer.call(friendPeerId, stream);
                currentCall = call;
                
                call.on('stream', function(remoteStream) {
                    // Zamanlayıcıyı Başlat
                    startCallTimer();
                    const remoteAudio = document.getElementById('remoteAudio'); 
                    remoteAudio.srcObject = remoteStream; remoteAudio.play();
                });
                
                call.on('close', function() { closeCallUI(); });
                call.on('error', function(err) { 
                    stopCallTimer();
                    document.getElementById('callStatusText').classList.remove('hidden');
                    document.getElementById('callStatusText').textContent = 'Ulaşılamıyor...'; 
                    setTimeout(closeCallUI, 2000); 
                });
            })
            .catch(function(err) {
                document.getElementById('callStatusText').textContent = 'Mikrofon erişimi reddedildi!'; setTimeout(closeCallUI, 2000);
            });
        }

        window.endRealCall = function() {
            if (currentCall) currentCall.close();
            if (pendingIncomingCall) pendingIncomingCall.close();
            closeCallUI();
        }

        function closeCallUI() {
            stopCallTimer();
            ringtone.pause(); ringtone.currentTime = 0;
            document.getElementById('callOverlay').classList.add('hidden'); document.getElementById('callOverlay').classList.remove('flex');
            
            const remoteAudio = document.getElementById('remoteAudio');
            if(remoteAudio) remoteAudio.srcObject = null;
            if (localStream) localStream.getTracks().forEach(track => track.stop());
            currentCall = null; pendingIncomingCall = null;
        }
    <?php endif; ?>
    </script>
</body>
</html>
