<?php
require_once __DIR__ . '/../config.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('pc_admin');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
        session_start();
    }
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function require_admin(): array {
    start_session();
    if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
    return $_SESSION['admin'];
}
function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'; }
function csrf_check(): void {
    start_session();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Your session expired. Go back, reload the page and try again.');
    }
}
if (!function_exists('setting')) {
    function setting(string $key, string $fallback = ''): string {
        try {
            $st = db()->prepare('SELECT setting_value FROM store_settings WHERE setting_key = ?');
            $st->execute([$key]);
            $v = $st->fetchColumn();
            return ($v === false || $v === '') ? $fallback : (string)$v;
        } catch (Throwable $e) { return $fallback; }
    }
}
