<?php
// Temporary diagnostics — DELETE THIS FILE after troubleshooting.
ini_set('display_errors', '1');
error_reporting(E_ALL);
require __DIR__ . '/config.php';
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '(none)') . "\n";
echo "isLocal check would use above host.\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : '(undefined)') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : '(undefined)') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : '(undefined)') . "\n";
try {
    require __DIR__ . '/db.php';
    $pdo = db();
    echo "DB CONNECTION: OK\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES: " . implode(', ', $tables) . "\n";
} catch (Throwable $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) echo "PHPMailer: installed\n";
else echo "PHPMailer: NOT installed (email will use mail())\n";
echo "PHP version: " . PHP_VERSION . "\n";