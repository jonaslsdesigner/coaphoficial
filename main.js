/* ============================================================
   COAPH – main.js
   Vanilla JS puro – sem dependências externas
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── NAVBAR STICKY SHADOW ──────────────────────────── */
  const navbar = document.getElementById('navbar');

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
    let dragStartX = null;
    let isDragging = false;

    const inContent = el => !!el.closest('.top-banner__content');

    track.addEventListener('mousedown', e => {
      if (inContent(e.target)) return;
      e.preventDefault();
      dragStartX = e.clientX;
      isDragging = false;
      track.style.cursor = 'grabbing';
    });
    track.addEventListener('mousemove', e => {
      if (dragStartX === null) return;
      if (Math.abs(e.clientX - dragStartX) > 5) isDragging = true;
    });
    track.addEventListener('mouseup', e => {
      track.style.cursor = '';
      if (dragStartX === null) return;
      const delta = e.clientX - dragStartX;
      if (Math.abs(delta) > 50) { delta < 0 ? goTo(current + 1, 1) : goTo(current - 1, -1); resetAuto(); }
      dragStartX = null;
    });
    track.addEventListener('mouseleave', e => {
      track.style.cursor = '';
      if (dragStartX === null) return;
      const delta = e.clientX - dragStartX;
      if (Math.abs(delta) > 50) { delta < 0 ? goTo(current + 1, 1) : goTo(current - 1, -1); resetAuto(); }
      dragStartX = null;
    });
    track.addEventListener('touchstart', e => {
      if (inContent(e.target)) return;
      dragStartX = e.touches[0].clientX; isDragging = false;
    }, { passive: true });
    track.addEventListener('touchmove',  e => {
      if (dragStartX === null) return;
      if (Math.abs(e.touches[0].clientX - dragStartX) > 5) isDragging = true;
    }, { passive: true });
    track.addEventListener('touchend', e => {
      if (dragStartX === null) return;
      const delta = e.changedTouches[0].clientX - dragStartX;
      if (Math.abs(delta) > 50) { delta < 0 ? goTo(current + 1, 1) : goTo(current - 1, -1); resetAuto(); }
      dragStartX = null;
    });
    track.addEventListener('click', e => { if (isDragging) e.preventDefault(); }, true);

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

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

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

});
