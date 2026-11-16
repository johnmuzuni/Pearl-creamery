<?php
// tools/gen_hash.php — DELETE AFTER USE
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

$password = 'ChangeMe123!';   // <-- set your first admin password here
$hash = password_hash($password, PASSWORD_DEFAULT);

echo '<h3>Generated hash</h3>';
echo '<pre>' . htmlspecialchars($hash) . '</pre>';
echo '<p>Copy the hash above into the SQL statement and run it in phpMyAdmin.</p>';
echo '<p>Log in with username <b>owner</b> and password <b>' . htmlspecialchars($password) . '</b>, then change the password in Admin > Settings.</p>';
?>