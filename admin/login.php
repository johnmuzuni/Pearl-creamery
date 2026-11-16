<?php
require __DIR__ . '/../includes/auth.php';
start_session();
if (!empty($_SESSION['admin'])) { header('Location: index.php'); exit; }
$error = '';
try { $noAdmins = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0; } catch (Throwable $e) { $noAdmins = false; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $fails = $_SESSION['fails'] ?? 0;
    if ($fails >= 5 && time() - ($_SESSION['last_fail'] ?? 0) < 60) {
        $error = 'Too many attempts. Wait a minute and try again.';
    } else {
        try {
            $st = db()->prepare('SELECT id, username, email, password_hash, role FROM admins WHERE username = ?');
            $st->execute([trim($_POST['username'] ?? '')]);
            $a = $st->fetch();
            if ($a && password_verify($_POST['password'] ?? '', $a['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION = ['admin' => ['id' => (int)$a['id'], 'username' => $a['username'], 'role' => $a['role']]];
                header('Location: index.php'); exit;
            }
            $_SESSION['fails'] = $fails + 1; $_SESSION['last_fail'] = time();
            $error = 'Username or password is wrong.';
        } catch (Throwable $e) { $error = 'Could not reach the database.'; }
    }
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login | Pearl Creamery</title><meta name="robots" content="noindex">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/pearl.css" rel="stylesheet"></head>
<body class="d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
  <div class="text-center mb-4"><span class="brand-mark mb-2">P</span><h1 class="h3 fw-bold">Pearl Creamery admin</h1></div>
  <form method="post" class="bg-white border rounded-4 p-4">
    <?= csrf_field() ?>
    <?php if ($noAdmins): ?><div class="alert alert-warning" role="status">No admin account exists yet. <a href="../setup_admin.php">Create the first admin</a>.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= h($error) ?></div><?php endif; ?>
    <label class="form-label" for="u">Username</label><input class="form-control mb-3" id="u" name="username" required autofocus autocomplete="username">
    <label class="form-label" for="p">Password</label><input class="form-control mb-4" id="p" name="password" type="password" required autocomplete="current-password">
    <button class="btn btn-pearl w-100 py-2">Log in</button>
  </form>
  <p class="text-center mt-3 mb-0"><a class="btn btn-outline-pearl w-100 py-2" href="../index.php"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Back to the shop</a></p>
</div>
<script src="../assets/js/password-toggle.js"></script></body></html>
