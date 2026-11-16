<?php
// One-time setup: creates the first admin account, then refuses to run again. Delete this file afterwards.
require __DIR__ . '/includes/auth.php';
$msg = ''; $err = ''; $done = false;
try {
    $exists = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (Throwable $e) { exit('Could not reach the database. Import database.sql first.'); }
if ($exists) exit('An admin account already exists. Delete setup_admin.php from the server.');

// Optional secret key: if PC_SETUP_KEY is set (as an env var), it is required.
// When no key is configured, the bootstrap is still allowed because this page
// only runs while zero admins exist and deletes itself right after.
$local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
$setupKey = getenv('PC_SETUP_KEY') ?: '';
$needsKey = $setupKey !== '' && !$local;
$deleted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $keyOk = !$needsKey || hash_equals($setupKey, (string)($_POST['setup_key'] ?? ''));
    $u = trim($_POST['username'] ?? ''); $e = trim($_POST['email'] ?? ''); $p = $_POST['password'] ?? '';
    if (!$keyOk) $err = 'The setup key is wrong.';
    elseif (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $u)) $err = 'Username must be 3 to 40 letters, numbers, dots, dashes or underscores.';
    elseif (!filter_var($e, FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email address.';
    elseif (strlen($p) < 10) $err = 'Use a password of at least 10 characters.';
    else {
        db()->prepare('INSERT INTO admins (username, email, password_hash, role) VALUES (?,?,?,?)')
            ->execute([$u, $e, password_hash($p, PASSWORD_DEFAULT), 'owner']);
        $done = true;
        $deleted = @unlink(__FILE__); // remove the setup page so nobody can reuse it
    }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create admin | Pearl Creamery</title><meta name="robots" content="noindex">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/css/pearl.css" rel="stylesheet"></head>
<body class="d-flex align-items-center" style="min-height:100vh"><div class="container" style="max-width:440px">
<h1 class="h3 fw-bold mb-3">Create the first admin</h1>
<?php if ($done): ?>
  <div class="alert alert-success">Admin created. <?= $deleted ? 'This setup page has been removed.' : '<strong>Delete setup_admin.php from the server now.</strong>' ?> <a href="admin/login.php">Log in</a>.</div>
<?php else: ?>
  <form method="post" class="bg-white border rounded-4 p-4"><?= csrf_field() ?>
    <?php if ($err): ?><div class="alert alert-danger" role="alert"><?= h($err) ?></div><?php endif; ?>
    <?php if ($needsKey): ?><label class="form-label" for="k">Setup key</label><input class="form-control mb-3" id="k" name="setup_key" type="password" required><?php endif; ?>
    <label class="form-label" for="u">Username</label><input class="form-control mb-3" id="u" name="username" required>
    <label class="form-label" for="e">Email</label><input class="form-control mb-3" id="e" name="email" type="email" required>
    <label class="form-label" for="p">Password (10+ characters)</label><input class="form-control mb-4" id="p" name="password" type="password" required minlength="10" autocomplete="new-password">
    <button class="btn btn-pearl w-100 py-2">Create admin</button>
  </form>
<?php endif; ?>
</div>
<script src="assets/js/password-toggle.js"></script></body></html>
