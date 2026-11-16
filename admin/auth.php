<?php
require_once __DIR__ . '/../db.php';

function require_admin() {
    // Accept both login formats: $_SESSION['admin_id'] (legacy pages) and
    // $_SESSION['admin'] (admin/login.php sets this one).
    if (empty($_SESSION['admin_id']) && empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); exit('Bad request'); }
}
