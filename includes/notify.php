<?php
// Admin email alert (spec section 5). Uses PHP mail() for now; SMTP (587/465) comes next.
function notify_admin(array $o): void {
    $to = ADMIN_EMAIL;
    try { $v = db()->query("SELECT setting_value FROM store_settings WHERE setting_key = 'admin_email'")->fetchColumn(); if ($v) $to = $v; } catch (Throwable $e) {}
    $lines = ["New order {$o['code']}", "Customer: {$o['name']} ({$o['phone']})", "Payment: {$o['payment']}", ''];
    foreach ($o['items'] as $i) $lines[] = "{$i['qty']} x {$i['name']} - UGX " . number_format($i['qty'] * $i['price']);
    $lines[] = 'Total: UGX ' . number_format($o['total']);
    $lines[] = 'Address: ' . $o['address'];
    if ($o['lat'] !== null) $lines[] = "Map: https://www.google.com/maps?q={$o['lat']},{$o['lng']}";
    @mail($to, "Pearl Creamery order {$o['code']}", implode("\n", $lines), 'Content-Type: text/plain; charset=UTF-8');
}
