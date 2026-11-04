<?php
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) exit;

$stmt = $pdo->prepare("
  SELECT id, message, link, is_read, created_at 
  FROM notifications 
  WHERE user_id = :uid OR is_global = 1
  ORDER BY created_at DESC 
  LIMIT 10
");
$stmt->execute(['uid' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$notifications) {
  echo '<div class="p-8 text-center text-slate-400"><i class="fa-regular fa-bell-slash text-4xl mb-3 opacity-30"></i><p class="text-sm font-medium">Şu an için yeni bildiriminiz yok.</p></div>';
  exit;
}

foreach ($notifications as $n): 
    $bgClass = $n['is_read'] ? 'bg-white' : 'bg-slate-50';
?>
  <li class="transition-colors duration-300 <?= $bgClass ?>">
    <a href="<?= htmlspecialchars($n['link'] ?? '#') ?>" class="block px-5 py-4 hover:bg-slate-50/80 transition-colors border-b border-slate-100/50">
      <p class="text-[13px] text-slate-700 font-medium leading-snug mb-1.5"><?= htmlspecialchars($n['message']) ?></p>
      <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
          <i class="fa-regular fa-clock mr-1"></i>
          <?= date('d M H:i', strtotime($n['created_at'])) ?>
      </p>
    </a>
  </li>
<?php endforeach; ?>