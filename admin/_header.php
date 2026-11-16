<?php require_once __DIR__ . '/auth.php'; require_admin(); $page = basename($_SERVER['PHP_SELF']); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin | Pearl Creamery</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet"></head>
<body>
<nav class="navbar navbar-expand pc-nav"><div class="container">
  <span class="navbar-brand pc-logo"><img src="../assets/monogram.png" alt="">Pearl Creamery Admin</span>
  <div class="navbar-nav ms-auto align-items-lg-center">
    <a class="nav-link" href="index.php" onclick="if (history.length > 1) { history.back(); return false; }">&larr; Back</a>
    <?php foreach (['index.php'=>'Dashboard','orders.php'=>'Orders','products.php'=>'Products','settings.php'=>'Settings'] as $f=>$l): ?>
      <a class="nav-link <?= $page===$f?'fw-bold':'' ?>" href="<?= $f ?>"><?= $l ?></a>
    <?php endforeach; ?>
    <a class="nav-link <?= $page==='setup_admin.php'?'fw-bold':'' ?>" href="setup_admin.php">Add admin</a>
    <a class="nav-link text-danger" href="logout.php">Log out</a>
  </div>
</div></nav>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var active = document.querySelector('.pc-nav .nav-link.fw-bold');
  if (active && window.matchMedia('(max-width: 767.98px)').matches) {
    active.scrollIntoView({ inline: 'center', block: 'nearest' });
  }
});
</script>
<div class="container py-4">
