<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pearl Creamery | Pure. Creamy. Delicious.</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/pearl.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg sticky-top pc-nav py-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#top">
        <span class="brand-mark">P</span>
        <span class="lh-1"><span class="brand-name d-block">Pearl Creamery</span><small class="brand-sub">Goodness in every spoon</small></span>
      </a>
      <div class="d-flex align-items-center gap-2 ms-auto">
        <a class="nav-link d-none d-md-inline px-3" href="#catalog">Our yoghurts</a>
        <a class="btn btn-outline-pearl btn-sm d-none d-sm-inline-flex align-items-center gap-1" href="admin/login.php"><i class="bi bi-lock" aria-hidden="true"></i> Staff login</a>
        <button class="btn btn-pearl px-4" data-bs-toggle="offcanvas" data-bs-target="#cartPanel" aria-label="Open cart">
          Cart <span id="cartCount" class="badge rounded-pill text-bg-light ms-1">0</span>
        </button>
      </div>
    </div>
  </nav>

  <header id="top" class="hero py-5">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <h1 class="font-serif display-4 fw-bold">Pure. Creamy. Delicious.</h1>
          <p class="lead hero-copy mt-3">Fresh yoghurt made in Kampala and brought to your door. No account, no sign-up. Pick your flavours, share your location and we deliver.</p>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <a href="#catalog" class="btn btn-pearl btn-lg px-4">Order yoghurt</a>
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= rawurlencode('Hello Pearl Creamery, I would like to order yoghurt.') ?>" class="btn btn-whatsapp btn-lg px-4" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1" aria-hidden="true"></i> Chat on WhatsApp</a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="weather-card" id="weatherCard" aria-live="polite">
            <div class="d-flex align-items-baseline gap-2">
              <span class="temp" id="wxTemp">--</span><span class="text-muted" id="wxPlace">Kampala</span>
            </div>
            <p class="mb-0 mt-2" id="wxMsg">Checking the weather for a yoghurt suggestion&hellip;</p>
          </div>
        </div>
      </div>
    </div>
  </header>

  <section id="catalog" class="py-5">
    <div class="container">
      <h2 class="font-serif fw-bold mb-1">Our yoghurts</h2>
      <p class="text-muted mb-4">Made fresh and kept cold until it reaches you.</p>
      <div id="productGrid" class="row g-4"><p class="text-muted">Loading products&hellip;</p></div>
    </div>
  </section>

  <footer class="pc-footer py-5">
    <div class="container">
      <div class="row gy-4 align-items-center">
        <div class="col-md-5 text-center text-md-start">
          <p class="mb-1 font-serif fs-4 text-navy">Pearl Creamery</p>
          <p class="mb-0 small">Fresh yoghurt, made in Kampala.<br>Call <a href="tel:+256780457866"><?= CONTACT_PHONE ?></a></p>
        </div>
        <div class="col-md-4 text-center">
          <p class="small fw-semibold mb-2">Follow and chat with us</p>
          <div class="d-flex justify-content-center gap-2">
            <a class="social-btn social-wa" href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank" rel="noopener" aria-label="Pearl Creamery on WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
            <a class="social-btn social-ig" href="<?= htmlspecialchars(INSTAGRAM_URL, ENT_QUOTES) ?>" target="_blank" rel="noopener" aria-label="Pearl Creamery on Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
            <a class="social-btn" href="tel:+256780457866" aria-label="Call Pearl Creamery"><i class="bi bi-telephone" aria-hidden="true"></i></a>
          </div>
        </div>
        <div class="col-md-3 text-center text-md-end">
          <a class="btn btn-outline-pearl btn-sm" href="admin/login.php"><i class="bi bi-lock me-1" aria-hidden="true"></i>Staff login</a>
        </div>
      </div>
      <p class="text-center small mt-4 mb-0">&copy; <?= date('Y') ?> Pearl Creamery. Natural &bull; Fresh &bull; Real.</p>
    </div>
  </footer>

  <a class="wa-float" href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= rawurlencode('Hello Pearl Creamery!') ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>

  <!-- Cart -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="cartPanel">
    <div class="offcanvas-header"><h5 class="font-serif fw-bold mb-0">Your cart</h5><button class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
    <div class="offcanvas-body d-flex flex-column">
      <div id="cartItems" class="flex-grow-1"></div>
      <div class="border-top pt-3">
        <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total</span><span id="cartTotal">UGX 0</span></div>
        <button id="checkoutBtn" class="btn btn-pearl w-100 py-2" data-bs-toggle="modal" data-bs-target="#checkoutModal" data-bs-dismiss="offcanvas" disabled>Checkout as guest</button>
      </div>
    </div>
  </div>

  <!-- Guest checkout -->
  <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="coTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title font-serif fw-bold" id="coTitle">Delivery details</h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <form id="orderForm" novalidate>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="fName">Full name</label><input class="form-control" id="fName" required autocomplete="name"></div>
            <div class="col-md-6"><label class="form-label" for="fPhone">Phone number</label><input class="form-control" id="fPhone" type="tel" placeholder="0780 457866" required autocomplete="tel"></div>
            <div class="col-12"><label class="form-label" for="fEmail">Email <span class="text-muted">(optional)</span></label><input class="form-control" id="fEmail" type="email" autocomplete="email"></div>
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-end mb-1"><label class="form-label mb-0" for="fAddress">Delivery address</label>
                <button type="button" class="btn btn-sm btn-outline-pearl" id="gpsBtn">Use my location</button></div>
              <textarea class="form-control" id="fAddress" rows="2" required placeholder="Street, area, landmark"></textarea>
              <div class="form-text" id="gpsStatus"></div>
            </div>
            <div class="col-12"><label class="form-label" for="fNotes">Delivery notes <span class="text-muted">(optional)</span></label><input class="form-control" id="fNotes" placeholder="Gate colour, floor, who to ask for"></div>
            <div class="col-12">
              <p class="form-label mb-2">Payment</p>
              <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="pay" id="payCod" value="cod" checked><label class="btn btn-outline-pearl" for="payCod">Cash on delivery</label>
                <input type="radio" class="btn-check" name="pay" id="payMomo" value="mobile_money"><label class="btn btn-outline-pearl" for="payMomo">Mobile Money (MTN / Airtel)</label>
              </div>
              <div class="form-text" id="payHelp">Pay the rider when your yoghurt arrives.</div>
            </div>
          </div>
          <div id="formError" class="alert alert-danger mt-3 d-none" role="alert"></div>
        </form>
        <div id="orderDone" class="text-center py-4 d-none">
          <h4 class="font-serif fw-bold">Order received</h4>
          <p class="mb-1">Your reference is</p>
          <p class="order-code" id="doneCode"></p>
          <p class="text-muted">We will call you on the number you gave to confirm delivery.</p>
          <a class="btn btn-pearl" id="waLink" target="_blank" rel="noopener">Message us on WhatsApp</a>
        </div>
      </div>
      <div class="modal-footer justify-content-between" id="coFooter">
        <span class="fw-bold" id="coTotal">UGX 0</span>
        <button class="btn btn-pearl px-4" id="placeBtn" form="orderForm" type="submit">Place order</button>
      </div>
    </div></div>
  </div>

  <script>window.PC = { whatsapp: "<?= WHATSAPP_NUMBER ?>" };</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
