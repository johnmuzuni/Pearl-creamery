<?php
require __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$page = 'orders';
$statuses = ['Pending', 'Processing', 'Delivered', 'Cancelled'];
$filter = in_array($_GET['status'] ?? '', $statuses, true) ? $_GET['status'] : '';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['order_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'status' && in_array($_POST['status'] ?? '', $statuses, true)) {
        $pdo->prepare('UPDATE orders SET order_status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
        // Cash is collected by the rider on delivery
        if ($_POST['status'] === 'Delivered')
            $pdo->prepare("UPDATE payments SET status = 'Paid' WHERE order_id = ? AND payment_method = 'cod'")->execute([$id]);
    } elseif (($_POST['action'] ?? '') === 'paid') {
        $pdo->prepare("UPDATE payments SET status = 'Paid' WHERE order_id = ?")->execute([$id]);
    }
    header('Location: index.php' . ($filter ? '?status=' . urlencode($filter) : '')); exit;
}

// KPIs (cancelled orders are left out of revenue and top sellers)
$kpi = $pdo->query("SELECT COALESCE(SUM(CASE WHEN order_status <> 'Cancelled' THEN total_price END),0) AS revenue,
                           COUNT(*) AS orders,
                           SUM(order_status = 'Pending') AS pending
                    FROM orders")->fetch();
$top = $pdo->query("SELECT pr.name, SUM(oi.quantity) AS qty FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id AND o.order_status <> 'Cancelled'
                    JOIN products pr ON pr.id = oi.product_id GROUP BY pr.id, pr.name ORDER BY qty DESC LIMIT 5")->fetchAll();

$sql = "SELECT o.*, MAX(p.payment_method) AS pay_method, MAX(p.status) AS pay_status,
               GROUP_CONCAT(CONCAT(oi.quantity, ' x ', pr.name) SEPARATOR ', ') AS items
        FROM orders o
        LEFT JOIN payments p ON p.order_id = o.id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        LEFT JOIN products pr ON pr.id = oi.product_id "
     . ($filter ? 'WHERE o.order_status = :s ' : '')
     . 'GROUP BY o.id ORDER BY o.created_at DESC LIMIT 100';
$st = $pdo->prepare($sql);
$st->execute($filter ? [':s' => $filter] : []);
$orders = $st->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><div class="kpi"><div class="value">UGX <?= number_format((int)$kpi['revenue']) ?></div><div class="label">Total revenue (cancelled excluded)</div></div></div>
  <div class="col-6 col-lg-3"><div class="kpi"><div class="value"><?= (int)$kpi['orders'] ?></div><div class="label">Orders received</div></div></div>
  <div class="col-6 col-lg-3"><div class="kpi"><div class="value"><?= (int)$kpi['pending'] ?></div><div class="label">Pending dispatch</div></div></div>
  <div class="col-6 col-lg-3"><div class="kpi">
    <div class="label mb-1">Top sellers</div>
    <?php if (!$top): ?><div class="small text-muted">No sales yet.</div><?php endif; ?>
    <?php foreach ($top as $t): ?><div class="d-flex justify-content-between small"><span><?= h($t['name']) ?></span><strong><?= (int)$t['qty'] ?></strong></div><?php endforeach; ?>
  </div></div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h1 class="h4 fw-bold mb-0">Orders</h1>
  <div class="btn-group btn-group-sm" role="group" aria-label="Filter by status">
    <a class="btn btn-outline-pearl <?= $filter === '' ? 'active' : '' ?>" href="index.php">All</a>
    <?php foreach ($statuses as $s): ?><a class="btn btn-outline-pearl <?= $filter === $s ? 'active' : '' ?>" href="?status=<?= $s ?>"><?= $s ?></a><?php endforeach; ?>
  </div>
</div>

<div class="panel">
<?php if (!$orders): ?><p class="p-4 mb-0 text-muted">No orders<?= $filter ? ' with status ' . h($filter) : ' yet' ?>.</p><?php endif; ?>
<?php foreach ($orders as $o):
    $hasGps = $o['latitude'] !== null && $o['longitude'] !== null;
    $gmaps = $hasGps ? "https://www.google.com/maps/dir/?api=1&destination={$o['latitude']},{$o['longitude']}"
                     : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($o['formatted_address']);
    $osm = $hasGps ? "https://www.openstreetmap.org/?mlat={$o['latitude']}&mlon={$o['longitude']}#map=17/{$o['latitude']}/{$o['longitude']}" : '';
?>
  <div class="order-card">
    <div class="d-flex flex-wrap justify-content-between gap-2">
      <div>
        <span class="order-code-sm"><?= h($o['order_code']) ?></span>
        <span class="status-pill st-<?= h($o['order_status']) ?> ms-2"><?= h($o['order_status']) ?></span>
        <div class="small text-muted"><?= h(date('D j M, H:i', strtotime($o['created_at']))) ?></div>
      </div>
      <div class="text-md-end">
        <div class="fw-bold">UGX <?= number_format((int)$o['total_price']) ?></div>
        <div class="small"><?= $o['pay_method'] === 'mobile_money' ? 'Mobile Money' : 'Cash on delivery' ?> &bull; <strong><?= h($o['pay_status']) ?></strong></div>
      </div>
    </div>
    <div class="row mt-2 gy-2">
      <div class="col-md-5">
        <div class="fw-semibold"><?= h($o['customer_name']) ?></div>
        <a href="tel:<?= h($o['customer_phone']) ?>"><?= h($o['customer_phone']) ?></a>
        <?php if ($o['customer_email']): ?><div class="small text-muted"><?= h($o['customer_email']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-4 small">
        <div><?= h($o['items'] ?? '') ?></div>
        <?php if ($o['delivery_notes']): ?><div class="text-muted">Note: <?= h($o['delivery_notes']) ?></div><?php endif; ?>
      </div>
      <div class="col-md-3 small">
        <div class="mb-1"><?= h($o['formatted_address']) ?></div>
        <a href="<?= h($gmaps) ?>" target="_blank" rel="noopener"><?= $hasGps ? 'Directions' : 'Find on map' ?></a>
        <?php if ($osm): ?> &bull; <a href="<?= h($osm) ?>" target="_blank" rel="noopener">OpenStreetMap</a><?php endif; ?>
      </div>
    </div>
    <form method="post" class="d-flex flex-wrap gap-2 mt-3 align-items-center">
      <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
      <label class="visually-hidden" for="st<?= (int)$o['id'] ?>">Order status</label>
      <select class="form-select form-select-sm w-auto" name="status" id="st<?= (int)$o['id'] ?>">
        <?php foreach ($statuses as $s): ?><option <?= $s === $o['order_status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-pearl" name="action" value="status">Update status</button>
      <?php if ($o['pay_status'] !== 'Paid'): ?>
        <button class="btn btn-sm btn-outline-pearl" name="action" value="paid">Mark paid</button>
      <?php endif; ?>
    </form>
  </div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
