<?php
// temp_diag.php — DELETE AFTER USE
ini_set('display_errors', '1');
error_reporting(E_ALL);
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
try {
    $pdo = db();
    echo "DB OK<br>";
    echo "Tables: " . implode(', ', $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
} catch (Throwable $e) {
    echo "DB ERROR: " . $e->getMessage();
}
?>