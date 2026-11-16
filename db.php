<?php
// Backwards-compatible shim. The real connection and json helper now live in
// config.php so that pages which only require config.php (api/*, includes/*)
// and pages which require db.php (admin/*, thanks.php) share ONE definition.
require_once __DIR__ . '/config.php';

if (!function_exists('setting')) {
    function setting($key, $default = '') {
        try {
            $st = db()->prepare('SELECT setting_value FROM store_settings WHERE setting_key = ?');
            $st->execute([$key]);
            $v = $st->fetchColumn();
            return $v === false ? $default : $v;
        } catch (Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
