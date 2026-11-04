<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Veritabanı ayarları
$db_host = 'localhost';
$db_name = 'deucditiccom_deucritic';
$db_user = 'deucditiccom_eniscagri';
$db_pass = 'Enis07654607';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    exit('Veritabanı bağlantı hatası: ' . $e->getMessage());
}

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Dosya yüklenmiş mi kontrol et
if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('Bir dosya seçmediniz.'); window.history.back();</script>";
    exit;
}

$fileTmpPath = $_FILES['profile_photo']['tmp_name'];
$fileName = $_FILES['profile_photo']['name'];
$fileSize = $_FILES['profile_photo']['size'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Dosya türü kontrolü
if (!in_array($fileExtension, $allowedExtensions)) {
    echo "<script>alert('Sadece JPG, PNG, GIF veya WEBP dosyaları yüklenebilir.'); window.history.back();</script>";
    exit;
}

// Maksimum dosya boyutu kontrolü (2MB)
if ($fileSize > 2 * 1024 * 1024) {
    echo "<script>alert('Dosya boyutu 2MB\'dan büyük olamaz.'); window.history.back();</script>";
    exit;
}

// Güvenli dosya adı
$newFileName = uniqid('profile_', true) . '.' . $fileExtension;
$uploadDir = 'uploads/';
$destPath = $uploadDir . $newFileName;

// Upload klasörü yoksa oluştur
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Dosyayı taşı
if (!move_uploaded_file($fileTmpPath, $destPath)) {
    echo "<script>alert('Dosya yüklenirken bir hata oluştu.'); window.history.back();</script>";
    exit;
}

// Eski fotoğrafı sil (varsayılan değilse)
$stmtOld = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmtOld->execute([$user_id]);
$oldPhoto = $stmtOld->fetchColumn();

if ($oldPhoto && $oldPhoto !== 'uploads/default-avatar.png' && file_exists($oldPhoto)) {
    unlink($oldPhoto);
}

// Veritabanını güncelle
$stmtUpdate = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
$stmtUpdate->execute([$destPath, $user_id]);

echo "<script>alert('Profil fotoğrafı başarıyla güncellendi!'); window.location.href='profile.php?user=" . urlencode($_SESSION['username'] ?? 'profil') . "';</script>";
exit;
?>
