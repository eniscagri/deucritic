<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$errors = []; $success = false;
$username = ''; $email = ''; $school = ''; $grade = '';




function validate_deu_email($email) {
    $email = trim(strtolower($email));
    $pattern = '/^20(\d{2})(\d{3})(\d{1,3})@(ogr\.deu\.edu\.tr|deu\.edu\.tr)$/i';
    if (!preg_match($pattern, $email, $matches)) return false;
    $localPart = explode('@', $email)[0];
    if (!preg_match('/^[0-9]+$/', $localPart)) return false;
    $year = intval($matches[1]); $bolum = intval($matches[2]); $sira = intval($matches[3]);
    return ($bolum >= 200 && $bolum <= 999 && $sira >= 0 && $sira <= 999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) $errors[] = 'Geçersiz istek. Hile yapma!';
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $school = trim($_POST['school'] ?? '');
    $grade = trim($_POST['grade_level'] ?? '');
    $bio = trim($_POST['bio'] ?? '');


    if (!$username || !$email || !$password || !$password2) $errors[] = 'Bütün alanları doldur, cimri olma!';
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors[] = 'Takma ad 3-20 karakter arası olmalı, süper kahraman isimleri geçerli değil 😎';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !validate_deu_email($email)) $errors[] = 'DEU e-postanı doğru gir, yoksa kapıda bekler kalırsın.';
    if (strlen($password) < 6) $errors[] = 'Şifre en az 6 karakter olmalı, "123" yetmez 🥲';
    if ($password !== $password2) $errors[] = 'Şifreler uyuşmuyor, lütfen birbirine küsmesinler 😅';
    if (!empty($grade) && (!is_numeric($grade) || intval($grade)<1 || intval($grade)>7)) $errors[] = 'Sınıf 1-7 arası olmalı, 8. boyut şimdilik yok.';

    if (empty($errors)) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = :email OR username = :username");
        $stmt->execute(['email'=>$email, 'username'=>$username]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['email'] === $email) $errors[] = 'Bu e-posta zaten kayıtlı, başka adres dene.';
            if ($existing['username'] === $username) $errors[] = 'Bu takma ad zaten kullanılıyor, biraz yaratıcı ol! 🤔';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, school, grade_level, bio) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hash, $school, $grade, $bio]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            unset($_SESSION['csrf_token']);
            $success = true;
            header('Location: index.php?register=success'); 
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = 'Hata oluştu, tekrar deneyin. Kodları yakaladık, merak etme 😉';
    }
}

}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kayıt Ol - Deucritic Sözlük</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {theme:{extend:{colors:{primary:'#44b5d5',secondary:'#f3cc8b'}}}};
function togglePassword(id){ const i=document.getElementById(id); i.type=i.type==="password"?"text":"password"; }
</script>
<style> body{font-family:'Arial',sans-serif;} </style>
   <link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon" />
  <link rel="apple-touch-icon" sizes="180x180" href="/gorsel/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/gorsel/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/gorsel/favicon-16x16.png">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

<div class="flex flex-col md:flex-row w-full max-w-6xl bg-white rounded-3xl shadow-xl overflow-hidden">

    <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-primary/20 via-secondary/10 to-primary/10 items-center justify-center p-8 relative">
        <div class="absolute -top-16 -left-16 w-32 h-32 bg-primary/30 rounded-full animate-bounce-slow"></div>
        <div class="absolute -bottom-16 -right-16 w-36 h-36 bg-secondary/20 rounded-full animate-bounce-slow"></div>
        <img src="/gorsel/register.png" alt="Hoşgeldin!" class="w-full h-auto object-contain">
       
    </div>

    <div class="w-full md:w-1/2 p-10 relative">
        <h1 class="text-3xl font-extrabold text-primary mb-2 text-center">🚀 DeuCritic’e Katıl!</h1>
        <p class="text-center text-gray-600 mb-6 text-sm">
