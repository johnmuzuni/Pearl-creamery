<?php require_once __DIR__ . '/db.php'; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Thank you | Pearl Creamery</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Jost:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet"></head>
<body><div class="container py-5"><div class="pc-card p-4 mx-auto" style="max-width:520px">
<h1 class="h4">Thank you!</h1>
<p>Your order <strong><?= e($_GET['ref'] ?? '') ?></strong> was received. We will confirm payment and call you before delivery.</p>
<a class="btn pc-btn" href="index.php">Back to the menu</a></div></div></body></html>
