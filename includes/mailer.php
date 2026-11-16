<?php
// Sends the admin an email for each new order.
// Install PHPMailer once:  composer require phpmailer/phpmailer
// Without it, we fall back to PHP mail() (which usually does not work on XAMPP).
function send_order_email($order, $items) {
    $to = setting('admin_email');
    if (!$to) return false;

    $map = ($order['latitude'] && $order['longitude'])
        ? 'https://www.google.com/maps?q=' . $order['latitude'] . ',' . $order['longitude'] : 'Not shared';

    $itemLines = '';
    if (is_array($items)) {
        foreach ($items as $it) {
            $itemLines .= ' - ' . ($it['name'] ?? 'Item') . ' x ' . (int)($it['quantity'] ?? 1)
                       . ' (UGX ' . number_format(((int)($it['price'] ?? 0)) * ((int)($it['quantity'] ?? 1))) . ")\n";
        }
    } else {
        $itemLines = ' - ' . (string)$items . "\n";
    }

    $body = "New Pearl Creamery order\n\n"
          . "Reference: {$order['order_code']}\n"
          . "Customer: {$order['customer_name']}\n"
          . "Phone: {$order['customer_phone']}\n"
          . "Items:\n" . $itemLines
          . "Total: UGX " . number_format($order['total_price']) . "\n"
          . "Payment: {$order['payment_method']}\n"
          . "Address: {$order['formatted_address']}\n"
          . "Map: {$map}\n"
          . "Notes: {$order['delivery_notes']}\n";
    $subject = 'New order ' . $order['order_code'];

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        try {
            $m = new PHPMailer\PHPMailer\PHPMailer(true);
            $m->isSMTP();
            $m->Host = setting('smtp_host');
            $m->Port = (int)setting('smtp_port', '587');
            $m->SMTPAuth = true;
            $m->Username = setting('smtp_user');
            $m->Password = setting('smtp_pass');
            $m->SMTPSecure = $m->Port === 465 ? 'ssl' : 'tls';
            $m->setFrom(setting('smtp_user'), setting('smtp_from_name', 'Pearl Creamery'));
            $m->addAddress($to);
            $m->Subject = $subject;
            $m->Body = $body;
            $m->send();
            return true;
        } catch (Exception $ex) {
            error_log('Mail error: ' . $ex->getMessage());
            return false;
        }
    }
    return @mail($to, $subject, $body);
}
