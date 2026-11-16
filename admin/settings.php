<?php
require __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$page = 'settings';
$pdo = db();
$msg = ''; $err = '';
$isOwner = ($admin['role'] ?? '') === 'owner';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'settings') {
        $email = trim($_POST['admin_email'] ?? ''); $phone = trim($_POST['contact_phone'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $err = 'Enter a valid email address for order alerts.'; }
        elseif ($phone === '') { $err = 'Enter a contact phone number.'; }
        else {
            $up = $pdo->prepare('INSERT INTO store_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $up->execute(['admin_email', $email]); $up->execute(['contact_phone', $phone]);
            $msg = 'Settings saved.';
        }
    } elseif ($action === 'password') {
        $st = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?'); $st->execute([$admin['id']]);
        $hash = $st->fetchColumn();
        $new = $_POST['new_password'] ?? '';
        if (!$hash || !password_verify($_POST['current_password'] ?? '', $hash)) $err = 'Your current password is wrong.';
        elseif (strlen($new) < 10) $err = 'Use a new password of at least 10 characters.';
        else {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            $msg = 'Password changed.';
        }
    } elseif ($action === 'add_staff' && $isOwner) {
        $u = trim($_POST['username'] ?? ''); $e = trim($_POST['email'] ?? ''); $p = $_POST['password'] ?? '';
        $dup = $pdo->prepare('SELECT 1 FROM admins WHERE username = ?'); $dup->execute([$u]);
        if (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $u)) $err = 'Username must be 3 to 40 letters, numbers, dots, dashes or underscores.';
        elseif (!filter_var($e, FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email address for the new staff member.';
        elseif (strlen($p) < 10) $err = 'Use a password of at least 10 characters.';
        elseif ($dup->fetch()) $err = 'That username is already taken.';
        else {
            $pdo->prepare('INSERT INTO admins (username, email, password_hash, role) VALUES (?,?,?,?)')->execute([$u, $e, password_hash($p, PASSWORD_DEFAULT), 'staff']);
            $msg = "Staff account {$u} created.";
        }
    }
}
$team = $isOwner ? $pdo->query('SELECT username, email, role, created_at FROM admins ORDER BY id')->fetchAll() : [];
include __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="h4 fw-bold mb-3">Settings</h1>
<?php if ($msg): ?><div class="alert alert-success" role="status"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger" role="alert"><?= h($err) ?></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-6">
    <form method="post" class="panel p-4 mb-4">
      <?= csrf_field() ?><input type="hidden" name="action" value="settings">
      <h2 class="h6 fw-bold mb-3">Store</h2>
      <label class="form-label" for="ae">Email for new-order alerts</label>
      <input class="form-control mb-3" id="ae" name="admin_email" type="email" value="<?= h(setting('admin_email', ADMIN_EMAIL)) ?>" required>
      <label class="form-label" for="cp">Contact phone</label>
      <input class="form-control mb-3" id="cp" name="contact_phone" value="<?= h(setting('contact_phone', CONTACT_PHONE)) ?>" required>
      <button class="btn btn-pearl px-4">Save settings</button>
    </form>
    <form method="post" class="panel p-4">
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <h2 class="h6 fw-bold mb-3">Change my password</h2>
      <label class="form-label" for="cpw">Current password</label>
      <input class="form-control mb-3" id="cpw" name="current_password" type="password" required autocomplete="current-password">
      <label class="form-label" for="npw">New password (10+ characters)</label>
      <input class="form-control mb-3" id="npw" name="new_password" type="password" minlength="10" required autocomplete="new-password">
      <button class="btn btn-pearl px-4">Change password</button>
    </form>
  </div>
  <?php if ($isOwner): ?>
  <div class="col-lg-6">
    <form method="post" class="panel p-4 mb-4">
      <?= csrf_field() ?><input type="hidden" name="action" value="add_staff">
      <h2 class="h6 fw-bold mb-3">Add a staff account</h2>
      <label class="form-label" for="su">Username</label><input class="form-control mb-3" id="su" name="username" required>
      <label class="form-label" for="se">Email</label><input class="form-control mb-3" id="se" name="email" type="email" required>
      <label class="form-label" for="sp">Temporary password (10+ characters)</label><input class="form-control mb-3" id="sp" name="password" type="password" minlength="10" required autocomplete="new-password">
      <button class="btn btn-pearl px-4">Create staff account</button>
    </form>
    <div class="panel p-4">
      <h2 class="h6 fw-bold mb-3">Team</h2>
      <?php foreach ($team as $t): ?>
        <div class="d-flex justify-content-between small py-1 border-bottom"><span><strong><?= h($t['username']) ?></strong> <span class="text-muted"><?= h($t['email']) ?></span></span><span class="status-pill st-Processing"><?= h($t['role']) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
