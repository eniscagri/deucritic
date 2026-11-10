<?php
require_once __DIR__ . '/includes/i18n.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hikaye - Kaldırıldı</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-lg border border-slate-200 p-8 max-w-md text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-2xl">🚫</div>
        <h1 class="text-2xl font-black text-slate-800">Hikaye özelliği kaldırıldı</h1>
        <p class="mt-3 text-sm text-slate-600">Bu bölüm artık aktif değil. Ana sayfaya dönmek için lütfen geri gidin.</p>
        <a href="/index.php" class="mt-6 inline-block rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700">Ana Sayfaya Dön</a>
    </div>
</body>
</html>
