<?php
session_start();
if (empty($_SESSION['coaph_user'])) {
    header('Location: login.php?next=conta.php');
    exit();
}
$user = $_SESSION['coaph_user'];

$avatar = null;
foreach (['jpg','png','webp','gif'] as $ext) {
    if (file_exists(__DIR__ . "/assets/avatars/{$user['username']}.{$ext}")) {
        $avatar = "assets/avatars/{$user['username']}.{$ext}";
        break;
    }
}
$initials = strtoupper(substr($user['name'], 0, 1));
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Minha Conta — COAPH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css?v=20" />
  <style>
    /* ── Page bg ── */
    .conta-page { background: #f1f5f9; min-height: calc(100vh - 129px); padding-bottom: 64px; }

    /* ── Profile header ── */
    .profile-header {
      background: #fff;
      border-bottom: 1px solid #e2e8f0;
    }

    /* ── Cover ── */
    .profile-cover {
      height: 152px;
      background: linear-gradient(120deg, #6b0009 0%, #8c010e 40%, #96020f 65%, #7a010c 100%);
      position: relative;
      overflow: hidden;
    }
    .profile-cover::before {
      content: '';
      position: absolute;
      bottom: -90px; left: -60px;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: rgba(255,255,255,.07);
      pointer-events: none;
    }
    .profile-cover::after {
      content: '';
      position: absolute;
      top: 0; right: 0; bottom: 0; left: 0;
      background: linear-gradient(to bottom right, transparent 55%, rgba(0,0,0,.18) 100%);
      pointer-events: none;
    }

    .profile-header__top {
      display: flex; align-items: center; gap: 24px;
      max-width: 1260px; margin: 0 auto;
      padding: 32px 24px 24px;
    }
    .profile-avatar-wrap { position: relative; flex-shrink: 0; cursor: pointer; }
    .profile-avatar {
      width: 96px; height: 96px; border-radius: 50%;
      background: #96020f; color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: 2.4rem; font-weight: 900;
      overflow: hidden;
      border: 4px solid #fff;
      box-shadow: 0 2px 16px rgba(0,0,0,.12);
      transition: opacity .15s;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-avatar-wrap:hover .profile-avatar { opacity: .85; }
    .profile-avatar__cam {
      position: absolute; bottom: 2px; right: 2px;
      width: 28px; height: 28px; border-radius: 50%;
      background: #96020f; color: #fff;
      display: flex; align-items: center; justify-content: center;
      border: 2px solid #fff;
      box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .profile-meta { flex: 1; }
    .profile-meta__top { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
    .profile-meta__name { font-size: 1.55rem; font-weight: 900; color: #0f172a; }
    .profile-meta__badge {
      font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
      padding: 3px 9px; border-radius: 20px;
      background: #dcfce7; color: #16a34a;
      display: flex; align-items: center; gap: 4px;
    }
    .profile-meta__badge::before {
      content: ''; width: 6px; height: 6px; border-radius: 50%; background: #16a34a;
    }
    .profile-meta__role { font-size: .85rem; color: #64748b; font-weight: 500; }
    .profile-meta__actions { margin-left: auto; display: flex; gap: 8px; align-items: center; }
    .pm-btn {
      display: inline-flex; align-items: center; gap: 6px;
      font-family: 'DM Sans', sans-serif; font-size: .78rem; font-weight: 700;
      padding: 8px 16px; border-radius: 9px; cursor: pointer;
      transition: all .13s; text-decoration: none; border: none;
    }
    .pm-btn--red   { background: #96020f; color: #fff; }
    .pm-btn--red:hover { background: #7a010c; }
    .pm-btn--ghost { background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; }
    .pm-btn--ghost:hover { border-color: #96020f; color: #96020f; }

    /* ── Tabs ── */
    .profile-tabs {
      max-width: 1260px; margin: 0 auto;
      padding: 0 24px;
      display: flex; gap: 0; border-top: 1px solid #f1f5f9;
      margin-top: 12px;
    }
    .tab-btn {
      display: flex; align-items: center; gap: 7px;
      padding: 14px 18px;
      font-family: 'DM Sans', sans-serif;
      font-size: .82rem; font-weight: 700; color: #64748b;
      background: none; border: none; cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -1px; transition: color .13s, border-color .13s;
    }
    .tab-btn:hover { color: #96020f; }
    .tab-btn.active { color: #96020f; border-bottom-color: #96020f; }

    /* ── Content layout ── */
    .conta-body { max-width: 1260px; margin: 0 auto; padding: 28px 24px 0; }
    .conta-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }

    /* ── Cards ── */
    .cc {
      background: #fff; border: 1px solid #e2e8f0;
      border-radius: 16px; overflow: hidden; margin-bottom: 20px;
    }
    .cc__head {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 22px 14px; border-bottom: 1px solid #f1f5f9;
    }
    .cc__head-left { display: flex; align-items: center; gap: 9px; }
    .cc__head-icon { color: #96020f; display: flex; align-items: center; }
    .cc__title { font-size: .88rem; font-weight: 800; color: #96020f; }
    .cc__body { padding: 20px 22px; }

    /* Info grid */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .info-item label { display: block; font-size: .62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px; }
    .info-val { font-size: .86rem; font-weight: 600; color: #0f172a; padding: 9px 13px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; }

    /* Skill bars */
    .skill-bar { margin-bottom: 12px; }
    .skill-bar:last-child { margin-bottom: 0; }
    .skill-bar__row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; }
    .skill-bar__label { font-size: .7rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: .06em; }
    .skill-bar__pct { font-size: .75rem; font-weight: 700; color: #64748b; }
    .skill-bar__track { height: 30px; background: #f1f5f9; border-radius: 6px; overflow: hidden; }
    .skill-bar__fill {
      height: 100%; border-radius: 6px;
      display: flex; align-items: center; padding-left: 12px;
      transition: width .6s ease;
    }
    .skill-bar__fill--red    { background: #96020f; }
    .skill-bar__fill--dark   { background: #1a1f2e; }
    .skill-bar__fill--amber  { background: #d97706; }

    /* Funções / Role card */
    .role-item { display: flex; align-items: flex-start; gap: 14px; }
    .role-item__icon {
      width: 40px; height: 40px; border-radius: 50%;
      background: #f1f5f9; color: #64748b;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .role-item__title { font-size: .86rem; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
    .role-item__desc { font-size: .78rem; color: #64748b; line-height: 1.6; }

    /* Ações rápidas (sidebar) */
    .quick-action {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 0; border-bottom: 1px solid #f1f5f9;
      cursor: pointer; text-decoration: none;
      transition: background .1s;
    }
    .quick-action:last-child { border-bottom: none; }
    .quick-action__icon {
      width: 38px; height: 38px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .quick-action__icon--red   { background: #fee2e2; color: #96020f; }
    .quick-action__icon--slate { background: #f1f5f9; color: #475569; }
    .quick-action__icon--green { background: #f0fdf4; color: #16a34a; }
    .quick-action__label { font-size: .82rem; font-weight: 700; color: #0f172a; }
    .quick-action__sub   { font-size: .7rem; color: #94a3b8; }
    .quick-action:hover .quick-action__label { color: #96020f; }

    /* Status card */
    .status-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .status-item:last-child { border-bottom: none; }
    .status-item__key { font-size: .78rem; font-weight: 600; color: #64748b; }
    .status-item__val { font-size: .78rem; font-weight: 700; color: #0f172a; }
    .dot-green { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #16a34a; margin-right: 5px; }

    /* Tabs content */
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* upload */
    #avatar-input { display: none; }
    .av-msg { font-size: .74rem; margin-top: 6px; min-height: 16px; }
    .av-msg.ok  { color: #16a34a; }
    .av-msg.err { color: #dc2626; }
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
        <button aria-label="Buscar"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button>
      </div>
      <div class="topbar__actions">
        <a href="#avisos" aria-label="Notificações" class="topbar__action-icon-btn">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="topbar__action-badge"></span>
        </a>
        <a href="#favoritos" aria-label="Favoritos" class="topbar__action-icon-btn">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </a>
        <a href="painel.php" aria-label="Painel Admin" class="topbar__action-icon-btn js-fade-link">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </a>
        <a href="conta.php" aria-label="Minha Conta" class="topbar__action-icon-btn" style="padding:0;border-radius:50%;overflow:hidden;width:34px;height:34px;display:flex;align-items:center;justify-content:center;">
          <?php if ($avatar): ?>
            <img src="<?= htmlspecialchars($avatar) ?>?v=<?= time() ?>" alt="Avatar" style="width:34px;height:34px;object-fit:cover;border-radius:50%;" />
          <?php else: ?>
            <span style="width:34px;height:34px;border-radius:50%;background:#96020f;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:800;"><?= $initials ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar" id="navbar">
    <div class="container navbar__inner">
      <ul class="navbar__menu">
        <li><a href="index.html">Início</a></li>
        <li class="has-dropdown"><a href="#">Quem Somos <span class="arrow">▾</span></a>
          <ul class="dropdown">
            <li><a href="sobre.html">Sobre</a></li>
            <li><a href="sedes.html">Nossas Sedes</a></li>
            <li><a href="cooperativismo.html">Cooperativismo</a></li>
          </ul>
        </li>
        <li class="has-dropdown"><a href="#">Área do Cooperado <span class="arrow">▾</span></a>
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
        <li class="has-dropdown"><a href="#">Fale Conosco <span class="arrow">▾</span></a>
          <ul class="dropdown">
            <li><a href="trabalhe-conosco.html">Trabalhe Conosco</a></li>
            <li><a href="ouvidoria.html">Ouvidoria</a></li>
            <li><a href="canal-de-etica.html">Canal de Ética</a></li>
          </ul>
        </li>
      </ul>
      <button class="navbar__burger" id="burger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
  </nav>

  <div class="conta-page">

    <!-- ── PROFILE HEADER ──────────────────────────────── -->
    <div class="profile-header">
      <div class="profile-cover"></div>
      <div class="profile-header__top">
        <div class="profile-avatar-wrap" id="avatar-trigger">
          <div class="profile-avatar" id="profile-avatar">
            <?php if ($avatar): ?>
              <img src="<?= htmlspecialchars($avatar) ?>?v=<?= time() ?>" alt="Avatar" />
            <?php else: ?>
              <?= $initials ?>
            <?php endif; ?>
          </div>
          <div class="profile-avatar__cam">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          </div>
        </div>

        <div class="profile-meta">
          <div class="profile-meta__top">
            <span class="profile-meta__name"><?= htmlspecialchars($user['name']) ?></span>
            <span class="profile-meta__badge">Online</span>
          </div>
          <div class="profile-meta__role"><?= htmlspecialchars($user['role'] ?? 'Administrador') ?> · COAPH</div>
          <div class="av-msg" id="av-msg-header"></div>
        </div>

        <div class="profile-meta__actions">
          <button class="pm-btn pm-btn--red" onclick="document.getElementById('avatar-input').click()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            Alterar foto
          </button>
          <a href="painel.php" class="pm-btn pm-btn--ghost js-fade-link">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Painel CRM
          </a>
        </div>
      </div>

    </div>

    <!-- ── CONTENT ─────────────────────────────────────── -->
    <div class="conta-body">
      <div class="conta-grid">

        <!-- Coluna principal: info da conta -->
        <div class="cc">
          <div class="cc__head">
            <div class="cc__head-left">
              <div class="cc__head-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg></div>
              <span class="cc__title">Informações da Conta</span>
            </div>
          </div>
          <div class="cc__body">
            <div class="info-grid">
              <div class="info-item">
                <label>Nome completo</label>
                <div class="info-val-wrap" style="display:flex;gap:8px;align-items:center;">
                  <div class="info-val" id="name-display" style="flex:1"><?= htmlspecialchars($user['name']) ?></div>
                  <input id="name-input" class="info-val" style="flex:1;display:none;border-color:#96020f;outline:none;" value="<?= htmlspecialchars($user['name']) ?>" maxlength="80" />
                  <button id="name-edit-btn" title="Editar nome" style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;display:flex;align-items:center;flex-shrink:0;transition:color .15s" onmouseover="this.style.color='#96020f'" onmouseout="this.style.color='#94a3b8'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button id="name-save-btn" title="Salvar" style="background:#96020f;border:none;cursor:pointer;color:#fff;padding:5px 12px;border-radius:7px;font-size:.75rem;font-weight:700;font-family:'DM Sans',sans-serif;display:none;flex-shrink:0">Salvar</button>
                  <button id="name-cancel-btn" title="Cancelar" style="background:none;border:1.5px solid #e2e8f0;cursor:pointer;color:#64748b;padding:5px 10px;border-radius:7px;font-size:.75rem;font-weight:600;font-family:'DM Sans',sans-serif;display:none;flex-shrink:0">✕</button>
                </div>
                <div id="name-msg" style="font-size:.72rem;min-height:14px;margin-top:4px"></div>
              </div>
              <div class="info-item">
                <label>Nome de usuário</label>
                <div class="info-val" style="font-family:monospace"><?= htmlspecialchars($user['username']) ?></div>
              </div>
              <div class="info-item">
                <label>Perfil de acesso</label>
                <div class="info-val"><?= htmlspecialchars($user['role'] ?? 'Administrador') ?></div>
              </div>
              <div class="info-item">
                <label>Situação</label>
                <div class="info-val" style="color:#16a34a"><span class="dot-green"></span>Ativo</div>
              </div>
            </div>

            <!-- Foto de perfil inline -->
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;gap:16px;">
              <div class="profile-avatar" style="width:56px;height:56px;font-size:1.3rem;flex-shrink:0" id="cfg-avatar">
                <?php if ($avatar): ?>
                  <img src="<?= htmlspecialchars($avatar) ?>?v=<?= time() ?>" alt="Avatar" />
                <?php else: echo $initials; endif; ?>
              </div>
              <div>
                <p style="font-size:.78rem;font-weight:600;color:#0f172a;margin-bottom:4px">Foto de perfil</p>
                <p style="font-size:.74rem;color:#94a3b8;margin-bottom:10px">JPG, PNG, WEBP ou GIF · Máx. 2 MB</p>
                <button class="pm-btn pm-btn--red" onclick="document.getElementById('avatar-input').click()">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  Alterar foto
                </button>
              </div>
              <div class="av-msg" id="av-msg" style="margin-left:auto"></div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div>
          <!-- Ações rápidas -->
          <div class="cc">
            <div class="cc__head">
              <div class="cc__head-left">
                <div class="cc__head-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
                <span class="cc__title">Ações Rápidas</span>
              </div>
            </div>
            <div class="cc__body">
              <a href="painel.php" class="quick-action js-fade-link">
                <div class="quick-action__icon quick-action__icon--red">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <div>
                  <div class="quick-action__label">Painel CRM</div>
                  <div class="quick-action__sub">Canal de Ética e chamados</div>
                </div>
              </a>
              <a href="logout.php" class="quick-action">
                <div class="quick-action__icon quick-action__icon--slate">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </div>
                <div>
                  <div class="quick-action__label">Sair da conta</div>
                  <div class="quick-action__sub">Encerrar sessão</div>
                </div>
              </a>
            </div>
          </div>
        </div>

      </div>
    </div><!-- /conta-body -->
  </div><!-- /conta-page -->

  <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/webp,image/gif" />

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
          <label class="newsletter__check"><input type="checkbox" /><span>Aceito receber e-mails promocionais da Coaph</span></label>
          <button class="btn btn--red btn--full">Assinar newsletter</button>
        </div>
      </div>
    </div>
    <div class="footer__bottom"><p>Coaph 2026 © Todos os direitos reservados.</p></div>
  </footer>

  <a href="https://wa.me/558530393030" class="whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
  </a>

  <script src="main.js?v=23"></script>
  <script>
  (function(){
    /* Edição de nome */
    const nameDisplay   = document.getElementById('name-display');
    const nameInput     = document.getElementById('name-input');
    const nameEditBtn   = document.getElementById('name-edit-btn');
    const nameSaveBtn   = document.getElementById('name-save-btn');
    const nameCancelBtn = document.getElementById('name-cancel-btn');
    const nameMsg       = document.getElementById('name-msg');

    function enterEdit() {
      nameDisplay.style.display   = 'none';
      nameInput.style.display     = 'block';
      nameEditBtn.style.display   = 'none';
      nameSaveBtn.style.display   = 'inline-flex';
      nameCancelBtn.style.display = 'inline-flex';
      nameInput.focus();
      nameInput.select();
    }
    function exitEdit() {
      nameDisplay.style.display   = 'block';
      nameInput.style.display     = 'none';
      nameEditBtn.style.display   = 'flex';
      nameSaveBtn.style.display   = 'none';
      nameCancelBtn.style.display = 'none';
      nameMsg.textContent = '';
    }
    function saveName() {
      const val = nameInput.value.trim();
      if (!val) return;
      nameSaveBtn.textContent = '…';
      fetch('api/update-name.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: val })
      })
      .then(r => r.json())
      .then(d => {
        if (d.error) { nameMsg.textContent = d.error; nameMsg.style.color = '#dc2626'; nameSaveBtn.textContent = 'Salvar'; return; }
        nameDisplay.textContent = d.name;
        document.querySelector('.profile-meta__name').textContent = d.name;
        exitEdit();
      })
      .catch(() => { nameMsg.textContent = 'Erro ao salvar.'; nameMsg.style.color = '#dc2626'; nameSaveBtn.textContent = 'Salvar'; });
    }

    nameEditBtn.addEventListener('click', enterEdit);
    nameCancelBtn.addEventListener('click', () => { nameInput.value = nameDisplay.textContent; exitEdit(); });
    nameSaveBtn.addEventListener('click', saveName);
    nameInput.addEventListener('keydown', e => { if (e.key === 'Enter') saveName(); if (e.key === 'Escape') { nameInput.value = nameDisplay.textContent; exitEdit(); } });

    /* Avatar upload */
    const input = document.getElementById('avatar-input');
    const msg   = document.getElementById('av-msg');

    document.getElementById('avatar-trigger').addEventListener('click', () => input.click());

    input.addEventListener('change', function(){
      const file = this.files[0];
      if (!file) return;
      const fd = new FormData();
      fd.append('avatar', file);
      msg.textContent = 'Enviando…'; msg.className = 'av-msg';

      fetch('api/avatar.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
          if (d.error) { msg.textContent = d.error; msg.className = 'av-msg err'; return; }
          msg.textContent = 'Foto atualizada!'; msg.className = 'av-msg ok';
          const src = d.avatar + '?v=' + Date.now();
          const imgTag = `<img src="${src}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;" />`;
          document.getElementById('profile-avatar').innerHTML = imgTag;
          const cfg = document.getElementById('cfg-avatar');
          if (cfg) cfg.innerHTML = imgTag;
          const top = document.querySelector('.topbar__actions a[aria-label="Minha Conta"]');
          if (top) top.innerHTML = `<img src="${src}" alt="Avatar" style="width:34px;height:34px;object-fit:cover;border-radius:50%;display:block;" />`;
        })
        .catch(() => { msg.textContent = 'Erro ao enviar.'; msg.className = 'av-msg err'; });
    });
  }());
  </script>
</body>
</html>
