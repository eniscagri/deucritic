<?php
require 'config.php'; 

$loginError = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $pw = $_POST['password'] ?? '';
    if(password_verify($pw, ADMIN_PASSWORD_HASH)){
        $_SESSION['admin_logged'] = true;
        session_regenerate_id(true);
        header('Location: admin_users.php');
        exit;
    } else {
        $loginError = 'Incorrect password.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
<div class="bg-white p-6 rounded shadow w-full max-w-sm">
<h1 class="text-xl font-bold mb-4">Admin Login</h1>
<?php if($loginError): ?><div class="text-red-600 mb-3"><?=htmlspecialchars($loginError)?></div><?php endif; ?>
<form method="post" class="space-y-3">
    <input type="password" name="password" placeholder="Password" required class="w-full p-2 border rounded">
    <input type="hidden" name="csrf" value="<?=csrf_token()?>">
    <button class="w-full bg-blue-600 text-white p-2 rounded">Login</button>
</form>
</div>
</body>
</html>
