<?php require_once __DIR__ . '/_header.php';
$pdo = db();
$statuses = ['Pending','Processing','Delivered','Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)$_POST['id']; $s = $_POST['status'] ?? '';
    if (in_array($s, $statuses, true)) {
        $pdo->prepare('UPDATE orders SET order_status = ? WHERE id = ?')->execute([$s, $id]);
    }
    header('Location: orders.php'); exit;
}
$filter = $_GET['status'] ?? '';
// Item names come from order_items -> products, which works whether or not the
// legacy product_id/quantity columns exist on orders.
$sql = 'SELECT o.*,
        GROUP_CONCAT(CONCAT(oi.quantity, " x ", COALESCE(p.name, "Item")) SEPARATOR ", ") AS items,
        pay.payment_method, pay.status pay_status
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.product_id
        LEFT JOIN payments pay ON pay.order_id = o.id';
$args = [];
if (in_array($filter, $statuses, true)) { $sql .= ' WHERE o.order_status = ?'; $args[] = $filter; }
$st = $pdo->prepare($sql . ' GROUP BY o.id ORDER BY o.created_at DESC LIMIT 200');
$st->execute($args);
$orders = $st->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Orders</h1>
  <div>
    <a class="btn btn-sm pc-btn-ghost" href="orders.php">All</a>
    <?php foreach ($statuses as $s): ?><a class="btn btn-sm pc-btn-ghost" href="?status=<?= $s ?>"><?= $s ?></a><?php endforeach; ?>
  </div>
</div>
<div class="pc-card p-3 table-responsive">
<table class="table align-middle mb-0">
<thead><tr><th>Ref</th><th>Customer</th><th>Item</th><th>Total</th><th>Payment</th><th>Delivery</th><th>Status</th></tr></thead><tbody>
<?php foreach ($orders as $o):
  $map = ($o['latitude'] && $o['longitude']) ? 'https://www.google.com/maps/dir/?api=1&destination=' . $o['latitude'] . ',' . $o['longitude'] : ''; ?>
<tr>
  <td><?= e($o['order_code']) ?><div class="small text-muted"><?= e($o['created_at']) ?></div></td>
  <td><?= e($o['customer_name']) ?><div class="small"><a href="tel:<?= e($o['customer_phone']) ?>"><?= e($o['customer_phone']) ?></a></div></td>
  <td><?= e($o['items']) ?></td>
  <td>UGX <?= number_format($o['total_price']) ?></td>
  <td><?= e($o['payment_method']) ?><div class="small text-muted"><?= e($o['pay_status']) ?></div></td>
  <td class="small"><?= e($o['formatted_address']) ?>
    <?php if ($o['delivery_notes']): ?><div class="text-muted"><?= e($o['delivery_notes']) ?></div><?php endif; ?>
    <?php if ($map): ?><a href="<?= e($map) ?>" target="_blank" rel="noopener">Get directions</a><?php endif; ?></td>
  <td><form method="post" class="d-flex gap-1">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
    <select name="status" class="form-select form-select-sm" aria-label="Order status">
      <?php foreach ($statuses as $s): ?><option <?= $s===$o['order_status']?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
    </select><button class="btn btn-sm pc-btn">Save</button></form></td>
</tr>
<?php endforeach; if (!$orders): ?><tr><td colspan="7" class="text-muted">No orders found.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require __DIR__ . '/_footer.php'; ?>
