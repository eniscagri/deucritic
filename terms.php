<?php
require_once __DIR__ . '/includes/i18n.php';

// Dil seçimi
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['tr', 'en'], true) ? $_GET['lang'] : dc_lang();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $lang=='tr' ? 'Kullanım Koşulları' : 'Terms of Use'; ?> | Deucritic</title>
<link rel="icon" href="/gorsel/favicon.ico?v=20261104">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { background: #f4f5f7; font-family: Inter, sans-serif; }
    .active-lang { font-weight: bold; text-decoration: underline; }
    .accordion-button { cursor: pointer; transition: all 0.2s; }
    .accordion-button:hover { text-decoration: underline; }
</style>
<script>
function toggleSection(id){
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
</script>
    <link rel="stylesheet" href="/css/brand.css">
</head>
<body class="min-h-screen flex flex-col">

<div class="max-w-5xl mx-auto px-4 py-10 space-y-8">

    <!-- Başlık ve dil seçimi -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900"><?php echo $lang=='tr' ? 'Kullanım Koşulları' : 'Terms of Use'; ?></h1>
        <div class="flex gap-4" aria-label="Language">
            <a href="?lang=tr" class="<?php echo $lang=='tr'?'active-lang':''; ?> text-blue-600">TR</a>
            <a href="?lang=en" class="<?php echo $lang=='en'?'active-lang':''; ?> text-blue-600">EN</a>
        </div>
    </div>

    <?php if($lang=='tr'): ?>
    <!-- TÜRKÇE KULLANIM KOŞULLARI -->

    <section class="space-y-6">

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('intro')">
                Giriş
            </button>
            <div id="intro" class="mt-2 text-gray-700">
                <p>Deucritic, öğrencilerin akademik ve sosyal eleştiri, fikir paylaşımı ve topluluk etkileşimi yapabildiği bir platformdur. Bu Kullanım Koşulları, Deucritic’in haklarını korumak ve kullanıcı sorumluluklarını belirlemek amacıyla hazırlanmıştır. Platforma erişim sağlayarak bu şartları kabul etmiş olursunuz.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('eligibility')">
                Üyelik ve Uygunluk
            </button>
            <div id="eligibility" class="mt-2 text-gray-700 hidden">
                <p>Deucritic’e üye olabilmek için 18 yaşını doldurmuş olmanız gerekir. Platformu kullanmak, bu şartları ve tüm Kullanım Koşulları’nı kabul ettiğiniz anlamına gelir. Kurum ve şirketler bireysel üye olamaz.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('content')">
                İçerik Kullanımı ve Sorumluluk
            </button>
            <div id="content" class="mt-2 text-gray-700 hidden">
                <ul class="list-disc ml-5 space-y-2">
                    <li>Kullanıcılar paylaştıkları içeriklerden tamamen sorumludur.</li>
                    <li>Deucritic, zararlı, yasa dışı veya hak ihlali içerikleri inceleme ve kaldırma hakkını saklı tutar.</li>
                    <li>Kullanıcılar, platform üzerinden diğer üyeleri taciz, tehdit veya spam ile rahatsız edemez.</li>
                    <li>Tüm içeriklerin telif hakları ve kişilik haklarına uygun olması kullanıcı sorumluluğundadır.</li>
                </ul>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('copyright')">
                Fikri Mülkiyet ve Telif Hakları
            </button>
            <div id="copyright" class="mt-2 text-gray-700 hidden">
                <p>Deucritic üzerinde bulunan yazılım, tasarım, logo ve içerikler Deucritic’e veya lisans verenlerine aittir. Kullanıcılar, Deucritic’in yazılı izni olmadan hiçbir materyali çoğaltamaz veya dağıtamaz. Telif hakları ihlali durumunda Deucritic, gerekli hukuki adımları atma hakkına sahiptir.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('privacy')">
                Gizlilik
            </button>
            <div id="privacy" class="mt-2 text-gray-700 hidden">
                <p>Kullanıcıların kişisel verileri, Deucritic Gizlilik Politikası kapsamında korunur. Platformu kullanmak, bu politika ile uyumlu olmayı kabul etmek anlamına gelir.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('disclaimer')">
                Sorumluluk Reddi
            </button>
            <div id="disclaimer" class="mt-2 text-gray-700 hidden">
                <p>Deucritic, kullanıcıların içeriklerinden, platformun kullanımından veya teknik aksaklıklardan doğabilecek hiçbir kayıp veya zarardan sorumlu değildir. Platform “AS-IS” sunulmaktadır ve herhangi bir sonuç garantisi verilmez.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('liability')">
                Tazminat ve Hukuki Hususlar
            </button>
            <div id="liability" class="mt-2 text-gray-700 hidden">
                <p>Kullanıcı, platform kullanımından kaynaklanan tüm talepler, kayıp veya zararlardan Deucritic’i tazmin etmekle yükümlüdür. Anlaşmazlıklar Türkiye Cumhuriyeti yasalarına tabidir ve İzmir mahkemeleri yetkilidir.</p>
            </div>
        </div>

    </section>

    <?php else: ?>
  

    <section class="space-y-6">

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('intro_en')">
                Introduction
            </button>
            <div id="intro_en" class="mt-2 text-gray-700">
                <p>Deucritic is a platform for students to share ideas, interact and engage in academic and social critique. These Terms of Use protect Deucritic’s rights and set user obligations. Accessing the platform indicates acceptance of these Terms.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('eligibility_en')">
                Eligibility
            </button>
            <div id="eligibility_en" class="mt-2 text-gray-700 hidden">
                <p>To register, you must be at least 18 years old. Use of the platform indicates your acceptance of these terms. Membership is limited to individual users; organizations cannot register.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('content_en')">
                Content Use & Responsibility
            </button>
            <div id="content_en" class="mt-2 text-gray-700 hidden">
                <ul class="list-disc ml-5 space-y-2">
                    <li>Users are fully responsible for content they post.</li>
                    <li>Deucritic reserves the right to remove content that violates laws, rights, or is harmful.</li>
                    <li>Users cannot harass, threaten, or spam other members.</li>
                    <li>All content must comply with copyright and privacy laws.</li>
                </ul>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('copyright_en')">
                Intellectual Property & Copyright
            </button>
            <div id="copyright_en" class="mt-2 text-gray-700 hidden">
                <p>All content, logos, designs, and code on Deucritic are the property of Deucritic or its licensors. Users may not reproduce or distribute without written permission. Deucritic reserves legal rights in case of copyright infringement.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('privacy_en')">
                Privacy
            </button>
            <div id="privacy_en" class="mt-2 text-gray-700 hidden">
                <p>User data is protected according to Deucritic Privacy Policy. Using the platform constitutes agreement with the policy.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('disclaimer_en')">
                Disclaimers
            </button>
            <div id="disclaimer_en" class="mt-2 text-gray-700 hidden">
                <p>Deucritic is not liable for content, platform usage or technical issues. The platform is provided "AS-IS" and no guarantees are made.</p>
            </div>
        </div>

        <div>
            <button class="accordion-button w-full text-left text-xl font-semibold text-gray-800" onclick="toggleSection('liability_en')">
                Indemnity & Legal
            </button>
            <div id="liability_en" class="mt-2 text-gray-700 hidden">
                <p>Users agree to indemnify Deucritic from all claims, losses or damages arising from use. Disputes are governed by Turkish law and Izmir courts have jurisdiction.</p>
            </div>
        </div>

    </section>

    <?php endif; ?>

   

    <?php include 'footer.php'; ?>

</div>

</body>
</html>
