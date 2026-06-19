/* ============================================================
   COAPH – main.js
   Vanilla JS puro – sem dependências externas
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── NAVBAR HIDE ON SCROLL ────────────────────────── */
  const navbar = document.getElementById('navbar');
  let lastScrollY = 0;
  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    if (y <= 10) {
      navbar.classList.remove('navbar--hidden');
    } else if (y > lastScrollY + 6) {
      navbar.classList.add('navbar--hidden');
    } else if (y < lastScrollY - 6) {
      navbar.classList.remove('navbar--hidden');
    }
    lastScrollY = y;
  }, { passive: true });

  /* ── BURGER MENU ───────────────────────────────────── */
  const burger = document.getElementById('burger');
  const menu   = document.querySelector('.navbar__menu');

  burger.addEventListener('click', () => {
    menu.classList.toggle('open');
    const open = menu.classList.contains('open');
    burger.setAttribute('aria-expanded', open);
    // Animate burger spans
    const spans = burger.querySelectorAll('span');
    if (open) {
      spans[0].style.transform = 'translateY(7px) rotate(45deg)';
      spans[1].style.opacity   = '0';
      spans[2].style.transform = 'translateY(-7px) rotate(-45deg)';
    } else {
      spans[0].style.transform = '';
      spans[1].style.opacity   = '';
      spans[2].style.transform = '';
    }
  });

  // Mobile dropdown toggle
  document.querySelectorAll('.has-dropdown > a').forEach(link => {
    link.addEventListener('click', (e) => {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        link.parentElement.classList.toggle('open');
      }
    });
  });

  // Close menu on outside click
  document.addEventListener('click', (e) => {
    if (!navbar.contains(e.target)) {
      menu.classList.remove('open');
      burger.setAttribute('aria-expanded', false);
      const spans = burger.querySelectorAll('span');
      spans[0].style.transform = '';
      spans[1].style.opacity   = '';
      spans[2].style.transform = '';
    }
  });

  /* ── TOP BANNER ROTATIVO ───────────────────────────── */
  (function () {
    const slides = document.querySelectorAll('.top-banner__slide');
    const dots   = document.querySelectorAll('.top-banner__dot');
    if (!slides.length) return;

    let current     = 0;
    let timer       = null;
    let isAnimating = false;
    const DURATION  = 650;
    const EASING    = 'cubic-bezier(0.22, 1, 0.36, 1)';

    function goTo(n, dir) {
      if (isAnimating) return;
      const prevIdx = current;
      current = (n + slides.length) % slides.length;
      if (prevIdx === current) return;

      const outgoing = slides[prevIdx];
      const incoming = slides[current];

      dots[prevIdx].classList.remove('active');
      dots[current].classList.add('active');

      const fromX = dir > 0 ? '100%' : '-100%';
      const toX   = dir > 0 ? '-100%' : '100%';

      // Position incoming off-screen instantly
      incoming.style.transition = 'none';
      incoming.style.transform  = `translateX(${fromX})`;
      incoming.style.zIndex     = '3';
      incoming.classList.add('active');

      incoming.offsetHeight; // force reflow

      // Animate both
      const t = `transform ${DURATION}ms ${EASING}`;
      incoming.style.transition = t;
      incoming.style.transform  = 'translateX(0)';
      outgoing.style.transition = t;
      outgoing.style.transform  = `translateX(${toX})`;

      isAnimating = true;
      setTimeout(() => {
        outgoing.classList.remove('active');
        outgoing.style.cssText = '';
        incoming.style.cssText = '';
        isAnimating = false;
      }, DURATION);
    }

    function startAuto() { timer = setInterval(() => goTo(current + 1, 1), 5000); }
    function resetAuto() { clearInterval(timer); startAuto(); }

    document.getElementById('topBannerNext').addEventListener('click', () => { goTo(current + 1,  1); resetAuto(); });
    document.getElementById('topBannerPrev').addEventListener('click', () => { goTo(current - 1, -1); resetAuto(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i, i >= current ? 1 : -1); resetAuto(); }));

    // Drag / swipe
    const track = document.getElementById('topBanner');
    let dragStartX  = null;
    let dragStartY  = null;
    let isDragging  = false;

    function finalizeDrag(clientX) {
      if (!isDragging) return;
      const delta = clientX - dragStartX;
      if (Math.abs(delta) > 50) { delta < 0 ? goTo(current + 1, 1) : goTo(current - 1, -1); resetAuto(); }
    }

    const isText = el => !!el.closest('p, h1, h2, h3, h4, h5, h6, span, a, button');

    track.addEventListener('mousedown', e => {
      if (e.button !== 0 || isText(e.target)) return;
      dragStartX = e.clientX;
      dragStartY = e.clientY;
      isDragging = false;
    });

    track.addEventListener('mousemove', e => {
      if (dragStartX === null) return;
      const dx = Math.abs(e.clientX - dragStartX);
      const dy = Math.abs(e.clientY - dragStartY);
      if (!isDragging && dx > 6 && dx > dy) {
        isDragging = true;
        track.style.cursor = 'grabbing';
      }
    });

    track.addEventListener('mouseup', e => {
      finalizeDrag(e.clientX);
      dragStartX = null;
      isDragging = false;
      track.style.cursor = '';
    });

    track.addEventListener('mouseleave', e => {
      finalizeDrag(e.clientX);
      dragStartX = null;
      isDragging = false;
      track.style.cursor = '';
    });

    track.addEventListener('click', e => { if (isDragging) e.preventDefault(); }, true);

    // Touch
    track.addEventListener('touchstart', e => {
      dragStartX = e.touches[0].clientX;
      dragStartY = e.touches[0].clientY;
      isDragging = false;
    }, { passive: true });
    track.addEventListener('touchmove', e => {
      if (dragStartX === null) return;
      if (Math.abs(e.touches[0].clientX - dragStartX) > 6) isDragging = true;
    }, { passive: true });
    track.addEventListener('touchend', e => {
      if (dragStartX === null) return;
      const delta = e.changedTouches[0].clientX - dragStartX;
      if (Math.abs(delta) > 50) { delta < 0 ? goTo(current + 1, 1) : goTo(current - 1, -1); resetAuto(); }
      dragStartX = null;
      isDragging = false;
    });

    startAuto();
  }());

  /* ── HERO SLIDER ───────────────────────────────────── */
  const slides  = document.querySelectorAll('.hero__slide');
  const dots    = document.querySelectorAll('.hero__dot');
  let current   = 0;
  let autoTimer = null;
  let animating = false;
  const SLIDE_MS = 520;

  function goToSlide(n, dir) {
    if (animating) return;
    const next = (n + slides.length) % slides.length;
    if (next === current) return;

    animating = true;
    const isForward = dir === undefined ? next > current : dir >= 0;
    const incoming  = slides[next];
    const outgoing  = slides[current];

    // Place incoming off-screen (no transition yet)
    incoming.style.transition = 'none';
    incoming.style.transform  = isForward ? 'translateX(100%)' : 'translateX(-100%)';

    // Force paint so browser registers start position
    incoming.getBoundingClientRect();

    // Enable transition on both
    const ease = `transform ${SLIDE_MS}ms cubic-bezier(0.37, 0, 0.63, 1)`;
    incoming.style.transition = ease;
    outgoing.style.transition = ease;

    // Slide to final positions
    incoming.style.transform = 'translateX(0)';
    outgoing.style.transform = isForward ? 'translateX(-100%)' : 'translateX(100%)';

    // Update classes & dots
    incoming.classList.add('active');
    dots[current].classList.remove('active');
    dots[next].classList.add('active');
    current = next;

    setTimeout(() => {
      outgoing.classList.remove('active');
      outgoing.style.cssText  = '';  // snap to CSS default (off-screen, invisible)
      incoming.style.transition = '';
      incoming.style.transform  = '';
      animating = false;
    }, SLIDE_MS);
  }

  function startAuto() {
    autoTimer = setInterval(() => goToSlide(current + 1, 1), 5000);
  }
  function resetAuto() {
    clearInterval(autoTimer);
    startAuto();
  }

  const heroNext = document.getElementById('heroNext');
  const heroPrev = document.getElementById('heroPrev');
  if (heroNext) heroNext.addEventListener('click', () => { goToSlide(current + 1,  1); resetAuto(); });
  if (heroPrev) heroPrev.addEventListener('click', () => { goToSlide(current - 1, -1); resetAuto(); });
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => { goToSlide(i, i >= current ? 1 : -1); resetAuto(); });
  });

  /* ── SWIPE (touch only) ────────────────────────────── */
  const heroEl   = document.querySelector('.hero__track');
  const DRAG_MIN = 60;
  let dragStartX = null;

  if (heroEl) {
    heroEl.addEventListener('touchstart', e => { dragStartX = e.touches[0].clientX; }, { passive: true });
    heroEl.addEventListener('touchend',   e => {
      if (dragStartX === null) return;
      const delta = e.changedTouches[0].clientX - dragStartX;
      dragStartX = null;
      if (Math.abs(delta) < DRAG_MIN) return;
      if (delta < 0) { goToSlide(current + 1,  1); } else { goToSlide(current - 1, -1); }
      resetAuto();
    }, { passive: true });
  }

  if (slides.length > 1) startAuto();

  /* ── SCROLL REVEAL ─────────────────────────────────── */

  // Tag elements with reveal classes
  const revealMap = [
    // selector, extra class, stagger within parent
    { sel: '.section-label',                     cls: '',               stagger: false },
    { sel: '.about__headline',                   cls: '',               stagger: false },
    { sel: '.about__text',                       cls: '',               stagger: false },
    { sel: '.about__stat',                       cls: '',               stagger: true  },
    { sel: '.about__images',                     cls: 'reveal--left',   stagger: false },
    { sel: '.about__content',                    cls: 'reveal--right',  stagger: false },
    { sel: '.services__headline',                cls: '',               stagger: false },
    { sel: '.service-card',                      cls: '',               stagger: true  },
    { sel: '.process__headline',                 cls: '',               stagger: false },
    { sel: '.process__step',                     cls: '',               stagger: true  },
    { sel: '.noticias__headline',                cls: '',               stagger: false },
    { sel: '.noticias__card',                    cls: '',               stagger: true  },
    { sel: '.numeros__card',                     cls: '',               stagger: true  },
    { sel: '.valores__item',                     cls: '',               stagger: true  },
    { sel: '.footer__col',                       cls: '',               stagger: true  },

    // Páginas internas (ex.: Sobre)
    { sel: '.proposito__headline',                cls: '',               stagger: false },
    { sel: '.proposito__card',                    cls: '',               stagger: true  },
    { sel: '.servicos-band__top',                 cls: '',               stagger: false },
    { sel: '.servicos-band__item',                cls: '',               stagger: true  },
    { sel: '.team__header',                       cls: '',               stagger: false },
    { sel: '.team__member',                       cls: '',               stagger: true  },
    { sel: '.estrutura__content',                 cls: 'reveal--left',   stagger: false },
    { sel: '.estrutura__video',                   cls: 'reveal--right',  stagger: false },
    { sel: '.sedes__header',                      cls: '',               stagger: false },
    { sel: '.sede-card',                          cls: '',               stagger: true  },
    { sel: '.coop-info__img',                     cls: 'reveal--left',   stagger: false },
    { sel: '.coop-info__content',                 cls: 'reveal--right',  stagger: false },
    { sel: '.principios__top',                    cls: '',               stagger: false },
    { sel: '.principio-item',                     cls: '',               stagger: true  },
    { sel: '.ramos__title',                       cls: '',               stagger: false },
    { sel: '.ramo-item',                          cls: '',               stagger: true  },

    // Pré-Cadastro
    { sel: '.beneficios__top',                    cls: '',               stagger: false },
    { sel: '.beneficio-item',                     cls: '',               stagger: true  },
    { sel: '.estrutura-band__top',                cls: '',               stagger: false },
    { sel: '.apoio__top',                         cls: '',               stagger: false },
    { sel: '.apoio-item',                         cls: '',               stagger: true  },
    { sel: '.profissionais-band__content',        cls: 'reveal--left',   stagger: false },
    { sel: '.profissionais-band__item',           cls: '',               stagger: true  },
    { sel: '.direitos__content',                  cls: 'reveal--right',  stagger: false },
    { sel: '.cadastro__top',                      cls: '',               stagger: false },
    { sel: '.cadastro__docs',                     cls: 'reveal--left',   stagger: false },
    { sel: '.cadastro__cta',                      cls: 'reveal--right',  stagger: false },

    // Termo de Adesão
    { sel: '.ta-intro__content',                  cls: 'reveal--left',   stagger: false },
    { sel: '.ta-intro__visual',                   cls: 'reveal--right',  stagger: false },
    { sel: '.ta-destaques__top',                  cls: '',               stagger: false },
    { sel: '.ta-destaque-card',                   cls: '',               stagger: true  },
    { sel: '.ta-processo__text',                  cls: 'reveal--left',   stagger: false },
    { sel: '.ta-processo__visual',                cls: 'reveal--right',  stagger: false },
    { sel: '.ta-cta__text',                       cls: 'reveal--left',   stagger: false },
    { sel: '.ta-cta__contacts',                   cls: 'reveal--right',  stagger: false },

    // Governança
    { sel: '.gv-intro__content',                  cls: 'reveal--left',   stagger: false },
    { sel: '.gv-intro__visual',                   cls: 'reveal--right',  stagger: false },
    { sel: '.gv-docs__top',                       cls: '',               stagger: false },
    { sel: '.gv-accordion__item',                 cls: '',               stagger: true  },
    { sel: '.gv-cta__text',                       cls: 'reveal--left',   stagger: false },
    { sel: '.gv-cta__contacts',                   cls: 'reveal--right',  stagger: false },
    // Fala do Presidente
    { sel: '.fp-carta__inner',                    cls: '',               stagger: false },
  ];

  revealMap.forEach(({ sel, cls }) => {
    document.querySelectorAll(sel).forEach(el => {
      el.classList.add('reveal');
      if (cls) el.classList.add(cls);
    });
  });

  const staggerSelectors = revealMap.filter(r => r.stagger).map(r => r.sel).join(', ');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;

      const el       = entry.target;
      const siblings = Array.from(el.parentElement.children).filter(c => c.classList.contains('reveal'));
      const idx      = siblings.indexOf(el);
      const isStagger = el.matches(staggerSelectors);
      const delay    = isStagger ? idx * 90 : 0;

      setTimeout(() => el.classList.add('visible'), delay);
      observer.unobserve(el);
    });
  }, { threshold: 0.12 });

  // Espera o navegador pintar o estado oculto inicial antes de observar —
  // sem isso, elementos já visíveis no primeiro frame (ex.: topo de páginas
  // internas) ficam "visible" antes da transição rodar e aparecem secos.
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      setTimeout(() => {
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
      }, 60);
    });
  });

  /* ── SMOOTH ACTIVE NAV ─────────────────────────────── */
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.navbar__menu > li > a');

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.getAttribute('id');
        navLinks.forEach(link => {
          link.parentElement.classList.remove('active');
          if (link.getAttribute('href') === `#${id}`) {
            link.parentElement.classList.add('active');
          }
        });
      }
    });
  }, { threshold: 0.4 });

  sections.forEach(s => sectionObserver.observe(s));


  /* ── NEWSLETTER FORM ───────────────────────────────── */
  const newsletterBtn = document.querySelector('.footer__newsletter .btn--red');
  if (newsletterBtn) {
    newsletterBtn.addEventListener('click', () => {
      const nameInput  = document.querySelector('.newsletter__form input[type="text"]');
      const emailInput = document.querySelector('.newsletter__form input[type="email"]');
      const check      = document.querySelector('.newsletter__check input[type="checkbox"]');

      if (!emailInput.value || !emailInput.value.includes('@')) {
        emailInput.style.borderColor = '#ffaaaa';
        emailInput.focus();
        return;
      }
      if (!check.checked) {
        check.nextElementSibling.style.opacity = '1';
        check.nextElementSibling.style.color   = '#ffeeaa';
        return;
      }

      newsletterBtn.textContent = '✓ Cadastro realizado!';
      newsletterBtn.disabled    = true;
      nameInput.value  = '';
      emailInput.value = '';
      check.checked    = false;
    });
  }

  /* ── MAPA LAZY ─────────────────────────────────────── */
  (function () {
    const placeholder = document.getElementById('mapaPlaceholder');
    const iframe      = document.getElementById('mapaIframe');
    if (!placeholder || !iframe) return;

    placeholder.addEventListener('click', () => {
      iframe.src = iframe.dataset.src;
      placeholder.style.display = 'none';
      iframe.classList.add('visible');
    });
  }());

  /* ── LIGHTBOX ──────────────────────────────────────── */
  (function () {
    const images   = [...document.querySelectorAll('[data-lightbox]')].map(el => el.querySelector('img').src);
    const triggers = document.querySelectorAll('[data-lightbox]');
    const lb       = document.getElementById('lightbox');
    const lbImg    = document.getElementById('lightboxImg');
    const lbThumbs = document.getElementById('lightboxThumbs');
    if (!triggers.length) return;

    let current = 0;

    images.forEach((src, i) => {
      const t = document.createElement('img');
      t.src = src; t.className = 'lightbox__thumb'; t.alt = `Foto ${i + 1}`;
      t.addEventListener('click', () => goTo(i));
      lbThumbs.appendChild(t);
    });

    function goTo(n) {
      current = (n + images.length) % images.length;
      lbImg.classList.add('fade');
      setTimeout(() => { lbImg.src = images[current]; lbImg.classList.remove('fade'); }, 180);
      document.querySelectorAll('.lightbox__thumb').forEach((t, i) => t.classList.toggle('active', i === current));
    }

    function open(n) {
      current = n;
      lbImg.src = images[current];
      document.querySelectorAll('.lightbox__thumb').forEach((t, i) => t.classList.toggle('active', i === current));
      lb.classList.add('open');
      lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function close() {
      lb.classList.remove('open');
      lb.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    triggers.forEach(el => el.addEventListener('click', () => open(+el.dataset.lightbox)));
    document.getElementById('lightboxClose').addEventListener('click', close);
    document.getElementById('lightboxOverlay').addEventListener('click', close);
    document.getElementById('lightboxPrev').addEventListener('click', () => goTo(current - 1));
    document.getElementById('lightboxNext').addEventListener('click', () => goTo(current + 1));

    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape')     close();
      if (e.key === 'ArrowLeft')  goTo(current - 1);
      if (e.key === 'ArrowRight') goTo(current + 1);
    });
  }());

  /* ── FADE TO LINK (Portal do Cooperado) ────────────── */
  document.querySelectorAll('.js-fade-link').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const overlay = document.createElement('div');
      overlay.className = 'page-fade-overlay';
      document.body.appendChild(overlay);
      requestAnimationFrame(() => overlay.classList.add('active'));
      overlay.addEventListener('transitionend', () => {
        window.location.href = link.href;
      }, { once: true });
    });
  });

  /* ── NOSSA ESTRUTURA — CARROSSEL INFINITO (Pré-Cadastro) ── */
  (function () {
    const carousel = document.getElementById('estruturaCarousel');
    if (!carousel) return;

    const track   = carousel.querySelector('.estrutura-carousel__track');
    const dotsBox = document.getElementById('estruturaDots');
    const prevBtn = carousel.querySelector('.estrutura-carousel__arrow--prev');
    const nextBtn = carousel.querySelector('.estrutura-carousel__arrow--next');

    // ── Clona slides: [cópia esq.] [originais] [cópia dir.]
    const orig = [...track.children];
    const N    = orig.length;
    track.innerHTML = '';
    const mkClone = s => { const c = s.cloneNode(true); c.setAttribute('aria-hidden', 'true'); return c; };
    orig.forEach(s => track.appendChild(mkClone(s)));
    orig.forEach(s => track.appendChild(s));
    orig.forEach(s => track.appendChild(mkClone(s)));
    const all = [...track.children]; // 3 × N

    // ── 3 dots fixos ciclando com current % 3
    const DOT_N = 3;
    for (let i = 0; i < DOT_N; i++) {
      const d = document.createElement('button');
      d.setAttribute('aria-label', `Grupo ${i + 1}`);
      d.addEventListener('click', () => { if (!moving) goToReal(i); });
      dotsBox.appendChild(d);
    }
    const dots = [...dotsBox.children];

    let current      = 0;
    let moving       = false;
    let programmatic = false; // bloqueia loopCheck durante animações programáticas

    function syncDots() {
      const active = current % DOT_N;
      dots.forEach((d, i) => d.classList.toggle('active', i === active));
    }

    function leftOf(idx) {
      const tr = track.getBoundingClientRect();
      const sl = all[idx].getBoundingClientRect();
      return track.scrollLeft + (sl.left - tr.left);
    }

    function jumpTo(idx) {
      programmatic = true;
      track.style.scrollSnapType = 'none';
      track.scrollLeft = leftOf(idx);
      requestAnimationFrame(() => requestAnimationFrame(() => {
        track.style.scrollSnapType = '';
        setTimeout(() => { programmatic = false; }, 80);
      }));
    }

    function smoothTo(idx) {
      programmatic = true;
      track.scrollTo({ left: leftOf(idx), behavior: 'smooth' });
    }

    function loopCheck() {
      const tr = track.getBoundingClientRect();
      let closest = 0, best = Infinity;
      all.forEach((s, i) => {
        const d = Math.abs(s.getBoundingClientRect().left - tr.left);
        if (d < best) { best = d; closest = i; }
      });
      if (closest < N || closest >= 2 * N) {
        current = closest % N;
        jumpTo(N + current); // reset silencioso para zona real
      } else {
        current = closest - N;
        programmatic = false;
      }
      syncDots();
      moving = false;
    }

    // Só roda loopCheck ao arrastar/touch (não durante scroll programático)
    track.addEventListener('scroll', () => {
      if (programmatic) return;
      clearTimeout(track._lt);
      track._lt = setTimeout(loopCheck, 150);
    }, { passive: true });

    function goToReal(i) {
      moving = true;
      current = i;
      smoothTo(N + i);
      syncDots();
      setTimeout(() => { loopCheck(); }, 520);
    }

    prevBtn.addEventListener('click', () => {
      if (moving) return;
      moving = true;
      smoothTo(N + current - 1);
      current = (current - 1 + N) % N;
      syncDots();
      setTimeout(() => { loopCheck(); }, 520);
    });

    nextBtn.addEventListener('click', () => {
      if (moving) return;
      moving = true;
      smoothTo(N + current + 1);
      current = (current + 1) % N;
      syncDots();
      setTimeout(() => { loopCheck(); }, 520);
    });

    // Inicializa no slide real 0
    jumpTo(N);
    syncDots();
  }());

  /* ── PDF MODAL (Termo de Adesão) ───────────────────── */
  (function () {
    const modal  = document.getElementById('pdfModal');
    if (!modal) return;

    const frame  = document.getElementById('pdfModalFrame');
    const title  = document.getElementById('pdfModalTitle');

    function open(src, label) {
      frame.src = src;
      title.textContent = label || '';
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      frame.src = '';
    }

    document.querySelectorAll('.js-pdf-modal').forEach(btn => {
      btn.addEventListener('click', () => open(btn.dataset.pdf, btn.dataset.title));
    });
    document.getElementById('pdfModalOverlay').addEventListener('click', close);
    document.getElementById('pdfModalClose').addEventListener('click', close);
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('open')) close();
    });
  }());

  /* ── Governança Accordion ── */
  function closeAccordion(item) {
    const body = item.querySelector('.gv-accordion__body');
    body.style.height = body.scrollHeight + 'px';
    requestAnimationFrame(() => {
      requestAnimationFrame(() => { body.style.height = '0'; });
    });
    item.classList.remove('open');
    item.querySelector('.gv-accordion__trigger').setAttribute('aria-expanded', 'false');
  }

  function openAccordion(item, instant) {
    item.classList.add('open');
    item.querySelector('.gv-accordion__trigger').setAttribute('aria-expanded', 'true');
    const body = item.querySelector('.gv-accordion__body');
    if (instant) {
      body.style.transition = 'none';
      body.style.height = 'auto';
      requestAnimationFrame(() => { body.style.transition = ''; });
      return;
    }
    body.style.height = body.scrollHeight + 'px';
    const onEnd = (e) => {
      if (e.propertyName === 'height') {
        if (item.classList.contains('open')) body.style.height = 'auto';
        body.removeEventListener('transitionend', onEnd);
      }
    };
    body.addEventListener('transitionend', onEnd);
  }

  /* Abre todos ao carregar sem animação */
  document.querySelectorAll('.gv-accordion__item').forEach(item => openAccordion(item, true));

  document.querySelectorAll('.gv-accordion__trigger').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const item = trigger.closest('.gv-accordion__item');
      if (item.classList.contains('open')) {
        closeAccordion(item);
      } else {
        openAccordion(item, false);
      }
    });
  });

});
