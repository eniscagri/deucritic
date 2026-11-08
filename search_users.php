<?php
require_once __DIR__ . '/includes/i18n.php';

include 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    die();
}

$user_id = $_SESSION['user_id'];

// ÇÖZÜM: FILTER_SANITIZE_STRING yerine htmlspecialchars veya doğrudan filtreleme kullanılır
$search_query = isset($_GET['q']) ? trim((string)$_GET['q']) : ''; 

$users = [];
if ($search_query !== '') {
    // Kullanıcı adına göre arama yap (Kendini listeleme)
    $stmt = $pdo->prepare("SELECT id, username, profile_photo, isverified, school FROM users WHERE username LIKE ? AND id != ? LIMIT 10");
    $stmt->execute(['%' . $search_query . '%', $user_id]);
    $users = $stmt->fetchAll();
}

/**
 * Yeni sohbet başlatma mantığı[cite: 2]
 */
if (isset($_GET['start_chat'])) {
    $target_user = (int)$_GET['start_chat'];

    // Aralarında zaten bir konuşma var mı kontrol et[cite: 2]
    $check = $pdo->prepare("
        SELECT cu1.conversation_id 
        FROM conversation_users cu1
        JOIN conversation_users cu2 ON cu1.conversation_id = cu2.conversation_id
        WHERE cu1.user_id = ? AND cu2.user_id = ?
    ");
    $check->execute([$user_id, $target_user]);
    $existing = $check->fetch();

    if ($existing) {
        $convo_id = $existing['conversation_id'];
    } else {
        // Yeni konuşma oluştur[cite: 2]
        $pdo->beginTransaction();
        try {
            $pdo->prepare("INSERT INTO conversations (last_message, last_message_at) VALUES ('Sohbet başladı', NOW())")->execute();
            $convo_id = $pdo->lastInsertId();

            $ins = $pdo->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (?, ?)");
            $ins->execute([$convo_id, $user_id]);
            $ins->execute([$convo_id, $target_user]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Hata oluştu: " . $e->getMessage());
        }
    }

    header("Location: dm.php?user=" . $target_user);
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Sohbet Başlat | Deucritic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="bg-slate-50 min-h-screen">

    <div class="max-w-2xl mx-auto min-h-screen bg-white shadow-sm border-x border-slate-100 flex flex-col">
        
        <header class="p-6 border-b border-slate-50 flex items-center gap-4 sticky top-0 bg-white/90 backdrop-blur-md z-10">
            <a href="dm.php" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Yeni Sohbet</h1>
        </header>

        <div class="p-6 flex-1">
            <form action="" method="GET" class="relative group">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                <!-- ÇÖZÜM: value kısmındaki null kontrolü eklendi[cite: 1, 2] -->
                <input type="text" name="q" value="<?= htmlspecialchars($search_query ?? '') ?>" 
                       placeholder="Kullanıcı adı yaz..." 
                       class="w-full bg-slate-100 border-none rounded-2xl py-4 pl-12 pr-4 outline-none focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all text-[15px]"
                       autoFocus autocomplete="off">
            </form>

            <div class="mt-8 space-y-3">
                <?php if ($search_query !== ''): ?>
                    <?php if ($users): ?>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest px-2 mb-4">Bulunan Sonuçlar</p>
                        <?php foreach ($users as $u): ?>
                            <?php $av = !empty($u['profile_photo']) ? $u['profile_photo'] : 'https://ui-avatars.com/api/?background=6366f1&color=fff&name='.urlencode($u['username']); ?>
                            <a href="?start_chat=<?= $u['id'] ?>" class="flex items-center gap-4 p-4 rounded-2xl hover:bg-indigo-50 transition-all border border-transparent hover:border-indigo-100 group">
                                <img src="<?= $av ?>" class="w-12 h-12 rounded-full object-cover shadow-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1">
                                        <h3 class="font-bold text-slate-800 group-hover:text-indigo-700"><span data-dc-user-content><?= htmlspecialchars($u['username'] ?? '') ?></span></h3>
                                        <?php if($u['isverified']): ?>
                                            <i class="fa-solid fa-circle-check text-blue-500 text-[12px]"></i>
                                        <?php endif; ?>
                                    </div>
                                    <p data-dc-user-content class="text-[13px] text-slate-500 truncate"><?= htmlspecialchars($u['school'] ?? 'Dokuz Eylül Üniversitesi') ?></p>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <i class="fa-solid fa-comment-dots text-sm"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-20">
                            <p class="text-slate-500 font-medium text-[15px]">Aradığın kişiyi bulamadık...</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-20">
                        <i class="fa-solid fa-users text-4xl text-slate-100 mb-4"></i>
                        <p class="text-slate-400 text-sm">Mesajlaşmak istediğin arkadaşının kullanıcı adını yazabilirsin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
