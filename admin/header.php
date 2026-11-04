
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DeuCritic Admin Panel</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
<header class="bg-white shadow sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-bold text-blue-600">DeuCritic Admin</h2>
            <span class="text-sm text-slate-500">— Dashboard</span>
        </div>
        <div class="flex items-center gap-3">
             <a href="index.php" class="px-3 py-2 bg-green-500 text-white rounded">Ana Sayfa</a>
             
             <a href="users.php" class="px-3 py-2 bg-blue-500 text-white rounded">Kullanıcılar</a>
             
             <a href="req.php" class="px-3 py-2 bg-yellow-500 text-white rounded">İstekler</a>
             <a href="header_ip.php" class="px-3 py-2 bg-green-500 text-white rounded">Detaylı IP</a>
             
            <form method="get" class="flex items-center">
                
                 
                <input name="q" value="<?=htmlspecialchars($_GET['q'] ?? '')?>" class="px-3 py-2 border rounded-l" placeholder="Search users...">
                <button class="px-3 py-2 bg-amber-500 text-white rounded-r">Search</button>
            </form>
            <a href="admin_logout.php" class="px-3 py-2 bg-red-500 text-white rounded">Logout</a>
        </div>
    </div>
</header>
<main class="max-w-7xl mx-auto px-6 py-8">
