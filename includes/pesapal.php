<?php
// Minimal Pesapal v3 helper. Fill in keys in config.php first.
function pesapal_call($method, $path, $data = null, $token = null) {
    $ch = curl_init(PESAPAL_BASE . $path);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30,
    ]);
    if ($data !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function pesapal_token() {
    $r = pesapal_call('POST', '/api/Auth/RequestToken', [
        'consumer_key' => PESAPAL_KEY, 'consumer_secret' => PESAPAL_SECRET,
    ]);
    return $r['token'] ?? null;
}

// Returns the Pesapal checkout URL (customer picks MTN / Airtel and confirms with PIN).
function pesapal_start($order, $paymentId) {
    $token = pesapal_token();
    if (!$token) return null;
    $r = pesapal_call('POST', '/api/Transactions/SubmitOrderRequest', [
        'id' => $order['order_code'] . '-' . $paymentId,
        'currency' => 'UGX',
        'amount' => (int)$order['total_price'],
        'description' => 'Pearl Creamery order ' . $order['order_code'],
        'callback_url' => SITE_URL . '/thanks.php?ref=' . $order['order_code'],
        'notification_id' => PESAPAL_IPN_ID,
        'billing_address' => [
            'phone_number' => $order['customer_phone'],
            'email_address' => $order['customer_email'] ?: 'noemail@pearlcreamery.local',
            'first_name' => $order['customer_name'],
            'country_code' => 'UG',
        ],
    ], $token);
    if (!empty($r['order_tracking_id'])) {
        $st = db()->prepare('UPDATE payments SET transaction_ref = ?, gateway = "Pesapal" WHERE id = ?');
        $st->execute([$r['order_tracking_id'], $paymentId]);
    }
    return $r['redirect_url'] ?? null;
}

function pesapal_status($trackingId) {
    $token = pesapal_token();
    return pesapal_call('GET', '/api/Transactions/GetTransactionStatus?orderTrackingId=' . urlencode($trackingId), null, $token);
}
