<?php
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user($pdo) {
    if (!is_logged_in()) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $stmt = $pdo->prepare("SELECT id, username, email, school, grade_level FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}

function post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

function redirect($url) {
    header("Location: $url");
    exit;
}
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function is_csrf_valid($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
function format_date($date) {
    return date('d.m.Y H:i', strtotime($date));
}
function is_new($created_at) {
    return (strtotime($created_at) > strtotime('-3 days'));
}

