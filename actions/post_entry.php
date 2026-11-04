
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

if(!is_logged_in()) {
    echo json_encode(['success'=>false,'error'=>'Giriş yapmalısın']); 
    exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if(!$csrf || !hash_equals($_SESSION['csrf_token'],$csrf)){
    echo json_encode(['success'=>false,'error'=>'CSRF doğrulaması başarısız']); 
    exit;
}
function is_logged_in(){
    return !empty($_SESSION['user_id']);
}

$topic_id = intval($_POST['topic_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$anonymous = isset($_POST['anonymous']) && $_POST['anonymous']=='1' ? 1 : 0;

if($topic_id <= 0 || mb_strlen($content) < 5){
    echo json_encode(['success'=>false,'error'=>'Eksik veya kısa içerik']); 
    exit;
}

$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $f = $_FILES['image'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!array_key_exists($mime, $allowedMimes)) {
        echo json_encode(['success' => false, 'error' => 'Sadece JPEG, PNG veya WEBP formatları kabul edilir.']); 
        exit;
    }

    if ($f['size'] > 2 * 1024 * 1024) { // 2MB sınırı
        echo json_encode(['success' => false, 'error' => 'Dosya boyutu en fazla 2MB olabilir.']); 
        exit;
    }

    $ext = $allowedMimes[$mime];
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $destDir = __DIR__ . '/../uploads/onepler/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $dest = $destDir . $name;

    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'error' => 'Dosya yüklenirken bir hata oluştu.']); 
        exit;
    }

    $imagePath = '/uploads/onepler/' . $name;
}


$link = null;
if(preg_match('/\b((https?:\/\/|www\.)[^\s<]+)/i', $content, $m)){
    $candidate = rtrim($m[1], '.,;:!?)');
    if(strpos($candidate,'www.') === 0) $candidate = 'http://' . $candidate;
    $link = $candidate;
}

try {
    $stmt = $pdo->prepare("INSERT INTO entries (topic_id,user_id,content,link,image,anonymous,created_at) VALUES (?,?,?,?,?,?,NOW())");
    $stmt->execute([$topic_id, $_SESSION['user_id'], $content, $link, $imagePath, $anonymous]);
    $entry_id = $pdo->lastInsertId();

    $username = $_SESSION['username'] ?? 'U';
    $displayName = $anonymous ? 'Anonim' : '@'.htmlspecialchars($username);
    $firstChar = strtoupper(mb_substr($username,0,1));

    $rendered = '<div class="p-4 rounded-xl bg-gray-50 border mb-3 animate-fade" data-id="'.$entry_id.'">';
    $rendered .= '<div class="flex gap-2 items-center mb-2">';
    $rendered .= '<div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">'.$firstChar.'</div>';
    $rendered .= '<span class="text-xs text-gray-500">'.$displayName.'</span>';
    $rendered .= '<span class="text-xs text-gray-400 ml-auto">'.date('d.m.Y H:i').'</span></div>';
    $rendered .= '<div class="text-sm leading-relaxed">'.nl2br(htmlspecialchars($content)).'</div>';
    if($imagePath) $rendered .= '<div class="mt-2"><img src="'.htmlspecialchars($imagePath).'" class="max-w-full rounded"></div>';
    $rendered .= '</div>';

    echo json_encode(['success'=>true, 'entry_id'=>$entry_id, 'html'=>$rendered]);

} catch(Exception $ex){
    error_log($ex->getMessage());
    echo json_encode(['success'=>false,'error'=>'Veritabanı hatası']);
}
require_once '../includes/helpers.php';
