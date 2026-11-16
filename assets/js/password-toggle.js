// Adds an eye button to every password field so people can check what they typed.
(function () {
  function enhance(input) {
    if (input.dataset.pwReady) return;
    input.dataset.pwReady = '1';
    const wrap = document.createElement('div');
    wrap.className = 'pw-wrap';
    // move the field's vertical spacing to the wrapper so the eye stays centred on the input
    input.className.split(/\s+/).filter((c) => /^m[bt]-\d$/.test(c)).forEach((c) => { wrap.classList.add(c); input.classList.remove(c); });
    input.classList.add('pw-input');
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-toggle';
    btn.setAttribute('aria-label', 'Show password');
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
    btn.addEventListener('click', function () {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.firstChild.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
      input.focus();
    });
    wrap.appendChild(btn);
  }
  function init() { document.querySelectorAll('input[type="password"]').forEach(enhance); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
