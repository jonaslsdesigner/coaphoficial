document.addEventListener('DOMContentLoaded', function () {

  /* ── CARROSSEL DE LOGOS ─────────────────────────────── */
  (function () {
    const track   = document.getElementById('clubeLogosTrack');
    const btnPrev = document.getElementById('logosArrowPrev');
    const btnNext = document.getElementById('logosArrowNext');
    const counter = document.getElementById('logosCounter');
    if (!track) return;

    const items = track.querySelectorAll('.clube-logos__item');
    let page = 0;

    function getPerPage() {
      if (window.innerWidth <= 600) return 2;
      if (window.innerWidth <= 900) return 3;
      return 5;
    }

    function totalPages() { return Math.ceil(items.length / getPerPage()); }

    function render() {
      const pp    = getPerPage();
      const total = totalPages();
      page = Math.max(0, Math.min(page, total - 1));

      const itemEl   = items[0];
      const itemW    = itemEl.offsetWidth;
      const gap      = 16;
      const offset   = page * pp * (itemW + gap);

      track.style.transform = `translateX(-${offset}px)`;
      counter.textContent   = `${page + 1} / ${total}`;
      btnPrev.disabled = page === 0;
      btnNext.disabled = page >= total - 1;
    }

    btnPrev.addEventListener('click', function () { page--; render(); });
    btnNext.addEventListener('click', function () { page++; render(); });
    window.addEventListener('resize', function () { page = 0; render(); });

    render();
  }());

  /* ── FILTRO DE CATEGORIAS ───────────────────────────── */
  (function () {
    const filtros = document.querySelectorAll('.clube-filtros__btn');
    const cards   = document.querySelectorAll('.clube-card');

    function applyFilter(cat) {
      filtros.forEach(function (b) { b.classList.remove('active'); });
      filtros.forEach(function (b) { if (b.dataset.cat === cat) b.classList.add('active'); });
      cards.forEach(function (card) {
        card.style.display = (cat === 'todos' || card.dataset.cat === cat) ? '' : 'none';
      });
    }

    filtros.forEach(function (btn) {
      btn.addEventListener('click', function () { applyFilter(btn.dataset.cat); });
    });

    const activeBtn = document.querySelector('.clube-filtros__btn.active');
    if (activeBtn) applyFilter(activeBtn.dataset.cat);
  }());

});
