<?php
require 'config.php';

unset($_SESSION['admin_logged'], $_SESSION['admin_last_activity']);
$_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
header('Location: admin_login.php');
exit;