Dersler sıkıcı olabilir, ama biz değiliz...</p>


        <?php if($errors): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
            <ul class="list-disc list-inside">
            <?php foreach($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center text-sm">Kayıt başarılı! Yönlendiriliyorsunuz...</div>
        <?php endif; ?>

        <form method="POST" class="space-y-4" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <input type="text" name="username" placeholder="Takma Adın" value="<?= htmlspecialchars($username) ?>"
                class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary" required>

            <input type="email" name="email" placeholder="20201234567@ogr.deu.edu.tr" value="<?= htmlspecialchars($email) ?>"
                class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary" required>

            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Şifre (min 6 karakter)"
                    class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary" required>
                <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">👁️</button>
            </div>

            <div class="relative">
                <input type="password" id="password2" name="password2" placeholder="Şifreyi tekrar gir"
                    class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary" required>
                <button type="button" onclick="togglePassword('password2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">👁️</button>
            </div>

          <select name="school" id="department" class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary">
    <option disabled selected value="">Bölüm seçiniz</option>
    <?php
    $faculties = [
        "Eğitim Fakültesi" => [
            "Almanca Öğretmenliği",
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
            "Rehberlik ve Psikolojik Danışmanlık",
            "Bilgisayar ve Öğretim Teknolojileri Öğretmenliği"
        ],
        "Fen-Edebiyat Fakültesi" => [
            "Felsefe",
            "Tarih",
            "Tarih Öğretmenliği",
            "Karşılaştırmalı Edebiyat",
            "Dilbilimi",
            "Sosyoloji",
            "Türk Dili ve Edebiyatı",
            "Türk Dili ve Edebiyatı Öğretmenliği",
            "Rus Dili ve Edebiyatı (Rusça)",
            "Amerikan Kültürü ve Edebiyatı (İngilizce)"
        ],
        "Mühendislik Fakültesi" => [
            "Bilgisayar Mühendisliği (İngilizce)",
            "Elektrik-Elektronik Mühendisliği (İngilizce)",
            "Endüstri Mühendisliği",
            "İnşaat Mühendisliği",
            "Makine Mühendisliği",
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
        "İktisadi ve İdari Bilimler Fakültesi" => [
            "İktisat",
            "İktisat (İngilizce)",
            "İktisat (İngilizce) (UOLP-SUNY Albany) (Ücretli)",
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
            "Lojistik Yönetimi (İngilizce)",
            "Yönetim Bilişim Sistemleri"
        ],
        "Sağlık Bilimleri Fakültesi" => [
            "Hemşirelik",
            "Fizyoterapi ve Rehabilitasyon",
            "Diş Hekimliği",
            "Tıp",
            "Veterinerlik"
        ],
        "Güzel Sanatlar ve Tasarım" => [
            "Fotoğraf",
            "Fotoğraf (KKTC UYRUKLU)",
            "Kurgu, Ses ve Görüntü Yönetimi",
            "Kurgu, Ses ve Görüntü Yönetimi (KKTC Uyruklu)",
            "Film Tasarımı ve Yazarlığı",
            "Film Tasarımı ve Yazarlığı (KKTC Uyruklu)",
            "Film Tasarımı ve Yönetmenliği",
            "Film Tasarımı ve Yönetmenliği (KKTC UYRUKLU)",
            "Müzecilik",
            "Müzecilik (KKTC UYRUKLU)"
        ],
        "Mimarlık ve Şehircilik" => [
            "Mimarlık",
            "Şehir ve Bölge Planlama"
        ],
        "Turizm ve Gastronomi" => [
            "Gastronomi ve Mutfak Sanatları (Turizm Fakültesi)",
            "Gastronomi ve Mutfak Sanatları (Uygulamalı Bilimler Y.O.)",
            "Turizm İşletmeciliği (Turizm Fakültesi)",
            "Turizm İşletmeciliği (Uygulamalı Bilimler Y.O.)",
            "Turizm İşletmeciliği (İngilizce)",
            "Turizm İşletmeciliği (KKTC Uyruklu)"
        ],
        "Diğer" => [
            "Arkeoloji",
            "Biyoloji",
            "Biyoloji (UOLP-Gence Devlet Üniversitesi) (Ücretli)",
            "Kimya",
            "Kimya (KKTC UYRUKLU)",
            "Matematik",
            "Matematik (İngilizce)",
            "İstatistik",
            "Teknikerlik",
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


            <select name="grade_level" class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary">
                <option disabled selected value="">Sınıf seçiniz</option>
                <?php for($i=1;$i<=7;$i++): ?>
                    <option value="<?= $i ?>" <?= $grade==$i?'selected':'' ?>><?= $i ?>. Sınıf</option>
                <?php endfor; ?>
            </select>
            <input type="textarea" name="bio"
                class="w-full p-3 rounded-xl border-2 border-indigo-300 focus:ring-2 focus:ring-primary" placeholder="Bio'nu ekleyebilirsin.) " >

            <button type="submit" class="w-full bg-primary text-white p-3 rounded-xl font-bold hover:bg-cyan-600 transition">
                Kayıt Ol 🚀
            </button>
        </form>

        <p class="text-sm text-gray-700 italic bg-yellow-100 border border-yellow-400 rounded-md p-4 mt-6 text-center">
            ⚠️ Sadece DEU öğrencisiysen kaydolabilirsin. Diğerleri hayal kurmaya devam etsin 😎
        </p>
    <p class="text-center text-sm mt-5 text-gray-800">
    Hesabın varsa şanslısın, yoksa üzülme… <br>
    <a href="login.php" class="text-primary underline font-semibold">Giriş Yap</a> ya da 
    <a href="index.php" class="text-primary underline font-semibold">Baba Sayfaya Dön</a>
</p>


    </div>
</div>
</body>
</html>
