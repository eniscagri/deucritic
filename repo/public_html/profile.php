<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, email, school, grade_level, profile_photo, bio FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . e($e->getMessage()));
}

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';
$form = [
    'username' => $user['username'],
    'email' => $user['email'],
    'school' => $user['school'],
    'grade_level' => $user['grade_level'],
    'bio' => $user['bio'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_msg = 'Geçersiz istek!';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $school = trim($_POST['school'] ?? '');
        $grade = trim($_POST['grade_level'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $bio = trim($_POST['bio'] ?? '');

        $form = [
            'username' => $username,
            'email' => $email,
            'school' => $school,
            'grade_level' => $grade,
            'bio' => $bio
        ];

        $profile_photo = $user['profile_photo'];
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['profile_photo']['type'], $allowed_types)) {
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/uploads/profile_photos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $target = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
                    $profile_photo = 'uploads/profile_photos/' . $filename;
                } else {
                    $error_msg = 'Profil fotoğrafı yüklenemedi.';
                }
            } else {
                $error_msg = 'Sonu JPG, PNG veya GIF olmayan dosya yüklenemez.';
            }
        }

        if (!$username || !$email) {
            $error_msg = 'Kullanıcı adı ve e-posta boş olamaz.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Geçerli bir e-posta giriniz.';
        } elseif ($password && strlen($password) < 6) {
            $error_msg = 'Şifre en az 6 karakter olmalı.';
        } elseif ($password && $password !== $password_confirm) {
            $error_msg = 'Şifreler eşleşmiyor.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?)) AND id != ?");
            $stmt->execute([$email, $username, $user_id]);
            if ($stmt->rowCount() > 0) {
                $error_msg = 'Bu kullanıcı adı veya e-posta başka bir kullanıcı tarafından kullanılıyor.';
            } else {
                try {
                    if ($password) {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, school = ?, grade_level = ?, password = ?, profile_photo = ?, bio = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $school, $grade, $hashed, $profile_photo, $bio, $user_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, school = ?, grade_level = ?, profile_photo = ?, bio = ? WHERE id = ?");
                        $stmt->execute([$username, $email, $school, $grade, $profile_photo, $bio, $user_id]);
                    }
                    $success_msg = 'Profiliniz başarıyla güncellendi.';
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['school'] = $school;
                    $user['grade_level'] = $grade;
                    $user['profile_photo'] = $profile_photo;
                    $user['bio'] = $bio;
                } catch (PDOException $e) {
                    $error_msg = 'Bir hata oluştu: ' . e($e->getMessage());
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8" />
  <link rel="icon" href="/gorsel/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Profil - Deucritic Sözlük</title>
<link rel="stylesheet" href="css/tailwind.output.css" />

<!--SEO-->
 <meta name="description" content="Deucritic, kullanıcıların fikirlerini özgürce paylaşabildiği, yorum yapabildiği ve çeşitli konularda eleştiri üretebildiği insan odaklı bir platformdur.">
    <meta name="keywords" content="Deucritic, eleştiri platformu, yorum, kullanıcı yorumları, sosyal platform, interaktif sözlük, özgür fikir, Türkiye">
    <meta name="author" content="deucritic.com">
    <meta property="og:title" content="Deucritic - İnsan Odaklı Eleştiri ve Yorum Platformu">
    <meta property="og:description" content="Deucritic ile fikirlerinizi özgürce paylaşın, yorum yapın ve toplulukla etkileşimde bulunun.">
    <meta property="og:image" content="https://deucritic.com/assets/og-image.png">
    <meta property="og:url" content="https://deucritic.com">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Deucritic - İnsan Odaklı Eleştiri ve Yorum Platformu">
    <meta name="twitter:description" content="Deucritic ile fikirlerinizi özgürce paylaşın, yorum yapın ve toplulukla etkileşimde bulunun.">

    <link rel="canonical" href="https://deucritic.com">


<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<header class="bg-white shadow mb-6">
  <div class="container mx-auto px-4 py-4 flex items-center justify-between">
    <a href="index.php" class="text-2xl font-bold text-primary hover:text-cyan-700 transition">Deucritic Sözlük</a>
    <nav class="flex items-center space-x-4">
      <span class="text-gray-700 font-medium">Merhaba, <?= e($user['username']) ?></span>
      <a href="logout.php" class="text-red-600 hover:underline">Çıkış Yap</a>
    </nav>
  </div>
</header>

<main class="container mx-auto mt-8 max-w-md bg-white p-6 rounded shadow">

<h2 class="text-xl font-semibold mb-4">Profil Bilgileri</h2>

<?php if ($error_msg): ?>
  <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= e($error_msg) ?></div>
<?php endif; ?>

<?php if ($success_msg): ?>
  <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= e($success_msg) ?></div>
<?php endif; ?>

<div class="mb-4 flex flex-col items-center">
  <?php if (!empty($user['profile_photo']) && file_exists(__DIR__ . '/' . $user['profile_photo'])): ?>
    <img src="<?= e($user['profile_photo']) ?>" alt="Profil Fotoğrafı" class="w-24 h-24 rounded-full object-cover border mb-2">
  <?php else: ?>
    <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center mb-2">
      <span class="text-gray-400 text-4xl">?</span>
    </div>
  <?php endif; ?>
</div>

<form method="POST" class="space-y-4" autocomplete="off" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
  <div>
    <label for="profile_photo" class="block font-medium mb-1">Profil Fotoğrafı</label>
    <input type="file" id="profile_photo" name="profile_photo" accept="image/*"
      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
    <p class="text-xs text-gray-500 mt-1">JPG, PNG veya GIF yükleyebilirsiniz.</p>
  </div>
  <div>
    <label for="username" class="block font-medium mb-1">Kullanıcı Adı</label>
    <input type="text" id="username" name="username" value="<?= e($form['username']) ?>" required
    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
  </div>
  <div>
    <label for="email" class="block font-medium mb-1">E-posta</label>
    <input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required
    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" /> 
    <p class="text-sm text-gray-400 mt-2">Okul e-postan yerine kişisel e-postanı koyabilirsin.</p>
  </div>
  <div>
    <label for="bio" class="block font-medium mb-1">Hakkında / Bio</label>
    <input type="text" id="bio" name="bio" value="<?= e($form['bio']) ?>"
    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
  </div>
  <div>
    <label for="school" class="block font-medium mb-1">Okul</label>
    <input type="text" id="school" name="school" readonly value="<?= e($form['school']) ?>"
    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
  </div>
  <div>
    <label for="grade_level" class="block font-medium mb-1">Sınıf / Seviye</label>
    <input type="text" id="grade_level" name="grade_level" value="<?= e($form['grade_level']) ?>"
    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
  </div>
  <div>
    <label for="password" class="block font-medium mb-1">Yeni Şifre (isteğe bağlı)</label>
    <input type="password" id="password" name="password" placeholder="İstersen değiştirebilirsin" readonly
    class="w-full border border-white-500 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" autocomplete="new-password" />
    <p class="text-sm text-gray-500 mt-1">Şaka :D Siz değiştirin, biz uğraşamayız.</p>
  </div>

  <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-cyan-600 transition">Kaydet</button>
  <hr class="my-4">
  <p class="text-sm text-gray-500 mt-2">Profil bilgilerinizi güncel tutmak, hesabınızın güvenliği ve iletişim için önemlidir.</p>
</form>

<h3 class="text-lg font-semibold mb-2 text-primary">Şifreni mi yenilemek istiyorsun?</h3>
<p class="text-sm text-gray-600">Şifreni yenilemek için <a href="reset_password.php" class="text-primary underline">buraya tıkla</a>.</p>

</main>
</body>
</html>
