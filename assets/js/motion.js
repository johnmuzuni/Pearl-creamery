// Scroll reveals + small parallax. Safe to load before app.js.
(function () {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let io = null;

  window.pcReveal = function () {
    const items = document.querySelectorAll('[data-reveal]:not(.seen)');
    if (reduce || !('IntersectionObserver' in window)) { items.forEach((el) => el.classList.add('seen')); return; }
    if (!io) {
      io = new IntersectionObserver((entries) => {
        entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('seen'); io.unobserve(en.target); } });
      }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    }
    items.forEach((el) => io.observe(el));
  };

  // Bowl photo drifts slightly as you scroll
  const bowl = document.querySelector('.pc-why img');
  if (bowl && !reduce) {
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        const r = bowl.getBoundingClientRect();
        if (r.top < innerHeight && r.bottom > 0) bowl.style.objectPosition = '50% ' + (50 + (r.top / innerHeight - 0.5) * 20) + '%';
        ticking = false;
      });
    }, { passive: true });
  }

  // Hero video: respect reduced motion, and pause when scrolled away
  const vid = document.querySelector('.pc-bgvideo');
  if (vid) {
    if (reduce) { vid.removeAttribute('autoplay'); vid.pause(); }
    else if ('IntersectionObserver' in window) {
      new IntersectionObserver((en) => { en[0].isIntersecting ? vid.play().catch(() => {}) : vid.pause(); }, { threshold: 0.05 }).observe(vid);
    }
  }

  document.addEventListener('DOMContentLoaded', window.pcReveal);
})();
