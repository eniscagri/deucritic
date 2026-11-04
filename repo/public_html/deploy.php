<?php
$secret = "BURAYA_GIZLI_TOKEN";

$input = file_get_contents('php://input');
if (strpos($input, $secret) === false) {
    header('HTTP/1.0 403 Forbidden');
    exit("Unauthorized");
}

$output = null;
$return_var = null;
exec("cd " . __DIR__ . " && git reset --hard && git pull origin main 2>&1", $output, $return_var);

file_put_contents("deploy_log.txt", date('Y-m-d H:i:s') . " - return: $return_var\n" . implode("\n", $output) . "\n", FILE_APPEND);

echo "Deploy executed.";
?>
