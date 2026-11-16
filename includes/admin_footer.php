</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/password-toggle.js"></script>
<script>
// Keep the current tab visible in the horizontally scrolling admin bar.
(function () {
  var box = document.querySelector('.admin-scroll');
  var active = box && box.querySelector('.nav-link.active');
  if (!box || !active) return;
  var b = box.getBoundingClientRect();
  var a = active.getBoundingClientRect();
  // Only move the nav strip itself, never the page.
  box.scrollLeft += (a.left - b.left) - (box.clientWidth - a.width) / 2;
})();
</script>
</body></html>
