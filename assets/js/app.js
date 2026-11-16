(function () {
  const KAMPALA = { lat: 0.3476, lng: 32.5825 };
  const $ = (id) => document.getElementById(id);
  const ugx = (n) => 'UGX ' + Number(n).toLocaleString('en-UG');
  const tints = ['#fde4e1', '#f3ead9', '#f5efd0', '#dfe6f5', '#efe3f1', '#e0efe6', '#ece8df'];

  let products = [];
  let cart = {};
  let coords = null;
  try { cart = JSON.parse(localStorage.getItem('pc_cart') || '{}'); } catch (e) { cart = {}; }

  const saveCart = () => { try { localStorage.setItem('pc_cart', JSON.stringify(cart)); } catch (e) {} };
  const el = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text !== undefined) n.textContent = text; return n; };
  const cartTotal = () => products.reduce((s, p) => s + (cart[p.id] || 0) * p.price, 0);
  const cartCount = () => Object.values(cart).reduce((s, q) => s + q, 0);

  function setQty(id, qty) {
    qty = Math.max(0, Math.min(50, qty));
    if (qty === 0) delete cart[id]; else cart[id] = qty;
    saveCart(); renderCart();
  }

  // ---- Catalog ----
  function renderProducts() {
    const grid = $('productGrid');
    grid.replaceChildren();
    if (!products.length) { grid.append(el('p', 'text-muted', 'No yoghurts are available right now. Please call us on +256 780 457866.')); return; }
    products.forEach((p, i) => {
      const col = el('div', 'col-sm-6 col-lg-4');
      const card = el('article', 'product-card');
      const thumb = el('div', 'product-thumb', p.name.charAt(0));
      thumb.style.background = tints[i % tints.length];
      thumb.setAttribute('aria-hidden', 'true');
      const body = el('div', 'product-body');
      body.append(el('span', 'product-meta', p.category + ' \u2022 ' + p.size), el('h3', 'mt-1', p.name), el('p', 'text-muted small mb-0', p.description));
      const foot = el('div', 'product-foot');
      const add = el('button', 'btn btn-pearl btn-sm px-3', 'Add to cart');
      add.type = 'button';
      add.addEventListener('click', () => setQty(p.id, (cart[p.id] || 0) + 1));
      foot.append(el('span', 'price', ugx(p.price)), add);
      body.append(foot);
      card.append(thumb, body); col.append(card); grid.append(col);
    });
  }

  // ---- Cart ----
  function renderCart() {
    const box = $('cartItems');
    box.replaceChildren();
    const lines = products.filter((p) => cart[p.id]);
    if (!lines.length) box.append(el('p', 'text-muted', 'Your cart is empty. Add a yoghurt to get started.'));
    lines.forEach((p) => {
      const row = el('div', 'd-flex justify-content-between align-items-center py-2 border-bottom');
      const info = el('div'); info.append(el('div', 'fw-semibold', p.name), el('small', 'text-muted', p.size + ' \u2022 ' + ugx(p.price)));
      const q = el('div', 'qty');
      const minus = el('button', '', '\u2212'); minus.type = 'button'; minus.setAttribute('aria-label', 'Remove one ' + p.name);
      const plus = el('button', '', '+'); plus.type = 'button'; plus.setAttribute('aria-label', 'Add one ' + p.name);
      minus.addEventListener('click', () => setQty(p.id, cart[p.id] - 1));
      plus.addEventListener('click', () => setQty(p.id, cart[p.id] + 1));
      q.append(minus, el('span', 'fw-bold', String(cart[p.id])), plus);
      row.append(info, q); box.append(row);
    });
    $('cartCount').textContent = cartCount();
    $('cartTotal').textContent = ugx(cartTotal());
    $('coTotal').textContent = ugx(cartTotal());
    $('checkoutBtn').disabled = cartCount() === 0;
  }

  // ---- Weather (Open-Meteo) ----
  function weatherMessage(t) {
    if (t >= 28) return 'It is hot out there. A cold Probiotic Yoghurt Drink or Strawberry Yoghurt is the move.';
    if (t >= 23) return 'Warm and pleasant. A Blueberry Swirl goes nicely with it.';
    return 'A cooler day. Try a Vanilla parfait for something soft and comforting.';
  }
  function loadWeather(lat, lng, place) {
    fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lng + '&current=temperature_2m')
      .then((r) => r.json())
      .then((d) => {
        const t = Math.round(d.current.temperature_2m);
        $('wxTemp').textContent = t + '\u00B0C';
        $('wxPlace').textContent = place;
        $('wxMsg').textContent = weatherMessage(t);
      })
      .catch(() => { $('wxMsg').textContent = 'Weather is unavailable right now, but yoghurt is always in season.'; });
  }

  // ---- GPS (HTML5 + Nominatim) ----
  function useLocation() {
    const status = $('gpsStatus');
    if (!navigator.geolocation) { status.textContent = 'Your browser cannot share location. Type your address instead.'; return; }
    status.textContent = 'Finding your location\u2026';
    navigator.geolocation.getCurrentPosition((pos) => {
      coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
      loadWeather(coords.lat, coords.lng, 'Your location');
      fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + coords.lat + '&lon=' + coords.lng)
        .then((r) => r.json())
        .then((d) => {
          if (d.display_name) $('fAddress').value = d.display_name;
          status.textContent = 'Location added. Check the address and add a landmark if you can.';
        })
        .catch(() => {
          if (!$('fAddress').value) $('fAddress').value = 'GPS: ' + coords.lat.toFixed(5) + ', ' + coords.lng.toFixed(5);
          status.textContent = 'Location saved, but we could not read the street name. Add your area below.';
        });
    }, () => {
      status.textContent = 'Location was blocked. Allow it in your browser settings, or type your address.';
    }, { enableHighAccuracy: true, timeout: 12000 });
  }

  // ---- Checkout ----
  function showError(msg) { const e = $('formError'); e.textContent = msg; e.classList.remove('d-none'); }

  function submitOrder(ev) {
    ev.preventDefault();
    $('formError').classList.add('d-none');
    const btn = $('placeBtn');
    btn.disabled = true; btn.textContent = 'Placing order\u2026';
    const payload = {
      name: $('fName').value, phone: $('fPhone').value, email: $('fEmail').value,
      address: $('fAddress').value, notes: $('fNotes').value,
      payment_method: document.querySelector('input[name=pay]:checked').value,
      lat: coords && coords.lat, lng: coords && coords.lng,
      items: Object.keys(cart).map((id) => ({ id: Number(id), qty: cart[id] })),
    };
    fetch('api/place_order.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
      .then((r) => r.json())
      .then((d) => {
        if (!d.ok) { showError(d.error || 'Something went wrong. Please try again.'); return; }
        $('doneCode').textContent = d.order_code;
        $('waLink').href = 'https://wa.me/' + PC.whatsapp + '?text=' + encodeURIComponent('Hello Pearl Creamery, my order is ' + d.order_code);
        $('orderForm').classList.add('d-none'); $('coFooter').classList.add('d-none'); $('orderDone').classList.remove('d-none');
        cart = {}; saveCart(); renderCart();
      })
      .catch(() => showError('Could not reach the server. Check your connection and try again.'))
      .finally(() => { btn.disabled = false; btn.textContent = 'Place order'; });
  }

  document.addEventListener('DOMContentLoaded', () => {
    $('gpsBtn').addEventListener('click', useLocation);
    $('orderForm').addEventListener('submit', submitOrder);
    document.querySelectorAll('input[name=pay]').forEach((r) => r.addEventListener('change', () => {
      $('payHelp').textContent = r.value === 'cod' ? 'Pay the rider when your yoghurt arrives.' : 'We will send a Mobile Money prompt to your phone after you place the order.';
    }));
    $('checkoutModal').addEventListener('show.bs.modal', () => {
      $('orderForm').classList.remove('d-none'); $('coFooter').classList.remove('d-none'); $('orderDone').classList.add('d-none');
    });
    loadWeather(KAMPALA.lat, KAMPALA.lng, 'Kampala');
    fetch('api/get_products.php').then((r) => r.json()).then((d) => {
      products = d.products || [];
      // drop cart lines for products that no longer exist
      Object.keys(cart).forEach((id) => { if (!products.some((p) => String(p.id) === id)) delete cart[id]; });
      renderProducts(); renderCart();
    }).catch(() => { $('productGrid').replaceChildren(el('p', 'text-danger', 'We could not load the products. Refresh the page or call +256 780 457866.')); });
  });
})();
