<?php
// Expects $admin and $page ('orders' | 'inventory' | 'settings') to be set by the caller.
$nav = ['orders' => ['index.php', 'Orders'], 'inventory' => ['inventory.php', 'Inventory'], 'settings' => ['settings.php', 'Settings']];
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pearl Creamery Admin</title>
<meta name="robots" content="noindex">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/pearl.css" rel="stylesheet">
<link href="../assets/css/admin.css" rel="stylesheet">
</head><body>
<nav class="navbar navbar-expand pc-nav py-2"><div class="container-xl admin-bar">
  <a class="navbar-brand d-flex align-items-center gap-2 flex-shrink-0" href="index.php"><span class="brand-mark">P</span><span class="brand-name">Pearl Creamery <small class="text-muted fw-normal">Admin</small></span></a>
  <div class="admin-scroll">
    <ul class="navbar-nav">
      <?php foreach ($nav as $key => [$href, $label]): ?>
        <li class="nav-item"><a class="nav-link <?= $page === $key ? 'active fw-bold' : '' ?>" href="<?= $href ?>"><?= $label ?></a></li>
      <?php endforeach; ?>
    </ul>
    <span class="small text-muted mx-3 d-none d-md-inline"><?= h($admin['username']) ?></span>
  </div>
  <a class="btn btn-sm btn-outline-pearl admin-logout" href="logout.php">Log out</a>
</div></nav>
<main class="container-xl py-4">
