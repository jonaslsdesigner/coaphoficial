<?php
session_start();
if (empty($_SESSION['coaph_user'])) { header('Location: login.php?next=painel.php'); exit(); }
$user     = $_SESSION['coaph_user'];
$initials = strtoupper(substr($user['name'], 0, 1));
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Painel Admin — COAPH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --red:        #96020f;
      --red-dark:   #7a010c;
      --red-light:  #fee2e2;
      --red-soft:   #fff5f5;
      --sidebar-bg: #1a1f2e;
      --sidebar-w:  228px;
      --font:       'DM Sans', sans-serif;
      --border:     #e2e8f0;
      --bg:         #f1f5f9;
      --white:      #fff;
      --text:       #0f172a;
      --muted:      #64748b;
      --amber:      #d97706;
      --green:      #16a34a;
    }
    html, body { height: 100%; width: 100%; font-family: var(--font); background: var(--bg); color: var(--text); overflow: hidden; }

    /* ══ SHELL ══════════════════════════════════════════════ */
    .crm { display: flex; height: 100vh; width: 100vw; overflow: hidden; }

    /* ══ SIDEBAR ════════════════════════════════════════════ */
    .sidebar {
      width: var(--sidebar-w); background: var(--sidebar-bg);
      display: flex; flex-direction: column;
      flex-shrink: 0; overflow-y: auto; overflow-x: hidden;
    }
    .sb-brand {
      display: flex; flex-direction: column; gap: 10px;
      padding: 20px 18px 16px;
      border-bottom: 1px solid rgba(255,255,255,.07);
    }
    .sb-brand__logo { height: 36px; width: auto; display: block; align-self: flex-start; }
    .sb-brand__role { font-size: .62rem; color: rgba(255,255,255,.35); font-weight: 600; letter-spacing: .04em; padding-left: 2px; }

    .sb-section { padding: 18px 10px 4px; }
    .sb-section-lbl {
      font-size: .57rem; font-weight: 700; color: rgba(255,255,255,.28);
      text-transform: uppercase; letter-spacing: .13em;
      padding: 0 8px; margin-bottom: 6px;
    }
    .sb-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 10px; border-radius: 10px;
      font-size: .83rem; font-weight: 600;
      color: rgba(255,255,255,.42);
      cursor: pointer; border: none; background: none;
      width: 100%; text-align: left;
      transition: background .14s, color .14s;
      text-decoration: none; margin-bottom: 2px;
    }
    .sb-item:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.8); }
    .sb-item.active { background: var(--red); color: #fff; }
    .sb-item svg { flex-shrink: 0; }
    .sb-item__badge {
      margin-left: auto; background: #ef4444; color: #fff;
      font-size: .58rem; font-weight: 800;
      min-width: 18px; height: 18px; border-radius: 20px;
      display: flex; align-items: center; justify-content: center; padding: 0 5px;
    }
    .sb-item.active .sb-item__badge { background: rgba(255,255,255,.28); }
    .sb-item.disabled { opacity: .28; cursor: default; pointer-events: none; }

    .sb-spacer { flex: 1; }

    .sb-bottom {
      padding: 8px 10px 14px;
      border-top: 1px solid rgba(255,255,255,.07);
    }
    .sb-user {
      display: flex; align-items: center; gap: 9px;
      padding: 10px 10px; margin-bottom: 2px;
    }
    .sb-user__av {
      width: 32px; height: 32px; border-radius: 50%;
      background: var(--red); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .78rem; font-weight: 800; flex-shrink: 0;
    }
    .sb-user__name { font-size: .8rem; font-weight: 700; color: rgba(255,255,255,.75); }
    .sb-user__role { font-size: .62rem; color: rgba(255,255,255,.3); }

    /* ══ MAIN ═══════════════════════════════════════════════ */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

    /* ── Topbar ── */
    .topbar {
      height: 64px; background: var(--white);
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center;
      padding: 0 28px; gap: 14px; flex-shrink: 0;
    }
    .topbar__titles { flex: 1; min-width: 0; }
    .topbar__page { font-size: 1.05rem; font-weight: 800; color: var(--text); line-height: 1.2; }
    .topbar__sub { font-size: .71rem; color: var(--muted); margin-top: 1px; }
    .topbar__search { position: relative; flex-shrink: 0; }
    .topbar__search svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
    .topbar__search input {
      width: 224px; padding: 8px 12px 8px 32px;
      border: 1.5px solid var(--border); border-radius: 10px;
      font-family: var(--font); font-size: .82rem;
      background: #f8fafc; color: var(--text); outline: none;
      transition: border-color .15s, width .2s;
    }
    .topbar__search input:focus { border-color: var(--red); background: var(--white); width: 260px; }
    .topbar__right { display: flex; align-items: center; gap: 8px; }
    .topbar__icon {
      width: 36px; height: 36px; border-radius: 10px;
      border: 1.5px solid var(--border); background: var(--white);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--muted); position: relative;
      transition: all .14s;
    }
    .topbar__icon:hover { border-color: var(--red); color: var(--red); }
    .topbar__icon__dot {
      position: absolute; top: 7px; right: 7px;
      width: 7px; height: 7px; border-radius: 50%;
      background: var(--red); border: 1.5px solid var(--white);
    }
    .topbar__user {
      display: flex; align-items: center; gap: 8px;
      padding: 5px 12px 5px 6px;
      border: 1.5px solid var(--border); border-radius: 50px;
      cursor: pointer; transition: border-color .14s;
    }
    .topbar__user:hover { border-color: var(--red); }
    .topbar__avatar {
      width: 30px; height: 30px; border-radius: 50%;
      background: var(--red); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .76rem; font-weight: 800;
    }
    .topbar__uname { font-size: .8rem; font-weight: 700; color: var(--text); }

    /* ── Content scroll area ── */
    .content { flex: 1; overflow-y: auto; padding: 24px 28px 36px; }

    /* ══ KPI CARDS ══════════════════════════════════════════ */
    .kpis { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 26px; }
    .kpi {
      background: var(--white); border: 1px solid var(--border);
      border-radius: 14px; padding: 20px 22px;
      display: flex; align-items: flex-start; justify-content: space-between;
      transition: box-shadow .14s;
    }
    .kpi:hover { box-shadow: 0 4px 18px rgba(0,0,0,.07); }
    .kpi__lbl { font-size: .67rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px; }
    .kpi__val { font-size: 2.1rem; font-weight: 900; color: var(--text); line-height: 1; }
    .kpi__icon {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .kpi__icon--slate { background: #f1f5f9; color: var(--muted); }
    .kpi__icon--red   { background: #fee2e2; color: var(--red); }
    .kpi__icon--amber { background: #fffbeb; color: var(--amber); }
    .kpi__icon--green { background: #f0fdf4; color: var(--green); }

    /* ══ CATEGORY CARDS ═════════════════════════════════════ */
    .cats-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .cats-title { font-size: .92rem; font-weight: 800; color: var(--text); }
    .cats-clear {
      font-size: .72rem; font-weight: 700; color: var(--muted);
      background: none; border: none; cursor: pointer; padding: 4px 8px;
      border-radius: 6px; transition: color .12s;
    }
    .cats-clear:hover { color: var(--red); }

    .cats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 26px; }
    .cat-card {
      background: var(--white); border: 1.5px solid var(--border);
      border-radius: 14px; padding: 16px 18px;
      display: flex; align-items: flex-start; justify-content: space-between;
      cursor: pointer; transition: border-color .14s, box-shadow .14s, background .14s;
    }
    .cat-card:hover { border-color: var(--red); box-shadow: 0 4px 16px rgba(150,2,15,.09); }
    .cat-card.active { border-color: var(--red); background: var(--red-soft); }
    .cat-card__name { font-size: .68rem; font-weight: 700; color: var(--muted); margin-bottom: 10px; line-height: 1.4; }
    .cat-card__val { font-size: 1.75rem; font-weight: 900; color: var(--text); line-height: 1; }
    .cat-card__icon {
      width: 38px; height: 38px; border-radius: 10px;
      background: #f1f5f9; color: var(--muted);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .cat-card.active .cat-card__icon { background: #fee2e2; color: var(--red); }

    /* ══ TABLE ══════════════════════════════════════════════ */
    .tbl-box {
      background: var(--white); border: 1px solid var(--border);
      border-radius: 16px; overflow: hidden;
    }
    .tbl-toolbar {
      display: flex; align-items: center; gap: 10px;
      padding: 16px 20px; border-bottom: 1px solid var(--border);
    }
    .tbl-search { flex: 1; position: relative; }
    .tbl-search svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
    .tbl-search input {
      width: 100%; padding: 8px 12px 8px 32px;
      border: 1.5px solid var(--border); border-radius: 9px;
      font-family: var(--font); font-size: .82rem;
      background: #f8fafc; color: var(--text); outline: none;
      transition: border-color .14s;
    }
    .tbl-search input:focus { border-color: var(--red); background: var(--white); }
    .tbl-btn {
      display: flex; align-items: center; gap: 6px;
      padding: 8px 14px; border-radius: 9px;
      font-family: var(--font); font-size: .78rem; font-weight: 700;
      cursor: pointer; border: 1.5px solid transparent;
      transition: all .13s; white-space: nowrap; flex-shrink: 0;
    }
    .tbl-btn--ghost { background: var(--white); color: var(--muted); border-color: var(--border); }
    .tbl-btn--ghost:hover { border-color: var(--red); color: var(--red); }

    .tbl-filters {
      display: flex; gap: 6px; padding: 10px 20px;
      border-bottom: 1px solid var(--border);
    }
    .tf {
      font-size: .69rem; font-weight: 700; font-family: var(--font);
      padding: 4px 12px; border-radius: 20px;
      border: 1.5px solid var(--border);
      background: var(--white); color: var(--muted);
      cursor: pointer; transition: all .12s;
    }
    .tf.on, .tf:hover { background: var(--red); border-color: var(--red); color: #fff; }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead tr { background: #f8fafc; border-bottom: 1px solid var(--border); }
    .data-table th {
      padding: 11px 18px;
      font-size: .64rem; font-weight: 800; color: var(--muted);
      text-transform: uppercase; letter-spacing: .08em; text-align: left; white-space: nowrap;
    }
    .data-table tbody tr {
      border-bottom: 1px solid #f1f5f9;
      transition: background .1s; cursor: pointer;
    }
    .data-table tbody tr:hover { background: #fafafa; }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table td { padding: 13px 18px; font-size: .82rem; color: var(--text); }
    .td-proto { font-family: monospace; font-weight: 700; color: var(--red); font-size: .8rem; }
    .td-subj { font-weight: 700; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
    .td-cat  { font-size: .76rem; color: var(--muted); max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
    .td-date { font-size: .76rem; color: var(--muted); white-space: nowrap; }
    .td-acts { display: flex; gap: 6px; }
    .td-act {
      width: 30px; height: 30px; border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      border: 1.5px solid var(--border); background: var(--white);
      color: var(--muted); cursor: pointer; transition: all .12s;
    }
    .td-act:hover { border-color: var(--red); color: var(--red); background: var(--red-soft); }

    .tbl-footer {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 20px; border-top: 1px solid var(--border);
    }
    .tbl-info { font-size: .75rem; color: var(--muted); }
    .pagination { display: flex; align-items: center; gap: 4px; }
    .pg {
      width: 30px; height: 30px; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      font-size: .74rem; font-weight: 700; font-family: var(--font);
      border: 1.5px solid var(--border); background: var(--white);
      color: var(--muted); cursor: pointer; transition: all .12s;
    }
    .pg:hover { border-color: var(--red); color: var(--red); }
    .pg.active { background: var(--red); border-color: var(--red); color: #fff; }
    .pg[disabled] { opacity: .3; cursor: default; pointer-events: none; }

    /* badges */
    .bdg {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: .64rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .04em; padding: 3px 9px; border-radius: 20px; white-space: nowrap;
    }
    .bdg::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; flex-shrink:0; }
    .bdg-ab  { background:#eff6ff; color:#2563eb; }
    .bdg-and { background:#fffbeb; color:#d97706; }
    .bdg-res { background:#f0fdf4; color:#16a34a; }
    .bdg-fch { background:#f1f5f9; color:#64748b; }

    .tbl-empty {
      padding: 64px 20px; text-align: center;
      color: var(--muted); font-size: .86rem;
    }
    .tbl-empty svg { display: block; margin: 0 auto 12px; color: #cbd5e1; }

    /* ══ SLIDE-OVER ══════════════════════════════════════════ */
    .sov-backdrop {
      position: fixed; inset: 0; background: rgba(15,23,42,.22);
      z-index: 100; opacity: 0; pointer-events: none;
      transition: opacity .22s;
    }
    .sov-backdrop.open { opacity: 1; pointer-events: auto; }
    .slideover {
      position: fixed; top: 0; right: 0; bottom: 0; width: 490px;
      background: var(--white); display: flex; flex-direction: column;
      transform: translateX(100%);
      transition: transform .26s cubic-bezier(.32,.72,0,1);
      z-index: 101; box-shadow: -10px 0 48px rgba(0,0,0,.13);
    }
    .slideover.open { transform: translateX(0); }

    .sov-head {
      background: var(--white); border-bottom: 1px solid var(--border);
      padding: 16px 20px; flex-shrink: 0;
    }
    .sov-head__r1 { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .sov-close {
      width: 32px; height: 32px; border-radius: 8px;
      background: #f1f5f9; border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: var(--muted); flex-shrink: 0;
      transition: all .14s;
    }
    .sov-close:hover { background: var(--red); border-color: var(--red); color: #fff; }
    .sov-subj { font-size: .97rem; font-weight: 900; color: var(--text); flex: 1; }
    .sov-sel {
      font-family: var(--font); font-size: .78rem; font-weight: 700;
      padding: 7px 10px; border-radius: 8px;
      border: 1.5px solid var(--border); background: var(--white); color: var(--text);
      cursor: pointer; outline: none; transition: border-color .14s; flex-shrink: 0;
    }
    .sov-sel:focus { border-color: var(--red); }

    .sov-meta { display: flex; flex-wrap: wrap; }
    .sov-meta__i {
      padding: 4px 18px 4px 0; margin-right: 18px;
      border-right: 1px solid var(--border);
    }
    .sov-meta__i:last-child { border-right: none; }
    .sov-meta__lbl { font-size: .57rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 3px; }
    .sov-meta__val { font-size: .8rem; font-weight: 700; color: var(--text); }

    .sov-msgs {
      flex: 1; overflow-y: auto;
      padding: 20px; background: #f8fafc;
      display: flex; flex-direction: column; gap: 10px;
    }
    .sov-msg { display: flex; align-items: flex-end; gap: 8px; flex-direction: row-reverse; }
    .sov-msg.admin { flex-direction: row; }
    .sov-msg__av {
      width: 28px; height: 28px; border-radius: 50%;
      background: var(--red); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .7rem; font-weight: 800; flex-shrink: 0;
    }
    .sov-msg.admin .sov-msg__av { background: #e2e8f0; color: var(--muted); }
    .sov-msg__body { display: flex; flex-direction: column; align-items: flex-end; max-width: 74%; }
    .sov-msg.admin .sov-msg__body { align-items: flex-start; }
    .sov-bubble {
      background: var(--red); color: #fff;
      border-radius: 16px 16px 4px 16px;
      padding: 10px 14px; font-size: .84rem; line-height: 1.55;
      white-space: pre-wrap; word-break: break-word;
    }
    .sov-msg.admin .sov-bubble {
      background: var(--white); color: var(--text);
      border-radius: 16px 16px 16px 4px;
      border: 1.5px solid var(--border);
    }
    .sov-time { font-size: .63rem; color: #94a3b8; margin-top: 4px; }

    .sov-reply {
      background: var(--white); border-top: 1px solid var(--border);
      padding: 14px 20px; display: flex; gap: 8px; align-items: flex-end; flex-shrink: 0;
    }
    .sov-reply textarea {
      flex: 1; padding: 9px 13px;
      border: 1.5px solid var(--border); border-radius: 10px;
      font-family: var(--font); font-size: .84rem; resize: none;
      min-height: 40px; max-height: 120px; outline: none;
      background: #f8fafc; color: var(--text);
      transition: border-color .14s, background .14s;
    }
    .sov-reply textarea:focus { border-color: var(--red); background: var(--white); }
    .sov-send {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--red); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #fff; flex-shrink: 0; transition: background .14s, transform .1s;
    }
    .sov-send:hover { background: var(--red-dark); }
    .sov-send:active { transform: scale(.91); }
    .sov-closed { flex: 1; text-align: center; font-size: .82rem; color: var(--muted); padding: 8px; }
  </style>
</head>
<body>
<div class="crm">

  <!-- ══ SIDEBAR ══════════════════════════════════════════ -->
  <aside class="sidebar">
    <div class="sb-brand">
      <img src="assets/logotipo/coaph logo branco.png" alt="COAPH" class="sb-brand__logo" />
      <span class="sb-brand__role">Painel Admin</span>
    </div>

    <div class="sb-section">
      <div class="sb-section-lbl">Principal</div>
      <button class="sb-item disabled">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Visão Geral
      </button>
      <button class="sb-item active" id="nav-etica">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Canal de Ética
        <span class="sb-item__badge" id="nav-badge" style="display:none">0</span>
      </button>
    </div>

    <div class="sb-section">
      <div class="sb-section-lbl">Em breve</div>
      <button class="sb-item disabled">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Contatos
      </button>
      <button class="sb-item disabled">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Avisos
      </button>
    </div>

    <div class="sb-spacer"></div>

    <div class="sb-bottom">
      <div class="sb-user">
        <div class="sb-user__av"><?= $initials ?></div>
        <div>
          <div class="sb-user__name"><?= htmlspecialchars($user['name']) ?></div>
          <div class="sb-user__role"><?= htmlspecialchars($user['role'] ?? 'Administrador') ?></div>
        </div>
      </div>
      <button class="sb-item disabled">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Configurações
      </button>
      <a href="index.html" class="sb-item js-crm-fade">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Voltar ao site
      </a>
      <a href="logout.php" class="sb-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair
      </a>
    </div>
  </aside>

  <!-- ══ MAIN ═════════════════════════════════════════════ -->
  <div class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar__titles">
        <div class="topbar__page">Canal de Ética</div>
        <div class="topbar__sub">Gerencie e responda os chamados recebidos</div>
      </div>
      <div class="topbar__search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="global-search" placeholder="Protocolo ou assunto…" />
      </div>
      <div class="topbar__right">
        <div class="topbar__icon" title="Notificações">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="topbar__icon__dot" id="notif-dot" style="display:none"></span>
        </div>
        <div class="topbar__user">
          <div class="topbar__avatar"><?= $initials ?></div>
          <span class="topbar__uname"><?= htmlspecialchars($user['name']) ?></span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--muted);margin-left:2px"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div class="content">

      <!-- KPIs -->
      <div class="kpis">
        <div class="kpi">
          <div>
            <div class="kpi__lbl">Total de Chamados</div>
            <div class="kpi__val" id="kpi-total">—</div>
          </div>
          <div class="kpi__icon kpi__icon--slate">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
        </div>
        <div class="kpi">
          <div>
            <div class="kpi__lbl">Em Aberto</div>
            <div class="kpi__val" id="kpi-ab">—</div>
          </div>
          <div class="kpi__icon kpi__icon--red">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
        </div>
        <div class="kpi">
          <div>
            <div class="kpi__lbl">Em Andamento</div>
            <div class="kpi__val" id="kpi-and">—</div>
          </div>
          <div class="kpi__icon kpi__icon--amber">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
        </div>
        <div class="kpi">
          <div>
            <div class="kpi__lbl">Resolvidos</div>
            <div class="kpi__val" id="kpi-res">—</div>
          </div>
          <div class="kpi__icon kpi__icon--green">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
        </div>
      </div>

      <!-- Categories -->
      <div class="cats-row">
        <div class="cats-title">Por Categoria</div>
        <button class="cats-clear" id="cats-clear" onclick="clearCat()" style="display:none">Limpar filtro ×</button>
      </div>
      <div class="cats-grid" id="cats-grid"></div>

      <!-- Table -->
      <div class="tbl-box">
        <div class="tbl-toolbar">
          <div class="tbl-search">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="tbl-search" placeholder="Protocolo ou assunto…" />
          </div>
          <button class="tbl-btn tbl-btn--ghost" onclick="exportCSV()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
          </button>
        </div>
        <div class="tbl-filters" id="tbl-filters">
          <button class="tf on" data-f="todos">Todos</button>
          <button class="tf" data-f="Aberto">Aberto</button>
          <button class="tf" data-f="Em Andamento">Andamento</button>
          <button class="tf" data-f="Resolvido">Resolvido</button>
          <button class="tf" data-f="Fechado">Fechado</button>
        </div>
        <div style="overflow-x:auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Protocolo</th>
                <th>Assunto</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Atualizado</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody id="tbl-body">
              <tr><td colspan="6" class="tbl-empty">Carregando…</td></tr>
            </tbody>
          </table>
        </div>
        <div class="tbl-footer">
          <span class="tbl-info" id="tbl-info">—</span>
          <div class="pagination" id="pagination"></div>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /main -->
</div><!-- /crm -->

<!-- ══ SLIDE-OVER ═══════════════════════════════════════ -->
<div class="sov-backdrop" id="sov-backdrop" onclick="closeSov()"></div>
<div class="slideover" id="slideover">
  <div class="sov-head">
    <div class="sov-head__r1">
      <button class="sov-close" onclick="closeSov()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <span class="sov-subj" id="sov-subj"></span>
      <select class="sov-sel" id="sov-status" onchange="changeStatus(this.value)">
        <option>Aberto</option>
        <option>Em Andamento</option>
        <option>Resolvido</option>
        <option>Fechado</option>
      </select>
    </div>
    <div class="sov-meta">
      <div class="sov-meta__i">
        <div class="sov-meta__lbl">Protocolo</div>
        <div class="sov-meta__val" id="sov-proto" style="font-family:monospace;color:var(--red)"></div>
      </div>
      <div class="sov-meta__i">
        <div class="sov-meta__lbl">Categoria</div>
        <div class="sov-meta__val" id="sov-cat"></div>
      </div>
      <div class="sov-meta__i">
        <div class="sov-meta__lbl">Aberto em</div>
        <div class="sov-meta__val" id="sov-date"></div>
      </div>
      <div class="sov-meta__i">
        <div class="sov-meta__lbl">Identidade</div>
        <div class="sov-meta__val" id="sov-anon"></div>
      </div>
    </div>
  </div>
  <div class="sov-msgs" id="sov-msgs"></div>
  <div id="sov-reply" class="sov-reply">
    <textarea id="sov-ta" placeholder="Responder como Equipe COAPH…" rows="1"
      oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
    <button class="sov-send" onclick="sendReply()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
    </button>
  </div>
</div>

<script>
(function(){
  'use strict';
  const API = 'api/tickets.php';
  let tickets = [], statusFilter = 'todos', search = '', catFilter = null, proto = null;
  let page = 1;
  const PER = 10;

  const $ = id => document.getElementById(id);
  const esc = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

  function fmt(s){
    const d = new Date((s||'').replace(' ','T'));
    if(isNaN(d)) return s||'—';
    return d.toLocaleDateString('pt-BR',{day:'2-digit',month:'2-digit',year:'numeric'})
      +' '+d.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
  }
  function fmtShort(s){
    const d = new Date((s||'').replace(' ','T'));
    if(isNaN(d)) return '—';
    return d.toLocaleDateString('pt-BR',{day:'2-digit',month:'short',year:'numeric'});
  }
  function bdgCls(s){ return s==='Aberto'?'bdg-ab':s==='Em Andamento'?'bdg-and':s==='Resolvido'?'bdg-res':'bdg-fch'; }

  /* ── Load ── */
  function load(){
    fetch(API+'?action=list').then(r=>r.json()).then(d=>{
      if(d.error) return;
      tickets = d;
      render();
    }).catch(()=>{
      $('tbl-body').innerHTML = '<tr><td colspan="6" class="tbl-empty">Erro ao carregar chamados.</td></tr>';
    });
  }

  /* ── KPIs ── */
  function renderKPIs(){
    $('kpi-total').textContent = tickets.length;
    $('kpi-ab').textContent    = tickets.filter(t=>t.status==='Aberto').length;
    $('kpi-and').textContent   = tickets.filter(t=>t.status==='Em Andamento').length;
    $('kpi-res').textContent   = tickets.filter(t=>t.status==='Resolvido').length;
    const ab = tickets.filter(t=>t.status==='Aberto').length;
    const badge = $('nav-badge');
    if(ab){ badge.textContent=ab; badge.style.display='flex'; } else { badge.style.display='none'; }
    $('notif-dot').style.display = ab ? '' : 'none';
  }

  /* ── Category cards ── */
  const CAT_ICONS = {
    'Assédio moral ou sexual':       `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    'Corrupção, fraude ou suborno':  `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`,
    'Conflitos de interesse':        `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/></svg>`,
    'Desvio de recursos':            `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>`,
    'Discriminação e preconceito':   `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>`,
    'Outras condutas antiéticas':    `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
  };
  const CAT_ORDER = Object.keys(CAT_ICONS);

  function renderCats(){
    const counts = {};
    tickets.forEach(t=>{ counts[t.category] = (counts[t.category]||0)+1; });

    $('cats-grid').innerHTML = CAT_ORDER.map((cat,i)=>`
      <div class="cat-card${catFilter===cat?' active':''}" onclick="filterCat(${i})">
        <div>
          <div class="cat-card__name">${esc(cat)}</div>
          <div class="cat-card__val">${counts[cat]||0}</div>
        </div>
        <div class="cat-card__icon">${CAT_ICONS[cat]}</div>
      </div>`).join('');

    $('cats-clear').style.display = catFilter ? '' : 'none';
  }

  window.filterCat = function(i){
    const cat = CAT_ORDER[i];
    catFilter = catFilter===cat ? null : cat;
    page=1; renderCats(); renderTable();
    $('cats-clear').style.display = catFilter ? '' : 'none';
  };
  window.clearCat = function(){ catFilter=null; page=1; renderCats(); renderTable(); };

  /* ── Table ── */
  function getFiltered(){
    const q = search.toLowerCase();
    return tickets.filter(t=>{
      if(statusFilter!=='todos' && t.status!==statusFilter) return false;
      if(catFilter && t.category!==catFilter) return false;
      if(q && !t.protocol.toLowerCase().includes(q) && !(t.subject||'').toLowerCase().includes(q)) return false;
      return true;
    });
  }

  function renderTable(){
    const fl = getFiltered();
    const total = fl.length;
    const pages = Math.max(1, Math.ceil(total/PER));
    if(page>pages) page=1;
    const slice = fl.slice((page-1)*PER, page*PER);

    $('tbl-info').textContent = `Mostrando ${slice.length} de ${total} chamado${total!==1?'s':''}`;

    if(!slice.length){
      $('tbl-body').innerHTML = `<tr><td colspan="6" class="tbl-empty">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Nenhum chamado encontrado.</td></tr>`;
      $('pagination').innerHTML = '';
      return;
    }

    $('tbl-body').innerHTML = slice.map(t=>`
      <tr onclick="openTicket('${esc(t.protocol)}')">
        <td><span class="td-proto">${esc(t.protocol)}</span></td>
        <td><span class="td-subj">${esc(t.subject||'—')}</span></td>
        <td><span class="td-cat">${esc(t.category||'—')}</span></td>
        <td><span class="bdg ${bdgCls(t.status)}">${esc(t.status)}</span></td>
        <td><span class="td-date">${fmtShort(t.updatedAt)}</span></td>
        <td onclick="event.stopPropagation()">
          <div class="td-acts">
            <button class="td-act" title="Abrir chamado" onclick="openTicket('${esc(t.protocol)}')">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </td>
      </tr>`).join('');

    /* pagination */
    let html = `<button class="pg" ${page<=1?'disabled':''} onclick="goPage(${page-1})"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></button>`;
    for(let i=1;i<=pages;i++){
      if(pages>6&&Math.abs(i-page)>2&&i!==1&&i!==pages){ if(i===2||i===pages-1) html+=`<button class="pg" disabled>…</button>`; continue; }
      html+=`<button class="pg${i===page?' active':''}" onclick="goPage(${i})">${i}</button>`;
    }
    html+=`<button class="pg" ${page>=pages?'disabled':''} onclick="goPage(${page+1})"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></button>`;
    $('pagination').innerHTML = html;
  }

  window.goPage = p=>{ page=p; renderTable(); };

  function render(){ renderKPIs(); renderCats(); renderTable(); }

  /* ── Slide-over ── */
  window.openTicket = function(p){
    proto = p;
    fetch(`${API}?action=get&protocol=${encodeURIComponent(p)}`).then(r=>r.json()).then(t=>{
      if(t.error) return;
      renderSov(t);
    });
  };

  function renderSov(t){
    $('sov-subj').textContent  = t.subject;
    $('sov-proto').textContent = t.protocol;
    $('sov-cat').textContent   = t.category;
    $('sov-date').textContent  = fmt(t.createdAt);
    $('sov-anon').textContent  = t.anonymous ? 'Anônimo' : 'Identificado';
    $('sov-status').value      = t.status;

    const msgs = $('sov-msgs');
    msgs.innerHTML = (t.messages||[]).map(m=>`
      <div class="sov-msg${m.isAdmin?' admin':''}">
        <div class="sov-msg__av">${(m.author||'?')[0].toUpperCase()}</div>
        <div class="sov-msg__body">
          <div class="sov-bubble">${esc(m.content)}</div>
          <div class="sov-time">${esc(m.author)} · ${fmt(m.date)}</div>
        </div>
      </div>`).join('');
    msgs.scrollTop = msgs.scrollHeight;

    const rep = $('sov-reply');
    if(t.status==='Fechado'){
      rep.innerHTML = '<p class="sov-closed">Este chamado está fechado.</p>';
    } else {
      rep.innerHTML = `<textarea id="sov-ta" placeholder="Responder como Equipe COAPH…" rows="1"
        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
        <button class="sov-send" onclick="sendReply()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>`;
    }

    $('slideover').classList.add('open');
    $('sov-backdrop').classList.add('open');
  }

  window.closeSov = function(){
    $('slideover').classList.remove('open');
    $('sov-backdrop').classList.remove('open');
    proto = null;
  };

  /* ── Reply ── */
  window.sendReply = function(){
    const ta = $('sov-ta');
    const msg = ta ? ta.value.trim() : '';
    if(!msg||!proto) return;
    fetch(API+'?action=admin_reply',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({protocol:proto, message:msg})
    }).then(r=>r.json()).then(d=>{
      if(d.error){alert(d.error);return;}
      ta.value=''; ta.style.height='auto';
      const m = d.message;
      $('sov-msgs').insertAdjacentHTML('beforeend',`
        <div class="sov-msg admin">
          <div class="sov-msg__av">E</div>
          <div class="sov-msg__body">
            <div class="sov-bubble">${esc(m.content)}</div>
            <div class="sov-time">${esc(m.author)} · ${fmt(m.date)}</div>
          </div>
        </div>`);
      $('sov-msgs').scrollTop = $('sov-msgs').scrollHeight;
      const i = tickets.findIndex(t=>t.protocol===proto);
      if(i!==-1){ tickets[i].updatedAt=m.date; if(tickets[i].status==='Aberto') tickets[i].status='Em Andamento'; render(); }
    });
  };

  /* ── Status change ── */
  window.changeStatus = function(s){
    if(!proto) return;
    fetch(API+'?action=set_status',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({protocol:proto, status:s})
    }).then(r=>r.json()).then(d=>{
      if(d.error){alert(d.error);return;}
      const i = tickets.findIndex(t=>t.protocol===proto);
      if(i!==-1){ tickets[i].status=s; render(); }
      if(s==='Fechado'){
        const rep=$('sov-reply');
        if(rep) rep.innerHTML='<p class="sov-closed">Este chamado está fechado.</p>';
      }
    });
  };

  /* ── Filters ── */
  document.querySelectorAll('.tf').forEach(b=>{
    b.addEventListener('click',function(){
      document.querySelectorAll('.tf').forEach(x=>x.classList.remove('on'));
      this.classList.add('on');
      statusFilter=this.dataset.f; page=1; renderTable();
    });
  });
  $('global-search').addEventListener('input',function(){ search=this.value; page=1; render(); });
  $('tbl-search').addEventListener('input',function(){ search=this.value; page=1; renderTable(); });

  /* ── Export CSV ── */
  window.exportCSV = function(){
    const fl = getFiltered();
    const rows=[['Protocolo','Assunto','Categoria','Status','Atualizado']];
    fl.forEach(t=>rows.push([t.protocol,t.subject||'',t.category||'',t.status,t.updatedAt||'']));
    const csv=rows.map(r=>r.map(c=>'"'+String(c).replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');
    a.href='data:text/csv;charset=utf-8,﻿'+encodeURIComponent(csv);
    a.download='chamados-canal-etica.csv';
    a.click();
  };

  /* ── Init ── */
  load();
  setInterval(load, 30000);
})();
</script>

<style>
  .crm-fade-overlay {
    position: fixed; inset: 0; background: #fff;
    opacity: 0; pointer-events: none;
    z-index: 9999; transition: opacity .45s ease;
  }
  .crm-fade-overlay.active { opacity: 1; pointer-events: auto; }
</style>
<script>
  /* fade de entrada no CRM */
  (function(){
    const ov = document.createElement('div');
    ov.className = 'crm-fade-overlay active';
    document.body.appendChild(ov);
    requestAnimationFrame(() => requestAnimationFrame(() => {
      ov.style.opacity = '0';
      ov.addEventListener('transitionend', () => ov.remove(), { once: true });
    }));

    /* fade de saída nos links do CRM */
    document.querySelectorAll('.js-crm-fade').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const out = document.createElement('div');
        out.className = 'crm-fade-overlay';
        document.body.appendChild(out);
        requestAnimationFrame(() => out.classList.add('active'));
        out.addEventListener('transitionend', () => {
          window.location.href = link.href;
        }, { once: true });
      });
    });
  }());
</script>
</body>
</html>
