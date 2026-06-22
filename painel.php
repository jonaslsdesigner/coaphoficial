<?php
session_start();
if (empty($_SESSION['coaph_user'])) { header('Location: login.php'); exit(); }
$user = $_SESSION['coaph_user'];
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Painel CRM — COAPH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;900&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --red:      #96020f;
      --red-dark: #7a010c;
      --nav-bg:   #1a1f2e;
      --font:     'DM Sans', sans-serif;
      --border:   #e2e8f0;
      --nav-w:    64px;
      --list-w:   300px;
    }
    html, body {
      height: 100%; width: 100%;
      font-family: var(--font);
      background: #f1f5f9;
      overflow: hidden;
      color: #1a1a1a;
    }

    /* ══ SHELL FULL SCREEN ══════════════════════════════ */
    .crm {
      display: flex;
      flex-direction: column;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
    }

    /* ── Topbar ── */
    .crm-top {
      height: 54px;
      background: #fff;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      flex-shrink: 0;
      z-index: 30;
    }
    .crm-top__brand {
      width: var(--nav-w);
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--nav-bg);
      flex-shrink: 0;
    }
    .crm-top__brand img { height: 26px; width: auto; }
    .crm-top__divider { width: 1px; height: 100%; background: var(--border); }
    .crm-top__mid {
      flex: 1;
      display: flex;
      align-items: center;
      padding: 0 20px;
      gap: 8px;
    }
    .crm-top__label {
      font-size: .72rem;
      font-weight: 700;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: .14em;
    }
    .crm-top__right {
      display: flex;
      align-items: center;
      gap: 10px;
      padding-right: 20px;
    }
    .crm-top__user {
      display: flex; align-items: center; gap: 8px;
      font-size: .84rem; font-weight: 600; color: #374151;
    }
    .crm-top__avatar {
      width: 32px; height: 32px; border-radius: 50%;
      background: var(--red); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .8rem; font-weight: 800;
    }
    .crm-top__logout {
      display: flex; align-items: center; gap: 6px;
      font-size: .78rem; font-weight: 600; color: #64748b;
      background: #f8fafc; border: 1px solid var(--border);
      border-radius: 8px; padding: 6px 12px;
      text-decoration: none; transition: all .15s;
    }
    .crm-top__logout:hover { background: #fee2e2; border-color: #fca5a5; color: var(--red); }

    /* ── Workspace ── */
    .crm-workspace {
      display: flex;
      flex: 1;
      overflow: hidden;
    }

    /* ── Icon nav ── */
    .crm-nav {
      width: var(--nav-w);
      background: var(--nav-bg);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 12px 0;
      gap: 4px;
      flex-shrink: 0;
    }
    .crm-nav__btn {
      width: 40px; height: 40px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: rgba(255,255,255,.4);
      border: none; background: transparent;
      transition: background .15s, color .15s;
      position: relative;
    }
    .crm-nav__btn:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.8); }
    .crm-nav__btn.active { background: var(--red); color: #fff; }
    .crm-nav__btn[disabled] { cursor: default; opacity: .25; }
    .crm-nav__tip {
      position: absolute; left: calc(100% + 10px); top: 50%;
      transform: translateY(-50%);
      background: #1e293b; color: #fff;
      font-size: .7rem; font-weight: 600; font-family: var(--font);
      padding: 4px 9px; border-radius: 6px;
      white-space: nowrap; pointer-events: none;
      opacity: 0; transition: opacity .12s;
      z-index: 50;
    }
    .crm-nav__btn:hover .crm-nav__tip { opacity: 1; }
    .crm-nav__spacer { flex: 1; }
    .crm-nav__sep {
      width: 32px; height: 1px;
      background: rgba(255,255,255,.08);
      margin: 4px 0;
    }

    /* ── List panel ── */
    .crm-list {
      width: var(--list-w);
      background: #fff;
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      overflow: hidden;
    }
    .crm-list__head {
      padding: 16px 14px 12px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    .crm-list__title {
      font-size: .92rem; font-weight: 800; color: #1a1a1a;
      margin-bottom: 10px;
    }
    .crm-list__search {
      position: relative;
    }
    .crm-list__search svg {
      position: absolute; left: 9px; top: 50%;
      transform: translateY(-50%); color: #94a3b8; pointer-events: none;
    }
    .crm-list__search input {
      width: 100%; padding: 7px 10px 7px 30px;
      border: 1.5px solid var(--border); border-radius: 8px;
      font-family: var(--font); font-size: .8rem;
      background: #f8fafc; color: #1a1a1a; outline: none;
      transition: border-color .15s;
    }
    .crm-list__search input:focus { border-color: var(--red); background: #fff; }

    /* Stats */
    .crm-stats {
      display: grid; grid-template-columns: repeat(4,1fr);
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    .crm-stat {
      padding: 10px 6px; text-align: center;
    }
    .crm-stat + .crm-stat { border-left: 1px solid var(--border); }
    .crm-stat__n { font-size: 1.15rem; font-weight: 900; color: #1a1a1a; line-height: 1; }
    .crm-stat__n.c-red   { color: var(--red); }
    .crm-stat__n.c-amber { color: #d97706; }
    .crm-stat__n.c-green { color: #16a34a; }
    .crm-stat__lbl { font-size: .58rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }

    /* Filters */
    .crm-filters {
      display: flex; gap: 4px; padding: 8px 10px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0; flex-wrap: wrap;
    }
    .crm-f {
      font-size: .67rem; font-weight: 700; font-family: var(--font);
      padding: 3px 9px; border-radius: 20px;
      border: 1.5px solid var(--border);
      background: #fff; color: #64748b;
      cursor: pointer; transition: all .12s;
    }
    .crm-f.on, .crm-f:hover { background: var(--red); border-color: var(--red); color: #fff; }

    /* Ticket rows */
    .crm-rows { flex: 1; overflow-y: auto; }
    .crm-row {
      padding: 11px 14px;
      border-bottom: 1px solid #f1f5f9;
      cursor: pointer; transition: background .1s;
    }
    .crm-row:hover { background: #f8fafc; }
    .crm-row.sel { background: #fef2f2; border-left: 3px solid var(--red); padding-left: 11px; }
    .crm-row__r1 { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 3px; }
    .crm-row__proto { font-size: .67rem; font-weight: 700; color: var(--red); font-family: monospace; }
    .crm-row__subj { font-size: .83rem; font-weight: 700; color: #1a1a1a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
    .crm-row__meta { font-size: .69rem; color: #94a3b8; display: flex; gap: 5px; }
    .crm-rows__empty { padding: 44px 16px; text-align: center; color: #94a3b8; font-size: .84rem; }

    /* Status badges */
    .bdg {
      display: inline-flex; align-items: center; gap: 4px;
      font-size: .6rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .05em; padding: 2px 7px; border-radius: 20px; white-space: nowrap;
    }
    .bdg::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; flex-shrink:0; }
    .bdg-ab  { background:#eff6ff; color:#2563eb; }
    .bdg-and { background:#fffbeb; color:#d97706; }
    .bdg-res { background:#f0fdf4; color:#16a34a; }
    .bdg-fch { background:#f1f5f9; color:#64748b; }

    /* ══ DETAIL PANEL ═══════════════════════════════════ */
    .crm-detail {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: #f8fafc;
      overflow: hidden;
      min-width: 0;
    }

    /* Empty */
    .crm-empty {
      flex: 1; display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: 16px; color: #cbd5e1;
    }
    .crm-empty__icon {
      width: 72px; height: 72px; border-radius: 20px;
      background: #fff; border: 1.5px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      color: #cbd5e1;
    }
    .crm-empty p { font-size: .88rem; color: #94a3b8; }

    /* Detail header */
    .crm-dh {
      background: #fff;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    .crm-dh__row1 {
      display: flex; align-items: center; gap: 12px;
      padding: 14px 24px; border-bottom: 1px solid var(--border);
    }
    .crm-dh__back {
      width: 30px; height: 30px; border-radius: 8px;
      background: #f1f5f9; border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; color: #475569; flex-shrink: 0;
      transition: all .15s;
    }
    .crm-dh__back:hover { background: var(--red); border-color: var(--red); color: #fff; }
    .crm-dh__subj { font-size: 1rem; font-weight: 900; color: #1a1a1a; flex: 1; }
    .crm-dh__acts { display: flex; align-items: center; gap: 8px; }
    .crm-sel {
      font-family: var(--font); font-size: .78rem; font-weight: 700;
      padding: 7px 10px; border-radius: 8px;
      border: 1.5px solid var(--border);
      background: #fff; color: #1a1a1a;
      cursor: pointer; outline: none;
      transition: border-color .15s;
    }
    .crm-sel:focus { border-color: var(--red); }

    /* Meta strip */
    .crm-dh__meta {
      display: flex; gap: 0;
      overflow-x: auto;
    }
    .crm-dh__meta-item {
      padding: 10px 20px;
      border-right: 1px solid var(--border);
      flex-shrink: 0;
    }
    .crm-dh__meta-item:last-child { border-right: none; }
    .crm-dh__meta-lbl { font-size: .6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 3px; }
    .crm-dh__meta-val { font-size: .82rem; font-weight: 700; color: #1a1a1a; }

    /* Messages */
    .crm-msgs {
      flex: 1; overflow-y: auto;
      padding: 24px 28px;
      display: flex; flex-direction: column; gap: 10px;
    }
    .crm-msg { display: flex; align-items: flex-end; gap: 8px; flex-direction: row-reverse; }
    .crm-msg.admin { flex-direction: row; }
    .crm-msg__av {
      width: 28px; height: 28px; border-radius: 50%;
      background: var(--red); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: .7rem; font-weight: 800; flex-shrink: 0;
    }
    .crm-msg.admin .crm-msg__av { background: #e2e8f0; color: #475569; }
    .crm-msg__body { display: flex; flex-direction: column; align-items: flex-end; max-width: 65%; }
    .crm-msg.admin .crm-msg__body { align-items: flex-start; }
    .crm-msg__bubble {
      background: var(--red); color: #fff;
      border-radius: 16px 16px 4px 16px;
      padding: 9px 14px; font-size: .86rem; line-height: 1.6;
      white-space: pre-wrap; word-break: break-word;
    }
    .crm-msg.admin .crm-msg__bubble {
      background: #fff; color: #1a1a1a;
      border-radius: 16px 16px 16px 4px;
      border: 1.5px solid var(--border);
      box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .crm-msg__time { font-size: .65rem; color: #94a3b8; margin-top: 4px; }

    /* Reply */
    .crm-reply {
      background: #fff; border-top: 1px solid var(--border);
      padding: 14px 24px;
      display: flex; gap: 10px; align-items: flex-end;
      flex-shrink: 0;
    }
    .crm-reply textarea {
      flex: 1; padding: 10px 14px;
      border: 1.5px solid var(--border); border-radius: 10px;
      font-family: var(--font); font-size: .88rem; resize: none;
      min-height: 42px; max-height: 130px; outline: none;
      background: #f8fafc; color: #1a1a1a;
      transition: border-color .15s, background .15s;
    }
    .crm-reply textarea:focus { border-color: var(--red); background: #fff; }
    .crm-reply__send {
      width: 42px; height: 42px; border-radius: 50%;
      background: var(--red); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #fff; flex-shrink: 0;
      transition: background .15s, transform .1s;
    }
    .crm-reply__send:hover { background: var(--red-dark); }
    .crm-reply__send:active { transform: scale(.92); }
    .crm-reply__closed {
      flex: 1; text-align: center;
      font-size: .82rem; color: #94a3b8; padding: 10px;
    }
  </style>
</head>
<body>
<div class="crm">

  <!-- Topbar -->
  <header class="crm-top">
    <div class="crm-top__brand">
      <img src="assets/logotipo/coaph logo branco.png" alt="COAPH" />
    </div>
    <div class="crm-top__mid">
      <span class="crm-top__label">Painel CRM</span>
    </div>
    <div class="crm-top__right">
      <div class="crm-top__user">
        <div class="crm-top__avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
        <?= htmlspecialchars($user['name']) ?>
      </div>
      <a href="logout.php" class="crm-top__logout">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair
      </a>
    </div>
  </header>

  <!-- Workspace -->
  <div class="crm-workspace">

    <!-- Icon nav -->
    <nav class="crm-nav">
      <button class="crm-nav__btn active" title="Canal de Ética">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span class="crm-nav__tip">Canal de Ética</span>
      </button>
      <div class="crm-nav__spacer"></div>
      <div class="crm-nav__sep"></div>
      <button class="crm-nav__btn" disabled title="Configurações">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span class="crm-nav__tip">Configurações</span>
      </button>
    </nav>

    <!-- List panel -->
    <div class="crm-list">
      <div class="crm-list__head">
        <div class="crm-list__title">Canal de Ética</div>
        <div class="crm-list__search">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="search-input" placeholder="Protocolo ou assunto…" />
        </div>
      </div>
      <div class="crm-stats">
        <div class="crm-stat"><div class="crm-stat__n" id="s-total">—</div><div class="crm-stat__lbl">Total</div></div>
        <div class="crm-stat"><div class="crm-stat__n c-red" id="s-ab">—</div><div class="crm-stat__lbl">Abertos</div></div>
        <div class="crm-stat"><div class="crm-stat__n c-amber" id="s-and">—</div><div class="crm-stat__lbl">Andamento</div></div>
        <div class="crm-stat"><div class="crm-stat__n c-green" id="s-res">—</div><div class="crm-stat__lbl">Resolvidos</div></div>
      </div>
      <div class="crm-filters">
        <button class="crm-f on"  data-f="todos">Todos</button>
        <button class="crm-f"     data-f="Aberto">Aberto</button>
        <button class="crm-f"     data-f="Em Andamento">Andamento</button>
        <button class="crm-f"     data-f="Resolvido">Resolvido</button>
        <button class="crm-f"     data-f="Fechado">Fechado</button>
      </div>
      <div class="crm-rows" id="ticket-list">
        <div class="crm-rows__empty">Carregando…</div>
      </div>
    </div>

    <!-- Detail panel -->
    <div class="crm-detail" id="crm-detail">

      <!-- Empty state -->
      <div class="crm-empty" id="empty-state">
        <div class="crm-empty__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <p>Selecione um chamado para visualizar</p>
      </div>

      <!-- Ticket view -->
      <div id="ticket-view" style="display:none; flex-direction:column; height:100%;">
        <div class="crm-dh">
          <div class="crm-dh__row1">
            <button class="crm-dh__back" onclick="closeTicket()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
            </button>
            <span class="crm-dh__subj" id="d-subject"></span>
            <div class="crm-dh__acts">
              <select class="crm-sel" id="status-sel" onchange="changeStatus(this.value)">
                <option>Aberto</option>
                <option>Em Andamento</option>
                <option>Resolvido</option>
                <option>Fechado</option>
              </select>
            </div>
          </div>
          <div class="crm-dh__meta">
            <div class="crm-dh__meta-item">
              <div class="crm-dh__meta-lbl">Protocolo</div>
              <div class="crm-dh__meta-val" id="d-proto" style="font-family:monospace;color:var(--red)"></div>
            </div>
            <div class="crm-dh__meta-item">
              <div class="crm-dh__meta-lbl">Categoria</div>
              <div class="crm-dh__meta-val" id="d-cat"></div>
            </div>
            <div class="crm-dh__meta-item">
              <div class="crm-dh__meta-lbl">Aberto em</div>
              <div class="crm-dh__meta-val" id="d-date"></div>
            </div>
            <div class="crm-dh__meta-item">
              <div class="crm-dh__meta-lbl">Identidade</div>
              <div class="crm-dh__meta-val" id="d-anon"></div>
            </div>
            <div class="crm-dh__meta-item">
              <div class="crm-dh__meta-lbl">Mensagens</div>
              <div class="crm-dh__meta-val" id="d-msgs"></div>
            </div>
          </div>
        </div>
        <div class="crm-msgs" id="crm-msgs"></div>
        <div id="reply-area" class="crm-reply">
          <textarea id="reply-ta" placeholder="Responder como Equipe COAPH…" rows="1"
            oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,130)+'px'"></textarea>
          <button class="crm-reply__send" onclick="sendReply()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
      </div>

    </div><!-- /crm-detail -->
  </div><!-- /crm-workspace -->
</div><!-- /crm -->

<script>
(function(){
  'use strict';
  const API = 'api/tickets.php';
  let tickets = [], filter = 'todos', search = '', proto = null;

  const $ = id => document.getElementById(id);

  function fmt(s){
    const d = new Date(s.replace(' ','T'));
    if(isNaN(d)) return s;
    return d.toLocaleDateString('pt-BR',{day:'2-digit',month:'2-digit',year:'numeric'})
      +' '+d.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
  }
  function esc(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function bdgCls(s){
    return s==='Aberto'?'bdg-ab':s==='Em Andamento'?'bdg-and':s==='Resolvido'?'bdg-res':'bdg-fch';
  }

  /* ── Load ── */
  function load(){
    fetch(API+'?action=list').then(r=>r.json()).then(d=>{
      if(d.error) return;
      tickets = d;
      stats(); list();
    }).catch(()=>{ $('ticket-list').innerHTML='<div class="crm-rows__empty">Erro ao carregar.</div>'; });
  }

  function stats(){
    $('s-total').textContent = tickets.length;
    $('s-ab').textContent    = tickets.filter(t=>t.status==='Aberto').length;
    $('s-and').textContent   = tickets.filter(t=>t.status==='Em Andamento').length;
    $('s-res').textContent   = tickets.filter(t=>t.status==='Resolvido').length;
  }

  function list(){
    const q = search.toLowerCase();
    const fl = tickets.filter(t=>{
      if(filter!=='todos' && t.status!==filter) return false;
      if(q && !t.protocol.toLowerCase().includes(q) && !t.subject.toLowerCase().includes(q)) return false;
      return true;
    });
    const el = $('ticket-list');
    if(!fl.length){ el.innerHTML='<div class="crm-rows__empty">Nenhum chamado.</div>'; return; }
    el.innerHTML = fl.map(t=>`
      <div class="crm-row${t.protocol===proto?' sel':''}" onclick="open('${t.protocol}')">
        <div class="crm-row__r1">
          <span class="crm-row__proto">${t.protocol}</span>
          <span class="bdg ${bdgCls(t.status)}">${t.status}</span>
        </div>
        <div class="crm-row__subj">${esc(t.subject)}</div>
        <div class="crm-row__meta"><span>${esc(t.category)}</span><span>·</span><span>${fmt(t.updatedAt)}</span></div>
      </div>`).join('');
  }

  /* ── Open ticket ── */
  window.open = function(p){
    proto = p; list();
    fetch(`${API}?action=get&protocol=${encodeURIComponent(p)}`).then(r=>r.json()).then(t=>{
      if(!t.error) render(t);
    });
  };

  function render(t){
    $('empty-state').style.display  = 'none';
    const v = $('ticket-view');
    v.style.display = 'flex';

    $('d-subject').textContent = t.subject;
    $('d-proto').textContent   = t.protocol;
    $('d-cat').textContent     = t.category;
    $('d-date').textContent    = fmt(t.createdAt);
    $('d-anon').textContent    = t.anonymous ? 'Anônimo' : 'Identificado';
    $('d-msgs').textContent    = t.messages.length;
    $('status-sel').value      = t.status;

    const msgs = $('crm-msgs');
    msgs.innerHTML = t.messages.map(m=>`
      <div class="crm-msg${m.isAdmin?' admin':''}">
        <div class="crm-msg__av">${(m.author||'?')[0].toUpperCase()}</div>
        <div class="crm-msg__body">
          <div class="crm-msg__bubble">${esc(m.content)}</div>
          <div class="crm-msg__time">${esc(m.author)} · ${fmt(m.date)}</div>
        </div>
      </div>`).join('');
    msgs.scrollTop = msgs.scrollHeight;

    const ra = $('reply-area');
    if(t.status==='Fechado'){
      ra.innerHTML='<p class="crm-reply__closed">Este chamado está fechado.</p>';
    } else {
      ra.innerHTML=`<textarea id="reply-ta" placeholder="Responder como Equipe COAPH…" rows="1"
        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,130)+'px'"></textarea>
        <button class="crm-reply__send" onclick="sendReply()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>`;
    }
  }

  window.closeTicket = function(){
    proto = null;
    $('ticket-view').style.display = 'none';
    $('empty-state').style.display = 'flex';
    list();
  };

  /* ── Reply ── */
  window.sendReply = function(){
    const ta = $('reply-ta');
    const msg = ta ? ta.value.trim() : '';
    if(!msg || !proto) return;
    fetch(API+'?action=admin_reply',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body:JSON.stringify({protocol:proto, message:msg})
    }).then(r=>r.json()).then(d=>{
      if(d.error){alert(d.error);return;}
      ta.value=''; ta.style.height='auto';
      const m = d.message;
      const msgs = $('crm-msgs');
      msgs.insertAdjacentHTML('beforeend',`
        <div class="crm-msg admin">
          <div class="crm-msg__av">E</div>
          <div class="crm-msg__body">
            <div class="crm-msg__bubble">${esc(m.content)}</div>
            <div class="crm-msg__time">${esc(m.author)} · ${fmt(m.date)}</div>
          </div>
        </div>`);
      msgs.scrollTop = msgs.scrollHeight;
      const i = tickets.findIndex(t=>t.protocol===proto);
      if(i!==-1){tickets[i].updatedAt=m.date; if(tickets[i].status==='Aberto') tickets[i].status='Em Andamento'; stats(); list();}
    });
  };

  /* ── Status ── */
  window.changeStatus = function(s){
    if(!proto) return;
    fetch(API+'?action=set_status',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body:JSON.stringify({protocol:proto, status:s})
    }).then(r=>r.json()).then(d=>{
      if(d.error){alert(d.error);return;}
      const i = tickets.findIndex(t=>t.protocol===proto);
      if(i!==-1){tickets[i].status=s; stats(); list();}
      if(s==='Fechado'){
        const ra=$('reply-area');
        if(ra) ra.innerHTML='<p class="crm-reply__closed">Este chamado está fechado.</p>';
      }
    });
  };

  /* ── Filters ── */
  document.querySelectorAll('.crm-f').forEach(b=>{
    b.addEventListener('click',function(){
      document.querySelectorAll('.crm-f').forEach(x=>x.classList.remove('on'));
      this.classList.add('on');
      filter = this.dataset.f;
      list();
    });
  });
  $('search-input').addEventListener('input',function(){ search=this.value; list(); });

  load();
  setInterval(load, 30000);
})();
</script>
</body>
</html>
