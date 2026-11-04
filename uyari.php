<?php
// uyari.php

// Sakıncalı konular listesi: Türkiye + Dünya + Genel
$sakincali_konular = [
    "15 Temmuz", "deprem", "trafik kazası", "okul kazası",
    "terör saldırısı", "cinayet", "şiddet",
    "ırk", "etnik köken", "din", "mezhep", "cinsiyet", "engellilik",
    "11 Eylül", "savaş", "katliam", "soykırım", "Holokost", "Ruanda",
    "doğal afet", "tsunami", "kasırga", "cinsel istismar", "çocuk hakları",
    "ırkçılık", "cinsel yönelim", "ayrımcılık", "din üzerinden hakaret",
    "tehdit", "taciz", "zorbalık", "yasa dışı içerik", "uyuşturucu",
    "korsan paylaşım", "pornografi", "aşırı cinsel içerik"
];

// Kullanıcının gönderisini al (örnek GET ile)
$gonderi = $_GET['gonderi'] ?? '';
$uyari_mesaji = "";
foreach ($sakincali_konular as $konu) {
    if (stripos($gonderi, $konu) !== false) {
        $uyari_mesaji = "Uyarı: Gönderinizde sakıncalı bir konu tespit edildi: <strong>$konu</strong>. deucritic kuralları gereği bu tür paylaşımlar yasaktır.";
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>deucritic Kurallar Uyarısı</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px);}
    to { opacity: 1; transform: translateY(0);}
}
.animate-fadeInUp { animation: fadeInUp 0.5s ease-out; }
</style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<!-- Modal -->
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 animate-fadeInUp relative">
        
        <!-- Kapat Butonu -->
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Başlık ve İkon -->
        <div class="flex flex-col items-center text-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14"/>
            </svg>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Dikkat!</h1>
            
        </div>

        <!-- Kurallar -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg text-gray-700">
                <h2 class="font-semibold mb-2">Önemli Kurallar:</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Trajik olaylar, kazalar, şiddet ve suç içerikleri paylaşmayın.</li>
                    <li>Irk, etnik köken, din, mezhep, cinsiyet, engellilik veya cinsel yönelim üzerinden hakaret yasaktır.</li>
                    <li>Kişisel saldırılar, tehdit ve taciz içerikleri yasaktır.</li>
                </ul>
            </div>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg text-gray-700">
                <h2 class="font-semibold mb-2">Genel Kurallar:</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Paylaşımlar yapıcı, saygılı ve tartışmaya açık olmalıdır.</li>
                    <li>Sakıncalı içerik tespit edilirse uyarı verilir veya içerik kaldırılır.</li>
                    <li>Yasa dışı, pornografik veya aşırı cinsel içerikler paylaşmayın.</li>
                </ul>
            </div>
        </div>

        <!-- Geri Dön Butonu -->
        <a href="javascript:history.back()" class="block w-full text-center px-6 py-4 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition mb-4">
            Geri Dön ve Düzenle
        </a>

        <!-- Alt Not -->
        <p class="text-gray-500 text-sm text-center">
            deucritic, herkes için güvenli ve saygılı bir tartışma ortamı sunmayı hedefler. Kurallara uymanız platformun sağlıklı işleyişi için önemlidir.
        </p>

    </div>
</div>

<script>
function closeModal() {
    document.querySelector('.fixed').style.display = 'none';
}
</script>

</body>
</html>