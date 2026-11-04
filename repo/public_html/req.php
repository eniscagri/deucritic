<?php
include 'includes/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
$error = '';

$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
$mysqli = new mysqli('localhost', 'deucditiccom_eniscagri', 'Enis07654607', 'deucditiccom_deucritic');
if ($mysqli->connect_error) {
    die("Veritabanı bağlantı hatası: " . $mysqli->connect_error);
}
if (!$isLoggedIn) {
    $error = "Giriş yapmadan istek gönderemezsiniz.";
}
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $feature = trim($_POST['feature'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';
    if ($csrf !== $_SESSION['csrf_token']) {
        $error = "CSRF token hatası.";
    } elseif ($feature === '') {
        $error = "İstek alanı boş bırakılamaz.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO request (user_id, feature, date) VALUES (?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param('is', $_SESSION['user_id'], $feature);
            if ($stmt->execute()) {
                $success = "İsteğiniz kaydedildi. (Bir sürü iş bindi, ağlamak üzereyim :D)";
            } else {
                $error = "İstek kaydedilirken bir hata oluştu.";
            }
            $stmt->close();
        } else {
            $error = "Veritabanı sorgusu hatası: " . $mysqli->error;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = "Lütfen giriş yapın ve tekrar deneyin.";

}


$entries = [];
$result = $mysqli->query("SELECT * FROM request ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
      <link rel="icon" href="/gorsel/favicon.ico" type="image/x-icon" />
  <link rel="apple-touch-icon" sizes="180x180" href="/gorsel/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/gorsel/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/gorsel/favicon-16x16.png">
    <meta charset="UTF-8">
    <title>İstekler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-pink-100 min-h-screen flex flex-col items-center font-sans">
    <div class="w-full max-w-xl mt-10 bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-3xl font-extrabold text-center text-blue-700 mb-6 flex items-center justify-center gap-2">
            Birlikte Geliştirelim
        </h2>
        <?php if ($isLoggedIn): ?>
            <form method="post" class="mb-6 space-y-4">
                <label class="block text-lg font-semibold text-gray-700 mb-2" for="feature">
                    DeuCritic'de özellik olarak görmek istediğin bir şeyler var mı? 
                </label>
                <textarea name="feature" id="feature" rows="3" required
                    class="w-full p-3 rounded-lg border-2 border-blue-200 focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition resize-none"
                    placeholder="Buraya yaz, hayal gücünü zorla!"><?= isset($_POST['feature']) && $error ? htmlspecialchars($_POST['feature']) : '' ?></textarea>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit"
                    class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded-lg shadow transition transform hover:scale-105">
                    Gönder 🚀
                </button>
            </form>
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center animate-pulse">
                    <?= $success ?>
                </div>
            <?php elseif ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 text-center">
                Giriş yapmadan istek gönderemezsiniz.  <span class="ml-2">😅</span>
            </div>
        <?php endif; ?>

    </div>
    

    <footer class="mt-10 text-gray-400 text-sm text-center">
        &copy; <?= date('Y') ?> DeuCritic Sözlük
        <br>
        <a href="index.php" class="text-blue-500 hover:underline">Baba Sayfa'ya Dön</a>


    </footer>
</body>
</html>
