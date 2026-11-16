<?php require_once __DIR__ . '/_header.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $f = [trim($_POST['name']), trim($_POST['category']), trim($_POST['description']), max(0, (int)$_POST['price']), trim($_POST['size']), isset($_POST['is_available']) ? 1 : 0];
    if ($f[0] !== '') {
        if ($id) $pdo->prepare('UPDATE products SET name=?,category=?,description=?,price=?,size=?,is_available=? WHERE id=?')->execute([...$f, $id]);
        else     $pdo->prepare('INSERT INTO products (name,category,description,price,size,is_available) VALUES (?,?,?,?,?,?)')->execute($f);
    }
    header('Location: products.php'); exit;
}
$rows = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
$rows[] = ['id'=>0,'name'=>'','category'=>'Yoghurt','description'=>'','price'=>0,'size'=>'','is_available'=>1];
?>
<h1 class="h4 mb-3">Products</h1>
<?php foreach ($rows as $r): ?>
<form method="post" class="pc-card p-3 mb-2">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label small"><?= $r['id'] ? 'Name' : 'New product name' ?></label><input name="name" class="form-control form-control-sm" value="<?= e($r['name']) ?>" required></div>
    <div class="col-md-2"><label class="form-label small">Category</label><input name="category" class="form-control form-control-sm" value="<?= e($r['category']) ?>"></div>
    <div class="col-md-2"><label class="form-label small">Price (UGX)</label><input name="price" type="number" min="0" class="form-control form-control-sm" value="<?= (int)$r['price'] ?>"></div>
    <div class="col-md-1"><label class="form-label small">Size</label><input name="size" class="form-control form-control-sm" value="<?= e($r['size']) ?>"></div>
    <div class="col-md-2"><label class="form-label small">Description</label><input name="description" class="form-control form-control-sm" value="<?= e($r['description']) ?>"></div>
    <div class="col-md-1"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_available" id="a<?= $r['id'] ?>" <?= $r['is_available']?'checked':'' ?>><label class="form-check-label small" for="a<?= $r['id'] ?>">In stock</label></div></div>
    <div class="col-md-1"><button class="btn btn-sm pc-btn w-100"><?= $r['id'] ? 'Save' : 'Add' ?></button></div>
  </div>
</form>
<?php endforeach; require __DIR__ . '/_footer.php'; ?>
