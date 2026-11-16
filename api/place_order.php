<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/notify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only.'], 405);
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) json_out(['error' => 'Invalid request.'], 400);

$name  = trim($in['name'] ?? '');
$phone = preg_replace('/[\s-]/', '', $in['phone'] ?? '');
$email = trim($in['email'] ?? '');
$addr  = trim($in['address'] ?? '');
$notes = trim($in['notes'] ?? '');
$pay   = $in['payment_method'] ?? 'cod';
$lat   = is_numeric($in['lat'] ?? null) ? round((float)$in['lat'], 7) : null;
$lng   = is_numeric($in['lng'] ?? null) ? round((float)$in['lng'], 7) : null;

if ($name === '' || mb_strlen($name) > 120) json_out(['error' => 'Enter your name.'], 422);
if (!preg_match('/^(\+?256|0)7\d{8}$/', $phone)) json_out(['error' => 'Enter a valid Uganda phone number, e.g. 0780 457866.'], 422);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(['error' => 'That email address looks wrong.'], 422);
if ($addr === '') json_out(['error' => 'Enter a delivery address or use your location.'], 422);
if (!in_array($pay, ['cod', 'mobile_money'], true)) json_out(['error' => 'Choose a payment method.'], 422);
if (empty($in['items']) || !is_array($in['items'])) json_out(['error' => 'Your cart is empty.'], 422);
$phone = '+256' . substr(preg_replace('/^\+?256|^0/', '', $phone, 1), -9);

try {
    $pdo = db();
    // Prices come from the database, never from the browser
    $stmt = $pdo->prepare('SELECT id, name, price FROM products WHERE id = ? AND is_available = 1');
    $items = []; $total = 0;
    foreach ($in['items'] as $it) {
        $qty = (int)($it['qty'] ?? 0);
        $stmt->execute([(int)($it['id'] ?? 0)]);
        $p = $stmt->fetch();
        if (!$p || $qty < 1 || $qty > 50) json_out(['error' => 'One of the items is no longer available.'], 422);
        $items[] = ['id' => (int)$p['id'], 'name' => $p['name'], 'price' => (int)$p['price'], 'qty' => $qty];
        $total += $p['price'] * $qty;
    }

    $pdo->beginTransaction();
    $chk = $pdo->prepare('SELECT 1 FROM orders WHERE order_code = ?');
    do {
        $code = 'PC-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $chk->execute([$code]);
    } while ($chk->fetch());

    $pdo->prepare('INSERT INTO orders (order_code, customer_name, customer_phone, customer_email, total_price, latitude, longitude, formatted_address, delivery_notes) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([$code, $name, $phone, $email ?: null, $total, $lat, $lng, mb_substr($addr, 0, 255), mb_substr($notes, 0, 255) ?: null]);
    $orderId = (int)$pdo->lastInsertId();

    $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?,?,?,?)');
    foreach ($items as $i) $ins->execute([$orderId, $i['id'], $i['qty'], $i['price']]);

    // Mobile Money stays 'Pending' until the gateway webhook marks it Paid (next step)
    $pdo->prepare('INSERT INTO payments (order_id, gateway, payment_method, status, amount) VALUES (?,?,?,?,?)')
        ->execute([$orderId, $pay === 'cod' ? 'none' : 'flutterwave', $pay, $pay === 'cod' ? 'Unpaid' : 'Pending', $total]);
    $pdo->commit();

    notify_admin(['code' => $code, 'name' => $name, 'phone' => $phone, 'payment' => $pay, 'items' => $items,
                  'total' => $total, 'address' => $addr, 'lat' => $lat, 'lng' => $lng]);
    json_out(['ok' => true, 'order_code' => $code, 'total' => $total]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    json_out(['error' => 'We could not save your order. Please try again or call ' . CONTACT_PHONE . '.'], 500);
}
