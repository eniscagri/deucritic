<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $error = "Kardeşim, şifreyi değiştirmek için önce bi' giriş yap da seni tanıyalım.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Formu boş bırakma, hepsini doldur ki işimiz kolay olsun.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Yeni şifreler birbirini tutmuyor, bi' daha dene.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['id'] == $_SESSION['user_id']) {
            if (password_verify($current_password, $user['password_hash'])) {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $update->execute([$password_hash, $user['id']]);
                $success = "Helal! Şifreyi güncelledik, artık yeni şifrenle giriş yapabilirsin.";
            } else {
                $error = "Mevcut şifre yanlış, klavyeye dikkat et.";
            }
        } else {
            $error = "Bu e-posta ile kayıtlı kullanıcı yok ya da yetkin yok, boşuna uğraşma.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Sıfırlama</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Şifre Sıfırlama</h2>
        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-center"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post" class="space-y-5">
            <div>
            <label class="block text-gray-700 mb-1">E-posta</label>
            <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Mailini yaz, yanlış yazarsan sıkıntı büyük.">
            </div>
            <div>
            <label class="block text-gray-700 mb-1">Mevcut Şifre</label>
            <input type="password" name="current_password" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Şu anki şifreni gir.">
            </div>
            <div>
            <label class="block text-gray-700 mb-1">Yeni Şifre</label>
            <input type="password" name="new_password" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Yeni şifreni yaz, sağlam olsun.">
            </div>
            <div>
            <label class="block text-gray-700 mb-1">Yeni Şifre (Tekrar)</label>
            <input type="password" name="confirm_password" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Bir daha yaz, yoksa mezar kazıcılar bile şaşırır.">
            </div>
            <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
            Şifreyi Güncelle
            </button>
        </form>
        <?php endif; ?>
        <div class="mt-6 text-center">
        <a href="index.php" class="text-blue-600 hover:underline">Baba Sayfa'ya Dön</a>
    </div>
    </div>

    
</body>
</html>