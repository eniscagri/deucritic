<?php
$url = isset($_GET['url']) ? $_GET['url'] : '';
if (empty($url)) die('Geçersiz bağlantı.');

$url = urldecode($url);
if (!filter_var($url, FILTER_VALIDATE_URL)) die('Geçersiz URL.');

$ourDomain = 'deucritic.com';
$host = parse_url($url, PHP_URL_HOST);

if (stripos($host, $ourDomain) !== false) {
    header("Location: $url");
    exit;
}

$linkDomain = parse_url($url, PHP_URL_HOST);
$linkScheme = parse_url($url, PHP_URL_SCHEME);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dış Bağlantı Güvenlik Uyarısı</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
.text-primary { color: #b84834; }
</style>
<script>
let countdown = 30;
function updateCountdown() {
    document.getElementById('countdown').innerText = countdown;
    if(countdown-- <= 0) {
        window.location.href = "<?= htmlspecialchars($url, ENT_QUOTES) ?>";
    } else {
        setTimeout(updateCountdown, 1000);
    }
}
document.addEventListener('DOMContentLoaded', updateCountdown);
</script>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="flex items-center justify-center min-h-screen p-4">
<div class="bg-white p-8 rounded-2xl shadow-xl max-w-md text-center">
    <h1 class="text-3xl font-bold text-primary mb-4">⚠ Dış Bağlantı Uyarısı</h1>
    <p class="text-gray-700 mb-4">
        Siz <b><?= htmlspecialchars($linkScheme . '://' . $linkDomain) ?></b> domainine yönlendiriliyorsunuz.<br>
        Bu bağlantı bizim siteye ait değildir. Lütfen dikkatli olun.
    </p>
    <p class="text-gray-500 mb-4">Otomatik yönlendirme <span id="countdown">5</span> saniye içinde gerçekleşecektir.</p>
    <div class="flex flex-col sm:flex-row justify-center gap-4 mt-4">
        <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer" 
           class="px-5 py-3 bg-black text-white rounded-xl font-semibold hover:bg-blue-700 transition">
           Hemen Git
        </a>
        <a href="javascript:history.back()" 
           class="px-5 py-3 bg-gray-300 text-black rounded-xl font-semibold hover:bg-gray-400 transition">
           Geri Dön
        </a>
    </div>
    <p class="text-xs text-gray-400 mt-4">Bu sayfa sizi güvenli şekilde uyarır ve yönlendirir.</p>
</div>
</body>
</html>
