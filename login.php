<?php
session_start();

if (!empty($_SESSION['coaph_user'])) {
    $next = $_GET['next'] ?? 'index.html';
    header('Location: ' . $next);
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $usersFile = __DIR__ . '/data/users.json';
    $users     = file_exists($usersFile) ? (json_decode(file_get_contents($usersFile), true) ?? []) : [];

    $matched = false;
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['coaph_user'] = [
                'username' => $user['username'],
                'name'     => $user['name'],
                'role'     => $user['role'],
            ];
            $matched = true;
            $next = $_GET['next'] ?? 'index.html';
            header('Location: ' . $next);
            exit();
        }
    }

    if (!$matched) {
        $error = 'Usuário ou senha incorretos.';
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Minha Conta — COAPH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css?v=20" />
  <style>
    /* Split de fundo: vermelho na esquerda, cinza na direita */
    .login-section {
      background: linear-gradient(to right, var(--red) 52%, #f3f4f6 52%);
      min-height: calc(100vh - 129px);
    }
    .login-section__inner {
      display: flex;
      min-height: calc(100vh - 129px);
      max-width: 1260px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* ── Coluna esquerda ── */
    .login-hero {
      flex: 0 0 52%;
      display: flex;
      align-items: center;
      /* recua o padding do container para o conteudo ficar no limite correto */
      margin-left: -24px;
      padding-left: 24px;
    }
    .login-hero__content {
      padding: 60px 48px 60px 0;
      color: #fff;
    }
    .login-hero__logo {
      height: 40px;
      width: auto;
      margin-bottom: 44px;
      display: block;
    }
    .login-hero__eyebrow {
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .18em;
      color: rgba(255,255,255,.65);
      margin-bottom: 14px;
    }
    .login-hero__title {
      font-size: clamp(1.7rem, 2.6vw, 2.4rem);
      font-weight: 900;
      line-height: 1.2;
      margin: 0 0 14px;
    }
    .login-hero__title span { color: rgba(255,200,200,1); }
    .login-hero__sub {
      font-size: .92rem;
      color: rgba(255,255,255,.75);
      line-height: 1.7;
      margin: 0 0 40px;
      max-width: 380px;
    }
    .login-hero__features {
      list-style: none;
      padding: 0; margin: 0;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .login-hero__features li {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: .88rem;
      color: rgba(255,255,255,.88);
    }
    .login-hero__features li svg {
      flex-shrink: 0;
      color: rgba(255,200,200,.9);
    }

    /* ── Coluna direita ── */
    .login-form-col {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f9fafb;
      padding: 48px 32px;
    }
    .login-box {
      width: 100%;
      max-width: 400px;
    }
    .login-box__title {
      font-size: 1.6rem;
      font-weight: 900;
      color: #1a1a1a;
      margin: 0 0 6px;
    }
    .login-box__sub {
      font-size: .87rem;
      color: #6b7280;
      margin: 0 0 36px;
    }

    .login-field { margin-bottom: 20px; }
    .login-field label {
      display: block;
      font-size: .8rem;
      font-weight: 700;
      color: #374151;
      margin-bottom: 7px;
      letter-spacing: .02em;
      text-transform: uppercase;
    }
    .login-field input {
      width: 100%;
      padding: 13px 16px;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      font-family: var(--font-body);
      font-size: .95rem;
      color: #1a1a1a;
      background: #fff;
      transition: border-color .18s, box-shadow .18s;
      outline: none;
    }
    .login-field input:focus {
      border-color: var(--red);
      box-shadow: 0 0 0 3px rgba(150,2,15,.08);
    }

    .login-pw-wrap { position: relative; }
    .login-pw-wrap input { padding-right: 46px; }
    .login-pw-toggle {
      position: absolute;
      right: 14px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      cursor: pointer; color: #9ca3af;
      display: flex; align-items: center;
      padding: 0; transition: color .15s;
    }
    .login-pw-toggle:hover { color: var(--red); }

    .login-error {
      background: #fef2f2;
      border: 1.5px solid #fca5a5;
      color: #991b1b;
      border-radius: 10px;
      padding: 11px 16px;
      font-size: .84rem;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .login-btn {
      width: 100%;
      padding: 14px;
      background: var(--red);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: var(--font-body);
      font-size: .97rem;
      font-weight: 700;
      cursor: pointer;
      transition: background .18s, transform .1s;
      margin-top: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .login-btn:hover { background: var(--red-dark); }
    .login-btn:active { transform: scale(.98); }

    .login-divider {
      text-align: center;
      margin: 24px 0;
      position: relative;
      color: #d1d5db;
      font-size: .78rem;
    }
    .login-divider::before,
    .login-divider::after {
      content: '';
      position: absolute;
      top: 50%;
      width: calc(50% - 28px);
      height: 1px;
      background: #e5e7eb;
    }
    .login-divider::before { left: 0; }
    .login-divider::after  { right: 0; }

    .login-contact-btn {
      display: block;
      width: 100%;
      padding: 13px;
      background: #fff;
      color: #374151;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      font-family: var(--font-body);
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      transition: border-color .18s, color .18s;
    }
    .login-contact-btn:hover { border-color: var(--red); color: var(--red); }

    /* ── Responsivo ── */
    @media (max-width: 820px) {
      .login-section { background: var(--red); }
      .login-section__inner { flex-direction: column; min-height: auto; padding: 0; }
      .login-hero { flex: none; margin-left: 0; padding-left: 24px; }
      .login-hero__content { padding: 40px 24px; }
      .login-hero__features { display: none; }
      .login-form-col { background: #f3f4f6; padding: 40px 24px; }
    }
  </style>
</head>
<body>

  <!-- TOP BAR -->
  <div class="topbar">
    <div class="container topbar__inner">
      <a href="index.html" class="topbar__logo">
        <img src="assets/logotipo/coaph logo branco.png" alt="COAPH" class="topbar__logo-img" />
      </a>
      <div class="topbar__search">
        <input type="text" placeholder="Pesquisar..." />
        <button aria-label="Buscar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </button>
      </div>
      <div class="topbar__actions">
        <a href="#avisos" aria-label="Notificações" class="topbar__action-icon-btn">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="topbar__action-badge"></span>
        </a>
        <a href="#favoritos" aria-label="Favoritos" class="topbar__action-icon-btn">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </a>
        <a href="login.php" aria-label="Minha Conta" class="topbar__action-icon-btn">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
        </a>
      </div>
    </div>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar" id="navbar">
    <div class="container navbar__inner">
      <ul class="navbar__menu">
        <li><a href="index.html">Início</a></li>
        <li class="has-dropdown">
          <a href="#">Quem Somos <span class="arrow">▾</span></a>
          <ul class="dropdown">
            <li><a href="sobre.html">Sobre</a></li>
            <li><a href="sedes.html">Nossas Sedes</a></li>
            <li><a href="cooperativismo.html">Cooperativismo</a></li>
          </ul>
        </li>
        <li class="has-dropdown">
          <a href="#">Área do Cooperado <span class="arrow">▾</span></a>
          <ul class="dropdown">
            <li><a href="pre-cadastro.html">Pré-Cadastro</a></li>
            <li><a href="https://areadocooperado.coaph.com.br/area_restrita_login.php" class="js-fade-link">Portal do Cooperado</a></li>
            <li><a href="termo-adesao.html">Termo de Adesão</a></li>
          </ul>
        </li>
        <li><a href="governanca.html">Governança Cooperativa</a></li>
        <li><a href="fala-do-presidente.html">Fala do Presidente</a></li>
        <li><a href="clube-coaph.html">Clube Coaph+</a></li>
        <li><a href="nep.html">NEP</a></li>
        <li><a href="blog.html">Blog</a></li>
        <li class="has-dropdown">
          <a href="#">Fale Conosco <span class="arrow">▾</span></a>
          <ul class="dropdown">
            <li><a href="trabalhe-conosco.html">Trabalhe Conosco</a></li>
            <li><a href="ouvidoria.html">Ouvidoria</a></li>
            <li><a href="canal-de-etica.html">Canal de Ética</a></li>
          </ul>
        </li>
      </ul>
      <button class="navbar__burger" id="burger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- LOGIN SECTION -->
  <section class="login-section">
    <div class="login-section__inner">

    <!-- Esquerda: hero -->
    <div class="login-hero">
      <div class="login-hero__content">
        <p class="login-hero__eyebrow">// Painel Administrativo</p>
        <h1 class="login-hero__title">Área restrita<br>à <span>equipe interna</span></h1>
        <p class="login-hero__sub">Acesso exclusivo para colaboradores autorizados da COAPH. Credenciais fornecidas pela gestão.</p>
        <ul class="login-hero__features">
          <li>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Gestão de avisos e comunicados
          </li>
          <li>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Atendimento ao Canal de Ética
          </li>
          <li>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Tickets de suporte e denúncias
          </li>
          <li>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Manutenção de informações do site
          </li>
        </ul>
      </div>
    </div>

    <!-- Direita: formulário -->
    <div class="login-form-col">
      <div class="login-box">
        <h2 class="login-box__title">Entrar na Plataforma</h2>
        <p class="login-box__sub">Use suas credenciais para acessar.</p>

        <?php if ($error): ?>
        <div class="login-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
          <div class="login-field">
            <label for="username">Usuário</label>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Digite seu usuário"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
              autocomplete="username"
              required
            />
          </div>

          <div class="login-field">
            <label for="password">Senha</label>
            <div class="login-pw-wrap">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button type="button" class="login-pw-toggle" id="pwToggle" aria-label="Mostrar senha">
                <svg id="pwIconShow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg id="pwIconHide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="login-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Acessar Plataforma
          </button>
        </form>

        <div class="login-divider">ou</div>

        <a href="ouvidoria.html" class="login-contact-btn">Fale com a COAPH</a>
      </div>
    </div>

    </div><!-- /.login-section__inner -->
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container footer__grid">
      <div class="footer__col">
        <h4>Institucional</h4>
        <ul>
          <li><a href="blog.html">Blog</a></li>
          <li><a href="pre-cadastro.html">Pré-Cadastro</a></li>
          <li><a href="trabalhe-conosco.html">Trabalhe Conosco</a></li>
        </ul>
        <div class="footer__logo-mark">
          <img src="assets/logotipo/coaph logo branco.png" alt="COAPH" class="footer__logo-img" />
        </div>
      </div>
      <div class="footer__col">
        <h4>Entre em contato</h4>
        <p>Telefone: <a href="tel:08530393030">(85) 3039-3030</a></p>
        <p>E-mail: <a href="mailto:faleconosco@coaph.com.br">faleconosco@coaph.com.br</a></p>
        <p class="footer__address">MATRIZ – Rua Joaquim Sá, 538 – Joaquim Távora – Fortaleza – CE, 60135-218 CNPJ: 11.769.319/0001-88</p>
      </div>
      <div class="footer__col footer__newsletter">
        <h4>Assine nossa Newsletter</h4>
        <div class="newsletter__form">
          <input type="text" placeholder="Nome" />
          <input type="email" placeholder="E-mail" />
          <label class="newsletter__check">
            <input type="checkbox" />
            <span>Aceito receber e-mails promocionais da Coaph</span>
          </label>
          <button class="btn btn--red btn--full">Assinar newsletter</button>
        </div>
      </div>
    </div>
    <div class="footer__bottom">
      <p>Coaph 2026 © Todos os direitos reservados.</p>
    </div>
  </footer>

  <a href="https://wa.me/558530393030" class="whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
  </a>

  <script src="main.js?v=23"></script>
  <script>
  (function () {
    var toggle   = document.getElementById('pwToggle');
    var input    = document.getElementById('password');
    var iconShow = document.getElementById('pwIconShow');
    var iconHide = document.getElementById('pwIconHide');
    if (!toggle) return;
    toggle.addEventListener('click', function () {
      var hidden = input.type === 'password';
      input.type             = hidden ? 'text'     : 'password';
      iconShow.style.display = hidden ? 'none'     : '';
      iconHide.style.display = hidden ? ''         : 'none';
    });
  })();
  </script>
</body>
</html>
