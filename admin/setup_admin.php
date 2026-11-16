<?php
// Create an admin account.
// - If NO admin exists yet, this acts as one-time bootstrap (unauthenticated).
// - Once at least one admin exists, only a logged-in admin can create more.
require_once __DIR__ . '/auth.php';
$pdo = db();
$count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$msg = '';

if ($count > 0) {
    require_admin();                 // restrict further creations to logged-in admins
}

$roles = ['admin', 'owner'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $u  = trim($_POST['username'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if (!in_array($role, $roles, true)) $role = 'admin';
    if ($count === 0) $role = 'owner';   // first admin is always owner
    if ($u === '' || strlen($pw) < 8 || $em === '') {
        $msg = 'Username, email and a password of 8+ characters are required.';
    } else {
        try {
            $pdo->prepare('INSERT INTO admins (username, email, password_hash, role) VALUES (?,?,?,?)')
                ->execute([$u, $em, password_hash($pw, PASSWORD_DEFAULT), $role]);
            $msg = 'Admin account created. You can log in now.';
            $count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        } catch (Throwable $e) {
            $msg = 'Could not create admin (username or email may already exist).';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $count > 0 ? 'Add admin' : 'Create first admin' ?> | Pearl Creamery</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet"></head>
<body><div class="container py-5" style="max-width:440px">
<h1 class="h4 mb-3"><?= $count > 0 ? 'Add a new admin' : 'Create first admin' ?></h1>
<?php if ($msg): ?><div class="alert alert-info"><?= e($msg) ?></div><?php endif; ?>
<?php if ($count === 0 || !empty($_SESSION['admin_id']) || !empty($_SESSION['admin'])): ?>
<form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<input name="username" class="form-control mb-2" placeholder="Username" required>
<input name="email" type="email" class="form-control mb-2" placeholder="Email" required>
<input name="password" type="password" class="form-control mb-3" placeholder="Password (8+ chars)" required>
<?php if ($count > 0): ?>
<label class="form-label small mb-1" for="role">Role</label>
<select id="role" name="role" class="form-select mb-3">
  <option value="admin">Admin</option>
  <option value="owner">Owner</option>
</select>
<?php endif; ?>
<button class="btn pc-btn w-100"><?= $count > 0 ? 'Add admin' : 'Create admin' ?></button>
</form>
<?php else: ?>
<div class="alert alert-warning">You must be logged in as an admin to add more accounts. <a href="login.php">Log in</a></div>
<?php endif; ?>
<p class="text-muted small mt-3 mb-0">Logged in as admin? <a href="index.php">Back to dashboard</a>.</p>
</div></body></html>