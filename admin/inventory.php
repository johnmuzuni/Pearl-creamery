<?php
require __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$page = 'inventory';
$pdo = db();
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        // Past orders reference products, so MySQL blocks deleting a product that
        // has been ordered. Tell the admin to hide it instead of showing a 500.
        $id = (int)($_POST['id'] ?? 0);
        try {
            $del = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $del->execute([$id]);
            $msg = $del->rowCount() ? 'Product deleted.' : 'That product no longer exists.';
        } catch (Throwable $ex) {
            $err = 'This product appears in past orders, so it cannot be deleted. Untick "In stock" to hide it from the shop instead.';
        }
    } else {
        $name = trim($_POST['name'] ?? ''); $cat = trim($_POST['category'] ?? '');
        $size = trim($_POST['size'] ?? ''); $desc = trim($_POST['description'] ?? '');
        $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000000]]);
        $avail = isset($_POST['is_available']) ? 1 : 0;
        if ($name === '' || $cat === '' || $size === '' || $price === false) {
            $err = 'Name, category, size and a whole-number price are all required.';
        } elseif ($action === 'add') {
            $pdo->prepare('INSERT INTO products (name, category, description, price, size, is_available) VALUES (?,?,?,?,?,?)')
                ->execute([$name, $cat, $desc, $price, $size, $avail]);
            $msg = "Added {$name}.";
        } elseif ($action === 'save') {
            $pdo->prepare('UPDATE products SET name=?, category=?, description=?, price=?, size=?, is_available=? WHERE id=?')
                ->execute([$name, $cat, $desc, $price, $size, $avail, (int)($_POST['id'] ?? 0)]);
            $msg = "Saved {$name}.";
        }
    }
}
$products = $pdo->query('SELECT * FROM products ORDER BY category, id')->fetchAll();
include __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="h4 fw-bold mb-3">Inventory</h1>
<?php if ($msg): ?><div class="alert alert-success" role="status"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger" role="alert"><?= h($err) ?></div><?php endif; ?>
<p class="text-muted small">Turn off &ldquo;In stock&rdquo; to hide a product from the shop. You can delete a product that has never been ordered; products used by past orders must be hidden instead so those orders keep their details.</p>

<div class="panel mb-4">
<?php foreach ($products as $p): $i = (int)$p['id']; ?>
  <form method="post" class="order-card row g-2 align-items-end">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $i ?>">
    <div class="col-md-3"><label class="form-label small mb-0" for="n<?= $i ?>">Name</label><input class="form-control form-control-sm" id="n<?= $i ?>" name="name" value="<?= h($p['name']) ?>" required></div>
    <div class="col-6 col-md-2"><label class="form-label small mb-0" for="c<?= $i ?>">Category</label><input class="form-control form-control-sm" id="c<?= $i ?>" name="category" value="<?= h($p['category']) ?>" required></div>
    <div class="col-6 col-md-1"><label class="form-label small mb-0" for="s<?= $i ?>">Size</label><input class="form-control form-control-sm" id="s<?= $i ?>" name="size" value="<?= h($p['size']) ?>" required></div>
    <div class="col-6 col-md-2"><label class="form-label small mb-0" for="p<?= $i ?>">Price (UGX)</label><input class="form-control form-control-sm" id="p<?= $i ?>" name="price" type="number" min="0" step="1" value="<?= (int)$p['price'] ?>" required></div>
    <div class="col-6 col-md-2 d-flex align-items-center gap-2">
      <div class="form-check mb-1"><input class="form-check-input" type="checkbox" name="is_available" id="a<?= $i ?>" <?= $p['is_available'] ? 'checked' : '' ?>><label class="form-check-label small" for="a<?= $i ?>">In stock</label></div>
    </div>
    <div class="col-md-2 d-flex gap-2 justify-content-md-end">
      <button class="btn btn-sm btn-pearl" name="action" value="save">Save</button>
      <button class="btn btn-sm btn-outline-danger btn-pill" name="action" value="delete" formnovalidate
              aria-label="Delete <?= h($p['name']) ?>"
              onclick="return confirm('Delete this product? This cannot be undone.');"><i class="bi bi-trash" aria-hidden="true"></i></button>
    </div>
    <div class="col-12"><label class="form-label small mb-0" for="d<?= $i ?>">Description</label><input class="form-control form-control-sm" id="d<?= $i ?>" name="description" value="<?= h($p['description']) ?>"></div>
  </form>
<?php endforeach; ?>
</div>

<h2 class="h5 fw-bold">Add a product</h2>
<form method="post" class="panel p-3 row g-2 align-items-end">
  <?= csrf_field() ?>
  <div class="col-md-3"><label class="form-label small mb-0" for="nn">Name</label><input class="form-control form-control-sm" id="nn" name="name" required></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-0" for="nc">Category</label><input class="form-control form-control-sm" id="nc" name="category" placeholder="Yoghurt" required></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-0" for="ns">Size</label><input class="form-control form-control-sm" id="ns" name="size" placeholder="250 ml" required></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-0" for="np">Price (UGX)</label><input class="form-control form-control-sm" id="np" name="price" type="number" min="0" step="1" required></div>
  <div class="col-6 col-md-1"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_available" id="na" checked><label class="form-check-label small" for="na">In stock</label></div></div>
  <div class="col-md-2 text-md-end"><button class="btn btn-sm btn-pearl" name="action" value="add">Add product</button></div>
  <div class="col-12"><label class="form-label small mb-0" for="nd">Description</label><input class="form-control form-control-sm" id="nd" name="description"></div>
</form>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
