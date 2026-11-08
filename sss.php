<?php
require_once __DIR__ . '/includes/i18n.php';

require_once 'includes/config.php';

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SSS - Deucritic Sözlük</title>
    <link rel="icon" href="/gorsel/favicon.ico?v=20261104" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#b84834',
                        secondary: '#e9c65a',
                    },
                    fontFamily: {
                        sans: ['Inter', 'Arial', 'sans-serif'],
                    }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .text-primary { color: #b84834; }
        .bg-primary { background-color: #b84834; }
        .bg-secondary { background-color: #e9c65a; }
        .accordion { cursor: pointer; transition: background-color 0.2s ease; }
        .accordion-content { display: none; padding: 0.5rem 1rem; border-left: 2px solid #b84834; background-color: #fdfdfd; }
    </style>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="bg-gray-100 min-h-screen">


<header class="bg-primary text-white shadow">
    <div class="container mx-auto p-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-2">
            <img src="/gorsel/deucritic-mark.svg" alt="Logo" class="w-10 h-10">
            <h1 class="text-2xl font-bold">Deucritic Sözlük</h1>
        </a>
        <nav class="flex gap-4">
            <?php if ($is_logged_in): ?>
                <a href="profile.php" class="hover:underline">Profilim</a>
                <span>|</span>
                <a href="logout.php" class="hover:underline">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php" class="hover:underline">Giriş</a>
                <a href="register.php" class="hover:underline">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container mx-auto mt-8 p-6 bg-white rounded-2xl shadow-lg min-h-[60vh]">
    <h2 class="text-3xl font-bold text-primary mb-6">Sıkça Sorulan Sorular (SSS)</h2>

    <?php
    $faqs = [
    ["a" => "Deucritic’e nasıl üye olabilirim?", 
     "c" => "Siteye gir, nick seç, okul postanı gir, şifreni uydur. 'Kayıt ol'a bas. Hop, üyelik."],

    ["a" => "Onep nedir?", 
     "c" => "O konuya dair kafana ilk geleni döktüğün cümlelerdir. Yani bir başlık altına yazılan yorum, görüş veya bilgi."],

    ["a" => "Onay maili gelmedi, napıcam?", 
     "c" => "Önce spam kutusuna bak. Yoksa internet servis sağlayıcın 'bu mail fazla dürüst, ben bunu engelleyeyim' demiş olabilir. Hiç gelmezse bana yaz, sonra nickini tekrar denersin."],
     
     ["a" => "Şifremi unuttum ama değiştiremiyorum.", 
     "c" => "Bilgilerini korumak adına şifre sıfırlama sistemini şimdilik tatildeyiz 😅 Ama endişelenme bro, bize kısa bir mail atarsan sana ışık hızıyla şifre sıfırlama linki göndeririz. Kusura bakma :( "],


    ["a" => "Oneplerim niye görünmüyor?", 
     "c" => "Genellikle hemencecik paylaşabilirsin, paylaşamazsan sistem seni önce bir bekleme odasına atıyor. Onay gelince bir anda hepsi ortaya çıkıyor, sürpriz yumurta gibi. Bekleme odasında da yoksa bana yaz sen aga."],

    ["a" => "Takma adımı değiştirebilir miyim?", 
     "c" => "Evet, değiştirebilirsin. Profil ayarlarına girip yeni bir nick seçerek güncelleyebilirsin. Ancak çok sık değiştirirsen sistem seni uyarabilir."],
     
     
 ["a" => "Rozetlerimi nasıl görebilirim?", 
 "c" => "Kendi profil sayfana giderek kazandığın tüm rozetleri görebilirsin. Rozetlerin üzerine gelerek açıklamalarını da inceleyebilirsin."],
 
 
 ["a" => "Takipçilerimi ve takip ettiklerimi nasıl kontrol edebilirim?", 
 "c" => "Profil sayfanda 'Takipçi' ve 'Takip Ettikleri' bölümlerine tıklayarak detaylı listeyi görebilirsin."],

    ["a" => "Hesabıma giriş yapınca “erişim kapatıldı” diyor. Bu ne demek?", 
     "c" => "Nazikçe söylemek gerekirse: ban yemişsin dostum. Sebebini öğrenmek için mailine bak, 'fuckulte' diye koca bir dosya gelmiş olabilir."],

    ["a" => "Başlık ya da arkadaşımı takip etmek istiyorum ama olmuyor?", 
     "c" => "Taşındığı başlığın yanında yine o meşhur '*' var. Ona bas → eski başlığa dön → 'takip etme'yi seç. Arkadaşını takip edeceksen arkadaşına tıkla → takip et butonuna bas. Eğer hiçbiri yoksa sistemde böyle şeyler henüz gelmemiştir. Az sabret :D"],

    ["a" => "Onep silmek istiyorum, nasıl yaparım?", 
     "c" => "Şu an bizde bir silme butonu yok, yazdığın onepi kesinlikle silmek istersen yaz, o bakar. Kısaca, 'yazdım oldu bitti' mantığı var, ama acil bir durumsa bana yaz."],

    ["a" => "Başlığa resim/gif ekleyebilir miyim?", 
     "c" => "Şu an sadece metin kabul ediyoruz dostum. Ama fikir güzel, ileride belki ekleriz. Onep’lerini güzel anlat, kelimelerle coştur."],


    ["a" => "Nickim çalınır mı, hesabım güvenli mi?", 
     "c" => "Sistemimiz temel güvenlik önlemlerini alıyor ama şifreni paylaşma, basit şifre koyma. Nick bir dövme gibi, çalınmaz ama hesabın şifresiz kalırsa sorun çıkar. Genel olarak kişisel bilgilerine ben dahil kisme erişemez; merak etme :D"],

    ["a" => "Çok fazla spam onep atarsam ne olur?", 
     "c" => "Sistem seni önce yavaşlatır, sonra gerekirse uyarır. Ama merak etme, kimseyi rahatsız etmedikçe kimse banlamaz."],

    ["a" => "Başka üniversiteler de Deucritic kullanabilir mi?", 
     "c" => "Şimdilik DEU öğrencileri için, ama eğer arkadaşın davet ederse veya sistem açılırsa o da katılabilir. Sürpriz olabilir, dikkat! 😎"],

    ["a" => "Onep karakter sınırı nedir?", 
     "c" => "Tek onep 500 karakter. Uzun yazarsan sistem bölüyor, kısa ve öz yazman daha iyi okunur."],
     
     ["a" => "Üyeliğimi silebilir miyim?", 
 "c" => "Evet, ama bunu yapmak geri dönüşü olmayan bir işlemdir. Hesabını silmek için destek ekibine ya da admin'e mail atman gerekiyor."],

["a" => "Profil fotoğrafımı değiştirebilir miyim?", 
 "c" => "Evet, profil sayfandan yeni bir fotoğraf yükleyebilirsin. AnY başlıklarda profil fotoğrafın görünmez, merak etme."],
 
 
 
     
    ["a" => "Onep’imin kimler tarafından görüldüğünü nasıl anlarım?", 
 "c" => "Anonim değilse herkes görür. AnY (anonim) başlıklarda ise sadece başlığı yazan hiçbir şekilde görünmez; diğer kullanıcılar AnY onep yazmış olsa bile, o onep profilinde görünür."]

];

    foreach($faqs as $faq):
    ?>
        <div class="mb-4">
            <button class="accordion w-full text-left font-semibold text-gray-800 p-4 bg-gray-50 rounded-xl shadow hover:bg-gray-100">
                <?= htmlspecialchars($faq['a']) ?>
            </button>
            <div class="accordion-content text-gray-700">
                <?= nl2br(htmlspecialchars($faq['c'])) ?>
            </div>
        </div>
    <?php endforeach; ?>

</main>
<br>

<script>
    const accordions = document.querySelectorAll('.accordion');
    accordions.forEach(btn => {
        btn.addEventListener('click', () => {
            const content = btn.nextElementSibling;
            content.style.display = (content.style.display === 'block') ? 'none' : 'block';
        });
    });
</script>

<?php include 'footer.php'; ?>


</body>
</html>
