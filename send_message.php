<?php
require 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum açılmamış.']);
    exit;
}

$sender = $_SESSION['user_id'];
$receiver = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
$convo = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
$msg = trim($_POST['message'] ?? '');

if (!$receiver || !$convo) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri.']);
    exit;
}

$image_tag = '';

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_tmp = $_FILES['photo']['tmp_name'];
    
    $file_type = mime_content_type($file_tmp);
    
    if (in_array($file_type, $allowed_types)) {
        $upload_dir = 'uploads/messages/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('img_') . '_' . time() . '.' . $extension;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file_tmp, $destination)) {
            $image_tag = "[IMAGE]" . $destination . "[/IMAGE]";
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sadece JPG, PNG, GIF ve WEBP formatları kabul edilir.']);
        exit;
    }
}

if (empty($msg) && empty($image_tag)) {
    echo json_encode(['status' => 'error', 'message' => 'Boş mesaj gönderilemez.']);
    exit;
}

$final_message = $msg;
if (!empty($image_tag)) {
    $final_message = empty($msg) ? $image_tag : $msg . "\n<br>\n" . $image_tag;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$convo, $sender, $receiver, $final_message]);
    
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Mesaj kaydedilirken bir hata oluştu.']);
}