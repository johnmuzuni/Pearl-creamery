<?php
// Pesapal IPN. Register this URL with Pesapal (RegisterIPN) and put the returned id in config.php.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/pesapal.php';

$trackingId = $_GET['OrderTrackingId'] ?? '';
if ($trackingId === '') json_out(['error' => 'missing id'], 400);

// Never trust the callback itself. Ask Pesapal for the real status.
$s = pesapal_status($trackingId);
$status = strtolower($s['payment_status_description'] ?? '');
$new = $status === 'completed' ? 'Paid' : ($status === 'failed' ? 'Failed' : null);

if ($new) {
    $st = db()->prepare('UPDATE payments SET status = ? WHERE transaction_ref = ?');
    $st->execute([$new, $trackingId]);
}
json_out([
    'orderNotificationType' => $_GET['OrderNotificationType'] ?? 'IPNCHANGE',
    'orderTrackingId' => $trackingId,
    'orderMerchantReference' => $_GET['OrderMerchantReference'] ?? '',
    'status' => 200,
]);
