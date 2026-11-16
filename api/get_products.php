<?php
require __DIR__ . '/../config.php';
try {
    $rows = db()->query('SELECT id, name, category, description, price, size FROM products WHERE is_available = 1 ORDER BY category, id')->fetchAll();
    foreach ($rows as &$r) { $r['id'] = (int)$r['id']; $r['price'] = (int)$r['price']; }
    json_out(['products' => $rows]);
} catch (Throwable $e) {
    json_out(['error' => 'Could not load products.'], 500);
}
