<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$errors = []; $success = false;
$username = ''; $email = ''; $recovery_email = ''; $school = ''; $grade = '';

// AKILLI DEÜ MAİL DOĞRULAMA FONKSİYONU
function validate_deu_email($email) {
    $email = trim(strtolower($email));
    
    // Uzantı kontrolü
    if (!preg_match('/@(ogr\.)?deu\.edu\.tr$/', $email)) return false;
    
    $local = explode('@', $email)[0];
    
    // Durum 1: Sadece Numara ise (Örn: 2020123456)
    if (ctype_digit($local)) {
        if (!preg_match('/^20\d{8}$/', $local)) return false;
        $num = substr($local, 2);
        if (preg_match('/01234567|12345678|23456789/', $num)) return false;
        if (preg_match('/98765432|87654321|76543210/', $num)) return false;
        if (preg_match('/^(\d)\1{7}$/', $num)) return false;
        return true;
    }
    
    // Durum 2: İsim.Soyisim formatı ise
    if (preg_match('/^[a-z0-9]+[._-][a-z0-9._-]+$/', $local)) {
        return true;
    }
    
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Geçersiz istek.';
    }
    
    $username = trim($_POST['username'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $recovery_email = trim($_POST['recovery_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $school = trim($_POST['school'] ?? '');
    $grade = trim($_POST['grade_level'] ?? '');

    if (!$username || !$email || !$password || !$password2) {
        $errors[] = 'Lütfen tüm temel alanları doldur.';
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = 'Takma ad 3-20 karakter ve uygun formatta olmalı.';
    }
    
    if (!validate_deu_email($email)) {
        $errors[] = 'Geçerli bir DEÜ öğrenci e-postası gir.';
    }
    
    $is_numeric_email = false;
    if ($email) {
        $local_part = explode('@', $email)[0];
        $is_numeric_email = ctype_digit($local_part);
    }

    if ($is_numeric_email) {
        if (empty($recovery_email)) {
            $errors[] = 'Öğrenci numarası kullandığın için yedek bir iletişim e-postası girmelisin.';
        } elseif (!filter_var($recovery_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir iletişim e-postası gir (Örn: adin@gmail.com).';
        } elseif ($email === $recovery_email) {
            $errors[] = 'İletişim e-postan ile okul e-postan aynı olamaz.';
        }
    } else {
        $recovery_email = $email;
    }

    if (strlen($password) < 8) {
        $errors[] = 'Şifre en az 8 karakter olmalı.';
    }
    
    if ($password !== $password2) {
        $errors[] = 'Şifreler uyuşmuyor.';
    }

    // Veritabanı Kayıt İşlemi
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, recovery_email FROM users WHERE email = :email OR username = :username OR recovery_email = :recovery_email");
            $stmt->execute(['email' => $email, 'username' => $username, 'recovery_email' => $recovery_email]);
            $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($existing) {
                foreach ($existing as $row) {
                    if ($row['email'] === $email) $errors[] = 'Bu okul e-postası zaten kayıtlı.';
                    if ($row['username'] === $username) $errors[] = 'Bu takma ad zaten alınmış.';
                    if ($row['recovery_email'] === $recovery_email && $is_numeric_email) $errors[] = 'Bu iletişim e-postası kullanımda.';
                }
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, recovery_email, password_hash, school, grade_level, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$username, $email, $recovery_email, $hash, $school, $grade]);

                $_SESSION['user_id'] = $pdo->lastInsertId();
                unset($_SESSION['csrf_token']);
                
                header('Location: welcome.php'); 
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Bir hata oluştu, lütfen tekrar dene.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, viewport-fit=cover">
    <title>Kayıt Ol | Deucritic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#44b5d5', secondary: '#f3cc8b', dark: '#1e293b' }
                }
            }
        };
        function togglePassword(id, btnIconId){ 
            const i = document.getElementById(id); 
            const icon = document.getElementById(btnIconId);
            if (i.type === "password") {
                i.type = "text";
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10a9.963 9.963 0 012.175-6.125M21.825 13.875A9.963 9.963 0 0122 12c0-5.523-4.477-10-10-10-1.78 0-3.44.48-4.875 1.325 M15 12a3 3 0 11-6 0 3 3 0 016 0z M3 3l18 18" />';
            } else {
                i.type = "password";
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
    <style> 
        body{ font-family:'Inter', sans-serif; -webkit-tap-highlight-color: transparent; } 
        input, select, textarea { font-size: 16px !important; }
        .animate-bounce-slow { animation: bounce 4s infinite; }
        @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }
        /* JS ile gizleme/gösterme efekti için */
        #recoveryEmailDiv { transition: all 0.4s ease-in-out; overflow: hidden; }
    </style>
    <link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon" />
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-3 md:p-6">

<div class="flex flex-col md:flex-row w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden my-4">

    <div class="hidden md:flex md:w-5/12 bg-gradient-to-br from-primary/20 via-secondary/20 to-primary/10 items-center justify-center p-8 relative overflow-hidden">
        <div class="absolute -top-16 -left-16 w-32 h-32 bg-primary/30 rounded-full animate-bounce-slow"></div>
        <div class="relative z-10 text-center">
            <h2 class="text-3xl font-extrabold text-dark mb-4">Topluluğa Katıl!</h2>
            <p class="text-slate-600 mb-8 font-medium">DEÜ'nün özgür platformunda yerini al.</p>
            <img src="/gorsel/register.png" alt="Welcome" class="w-full h-auto object-contain drop-shadow-xl">
        </div>
    </div>

    <div class="w-full md:w-7/12 p-6 sm:p-10">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-primary mb-1 text-center">🚀 Kayıt Ol</h1>
        <p class="text-center text-slate-500 mb-6 text-sm font-medium">Sadece DEÜ öğrencilerine özel.</p>

        <?php if($errors): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-6 text-sm font-medium">
            <ul class="list-disc list-inside">
                <?php foreach($errors as $err): ?> <li><?= htmlspecialchars($err) ?></li> <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5" id="regForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1">Kullanıcı Bilgileri</label>
                    <input type="text" name="username" placeholder="Takma Adın" value="<?= htmlspecialchars($username) ?>"
                        class="w-full p-3.5 rounded-2xl border-2 border-slate-100 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Şifre"
                            class="w-full p-3.5 rounded-2xl border-2 border-slate-100 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition pr-12" required>
                        <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute right-0 top-0 h-full w-12 flex items-center justify-center text-slate-300 hover:text-primary">
                            <svg id="eyeIcon1" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" id="password2" name="password2" placeholder="Şifre Tekrar"
                            class="w-full p-3.5 rounded-2xl border-2 border-slate-100 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition pr-12" required>
                        <button type="button" onclick="togglePassword('password2', 'eyeIcon2')" class="absolute right-0 top-0 h-full w-12 flex items-center justify-center text-slate-300 hover:text-primary">
                            <svg id="eyeIcon2" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1">E-Posta</label>
                    <input type="email" id="schoolEmailInput" name="email" placeholder="2020123456 veya isim.soyisim@ogr.deu.edu.tr" value="<?= htmlspecialchars($email) ?>"
                        class="w-full p-3.5 rounded-2xl border-2 border-slate-100 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 transition bg-slate-50/50" required>
                    <p id="emailError" class="text-[11px] text-red-500 hidden mt-1 font-bold">Öğrenci numarası veya isim.soyisim formatında okul mailinizi girin.</p>
                </div>

                <div id="recoveryEmailDiv" class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100" style="display: none;">
                    <label class="block text-sm font-bold text-blue-900 mb-1">Şifre Kurtarma E-postası</label>
                    <input type="email" id="recoveryEmailInput" name="recovery_email" placeholder="adin@gmail.com" value="<?= htmlspecialchars($recovery_email) ?>"
                        class="w-full p-3 rounded-xl border-2 border-blue-200 focus:border-blue-500 focus:outline-none transition">
                    <p class="text-[11px] text-blue-500 mt-1">Numaralı DEÜ maillerine dışarıdan ileti gitmez. Şifreni unutursan linki bu kişisel adresine göndereceğiz.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <select name="school" id="department" class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary">
    <option disabled selected value="">Bölüm seçiniz</option>
    <?php
    $faculties = [
        "Eğitim Fakültesi" => [
            "Almanca Öğretmenliği",
            "Bilgisayar ve Öğretim Teknolojileri Öğretmenliği",
            "Biyoloji Öğretmenliği",
            "Biyoloji Öğretmenliği (KKTC Uyruklu)",
            "Coğrafya Öğretmenliği",
            "Coğrafya Öğretmenliği (KKTC Uyruklu)",
            "Fen Bilgisi Öğretmenliği",
            "Fizik Öğretmenliği",
            "Fransızca Öğretmenliği",
            "İlköğretim Matematik Öğretmenliği",
            "İngilizce Öğretmenliği (İngilizce)",
            "Matematik Öğretmenliği",
            "Okul Öncesi Öğretmenliği",
            "Özel Eğitim Öğretmenliği",
            "Özel Eğitim Öğretmenliği (KKTC Uyruklu)",
            "Sınıf Öğretmenliği",
            "Sosyal Bilgiler Öğretmenliği",
            "Türkçe Öğretmenliği",
            "Türk Dili ve Edebiyatı Öğretmenliği",
            "Tarih Öğretmenliği",
            "Rehberlik ve Psikolojik Danışmanlık",
        ],

        "Denizcilik Fakültesi" => [
            "Deniz Ulaştırma İşletme Mühendisliği (İngilizce)",
            "Gemi Makineleri İşletme Mühendisliği (İngilizce)",
            "Gemi Makineleri İşletme Mühendisliği (İngilizce) (KKTC Uyruklu)",
            "Lojistik Yönetimi (İngilizce)",
        ],

        "Fen-Edebiyat Fakültesi" => [
            "Arkeoloji",
            "Bilgisayar Bilimleri",
            "Biyoloji",
            "Biyoloji (UOLP-Gence Devlet Üniversitesi) (Ücretli)",
            "Dilbilimi",
            "Felsefe",
            "Fizik",
            "Tarih",
            "Tarih Öğretmenliği",
            "Karşılaştırmalı Edebiyat",
            "İngilizce Mütercim ve Tercümanlık",
            "İstatistik",
            "Karşılaştırmalı Edebiyat",
            "Kimya",
            "Kimya (KKTC UYRUKLU)",
            "Müzecilik",
            "Müzecilik (KKTC UYRUKLU)",
            "Dilbilimi",
            "Sosyoloji",
            "Türk Dili ve Edebiyatı",
            "Türk Dili ve Edebiyatı Öğretmenliği",
            "Rus Dili ve Edebiyatı (Rusça)",
            "Amerikan Kültürü ve Edebiyatı (İngilizce)",
            "Psikoloji",
            "Sosyoloji",
            "Matematik (İngilizce)"

        ],
        "Mühendislik Fakültesi" => [
            "Bilgisayar Mühendisliği (İngilizce)",
            "Elektrik-Elektronik Mühendisliği (İngilizce)",
            "Endüstri Mühendisliği",
            "İnşaat Mühendisliği",
            "Makine Mühendisliği",
            "Maden Mühendisliği",
            "Mekatronik Mühendisliği",
            "Metalurji ve Malzeme Mühendisliği",
            "Tekstil Mühendisliği",
            "Jeofizik Mühendisliği",
            "Jeoloji Mühendisliği",
            "Gemi Makineleri İşletme Mühendisliği (İngilizce)",
            "Gemi Makineleri İşletme Mühendisliği (İngilizce) (KKTC Uyruklu)",
            "Deniz Ulaştırma İşletme Mühendisliği (İngilizce)",
            "Çevre Mühendisliği"
        ],
        "İktisadi ve İdari Bilimler Fakültesi ve İşletme Fakültesi" => [
            "İktisat",
            "İktisat (İngilizce)",
            "İktisat (İngilizce) (UOLP-SUNY Albany) (Ücretli)",
            "Ekonometri",
            "Ekonometri (KKTC UYRUKLU)",
            "İşletme",
            "İşletme (İngilizce)",
            "İşletme (İngilizce) (UOLP-SUNY Albany) (Ücretli)",
            "Maliye",
            "Uluslararası Ticaret",
            "Uluslararası Ticaret ve İşletmecilik (İngilizce)",
            "Çalışma Ekonomisi ve Endüstri İlişkileri",
            "Siyaset Bilimi ve Uluslararası İlişkiler (İngilizce)",
            "Siyaset Bilimi ve Uluslararası İlişkiler (UOLP-SUNY Albany) (Ücretli)",
            "Kamu Yönetimi",
            "Yönetim Bilişim Sistemleri",
            "Turizm İşletmeciliği (İngilizce)"
        ],
       
        "Güzel Sanatlar ve Tasarım" => [
            "Fotoğraf",
            "Fotoğraf (KKTC UYRUKLU)",
            "Kurgu, Ses ve Görüntü Yönetimi",
            "Kurgu, Ses ve Görüntü Yönetimi (KKTC Uyruklu)",
            "Film Tasarımı ve Yazarlığı",
            "Film Tasarımı ve Yazarlığı (KKTC Uyruklu)",
            "Film Tasarımı ve Yönetmenliği",
            "Film Tasarımı ve Yönetmenliği (KKTC UYRUKLU)"
        
        ],
        "Mimarlık ve Şehircilik" => [
            "Mimarlık",
            "Şehir ve Bölge Planlama"
        ],
        "Turizm ve Gastronomi" => [
            "Gastronomi ve Mutfak Sanatları ",
            "Gastronomi ve Mutfak Sanatları (Uygulamalı Bilimler Y.O.)",
            "Turizm İşletmeciliği",
             "Turizm İşletmeciliği (Uygulamalı Bilimler Y.O.)",
            "Turizm İşletmeciliği (KKTC Uyruklu)",
            "Uluslararası Ticaret"
        ],
         "Bergama Meslek Yüksekokulu" => [
            "Bilgisayar Teknolojisi",
            "İş Makineleri Operatörlüğü",
            "İş Sağlığı ve Güvenliği"
        ],
        "Efes Meslek Yüksekokulu" => [
            "Gıda Teknolojisi",
            "Kültürel Miras ve Turizm",
            "Sivil Hava Ulaştırma İşletmeciliği",
            "Sivil Hava Ulaştırma İşletmeciliği (KKTC Uyruklu)",
            "Sivil Havacılık Kabin Hizmetleri",
            "Turizm ve Otel İşletmeciliği"
        ],
        "İzmir Meslek Yüksekokulu" => [
            "Bilgisayar Programcılığı",
            "Bilgisayar Programcılığı (Uzaktan Öğretim)",
            "Biyomedikal Cihaz Teknolojisi",
            "Biyomedikal Cihaz Teknolojisi (KKTC Uyruklu)",
            "Büro Yönetimi ve Yönetici Asistanlığı",
            "Döküm",
            "Elektrik",
            "Elektrik (KKTC Uyruklu)",
            "Elektronik Teknolojisi",
            "Elektronik Haberleşme Teknolojisi",
            "Harita ve Kadastro",
            "İnşaat Teknolojisi",
            "İklimlendirme ve Soğutma Teknolojisi",
            "Kimya Teknolojisi",
            "Kuyumculuk ve Takı Tasarımı ",
            "Makine",
            "Makine Resim ve Konstrüksiyonu",
            " Mekatronik ",
            "Tekstil Teknolojisi ",
            "Yapı Tesisat Teknolojisi",
            "Muhasebe ve Vergi Uygulamaları",
            "Pazarlama",
            "Sağlık Kurumları İşletmeciliği",
            "Endüstriyel Otomasyon",
            "İnşaat Teknolojisi",
            "Lojistik",
            "Makine",
            "Muhasebe ve Vergi Uygulamaları",
            "Pazarlama",
            "Radyo ve Televizyon Programcılığı",
            "Tıbbi Dokümantasyon ve Sekreterlik",
            "Turizm ve Otel İşletmeciliği",
            "Yerel Yönetimler"
        ],
        "Diğer Bölümler" => [
            "Diş Hekimliği",
            "Ağız ve Diş Sağlığı",
            "Hemşirelik",
            "Tıp",
            "Hukuk",
            "İlahiyat",
            "İlahiyat (M.T.O.K.)",
             "Fizyoterapi ve Rehabilitasyon",
            "Mimarlık",
            "Şehir ve Bölge Planlama ",
            "Teknikerlik",
            "Nükleer Tıp Teknikleri",
            "Nükleer Tıp Teknikleri (KKTC UYRUKLU)",
            "Odyometri",
            "Radyoterapi",
            "Tıbbi Görüntüleme Teknikleri",
            "Tıbbi Görüntüleme Teknikleri (KKTC UYRUKLU)",
            "Tıbbi Laboratuvar Teknikleri",
            "Doğal Yapı Taşları Teknolojisi",
            "Endüstriyel Cam ve Seramik",
            "Geoteknik",
            "Kaynak Teknolojisi",
            "Madencilik Teknolojisi",
            "Sondaj Teknolojisi",
            "Diğer"
        ]
    ];

    foreach ($faculties as $faculty => $departments) {
        echo "<optgroup label=\"" . htmlspecialchars($faculty, ENT_QUOTES) . "\">";
        foreach ($departments as $department) {
            $selected = (isset($school) && $school === $department) ? 'selected' : '';
            echo "<option value=\"" . htmlspecialchars($department, ENT_QUOTES) . "\" $selected>$department</option>";
        }
        echo "</optgroup>";
    }
    ?>
</select>
                <select name="grade_level" class="w-full p-3.5 rounded-2xl border-2 border-slate-100 focus:border-primary focus:outline-none bg-white transition appearance-none cursor-pointer">
                    <option disabled selected value="">Sınıfın</option>
                    <option value="hazirlik">Hazırlık</option>
                    <?php for ($i = 1; $i <= 4; $i++): ?> <option value="<?= $i ?>"><?= $i ?>. Sınıf</option> <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-primary text-white p-4 rounded-2xl font-extrabold text-lg hover:bg-[#3ba2c0] hover:-translate-y-1 hover:shadow-lg transition-all active:scale-95">
                Hesabımı Oluştur 🚀
            </button>
        </form>

        <p class="text-center text-sm mt-6 text-slate-500 font-medium">
            Zaten üye misin? <a href="login.php" class="text-primary font-bold hover:underline">Giriş Yap</a>
        </p>
    </div>
</div>

<script>
    const emailInput = document.getElementById('schoolEmailInput');
    const emailError = document.getElementById('emailError');
    const recoveryDiv = document.getElementById('recoveryEmailDiv');
    const recoveryInput = document.getElementById('recoveryEmailInput');

    function checkEmailType() {
        const value = emailInput.value.toLowerCase().trim();
        const regex = /^((20\d{8})|([a-z0-9]+[._-][a-z0-9._-]+))@(ogr\.)?deu\.edu\.tr$/;
        
        if (value && !regex.test(value)) {
            emailError.classList.remove("hidden");
            emailInput.classList.add("border-red-300");
        } else {
            emailError.classList.add("hidden");
            emailInput.classList.remove("border-red-300");
        }

        if (value && regex.test(value)) {
            const localPart = value.split('@')[0];
            const isNumeric = /^\d+$/.test(localPart);

            if (isNumeric) {
                recoveryDiv.style.display = 'block';
                recoveryInput.setAttribute('required', 'required');
            } else {
                recoveryDiv.style.display = 'none';
                recoveryInput.removeAttribute('required');
                recoveryInput.value = ''; // İçini temizle
            }
        } else {
            // Hatalıysa veya boşsa varsayılan olarak gizle
            recoveryDiv.style.display = 'none';
            recoveryInput.removeAttribute('required');
        }
    }

    emailInput.addEventListener('input', checkEmailType);
    
    // Sayfa ilk yüklendiğinde eski veriler (hata sonrası) varsa kontrol et
    window.addEventListener('DOMContentLoaded', checkEmailType);
</script>

</body>
</html>