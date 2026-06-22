(function () {
  'use strict';

  /* Detecta o slug a partir do nome do arquivo (ex: blog-traumatologia.html → traumatologia) */
  const slug = window.location.pathname
    .split('/').pop()
    .replace(/^blog-/, '')
    .replace(/\.html$/, '');

  if (!slug || !document.querySelector('.post-content')) return;

  const API = 'api/comments.php';
  let commentCount = 0;

  /* ── Injeta a seção de comentários antes do <footer> ──── */
  const section = document.createElement('section');
  section.className = 'comments-section';
  section.innerHTML = `
    <div class="container">
      <div class="comments-inner">
        <h3 class="comments-heading">Comentários</h3>
        <p class="comments-count" id="comments-count">Carregando...</p>
        <div id="comments-list"></div>

        <div class="comment-form-wrap">
          <h4 class="comment-form-title">Deixe seu comentário</h4>
          <form id="comment-form" novalidate>
            <div class="comment-form-field">
              <input
                type="text"
                id="comment-username"
                placeholder="Seu nome"
                maxlength="50"
                required
                autocomplete="nickname"
              />
            </div>
            <div class="comment-form-field">
              <textarea
                id="comment-text"
                placeholder="Escreva seu comentário..."
                maxlength="1500"
                required
              ></textarea>
            </div>
            <button type="submit" class="comment-submit-btn" id="comment-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
              </svg>
              Enviar comentário
            </button>
            <div class="comment-form-msg" id="comment-msg"></div>
          </form>
        </div>
      </div>
    </div>`;

  const footer = document.querySelector('footer.footer');
  if (footer) {
    footer.parentNode.insertBefore(section, footer);
  } else {
    document.body.appendChild(section);
  }

  /* ── Helpers ─────────────────────────────────────────── */
  function formatDate(dateStr) {
    const d = new Date(dateStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' });
  }

  function renderComment(c) {
    const initial = (c.username || '?')[0].toUpperCase();
    return `
      <div class="comment-item">
        <div class="comment-header">
          <div class="comment-avatar">${initial}</div>
          <div class="comment-meta">
            <span class="comment-author">${c.username}</span>
            <span class="comment-date">${formatDate(c.date)}</span>
          </div>
        </div>
        <p class="comment-text">${c.text}</p>
      </div>`;
  }

  function updateCount(n) {
    commentCount = n;
    const el = document.getElementById('comments-count');
    if (!el) return;
    if (n === 0) {
      el.textContent = 'Nenhum comentário ainda. Seja o primeiro!';
    } else {
      el.textContent = n + (n === 1 ? ' comentário' : ' comentários');
    }
  }

  /* ── Carrega comentários existentes ──────────────────── */
  fetch(`${API}?post=${encodeURIComponent(slug)}`)
    .then(function (r) { return r.json(); })
    .then(function (data) {
      const usernameInput = document.getElementById('comment-username');
      if (data.savedUsername && usernameInput) {
        usernameInput.value = data.savedUsername;
      }

      const comments = data.comments || [];
      updateCount(comments.length);

      const list = document.getElementById('comments-list');
      if (!list) return;

      if (comments.length === 0) {
        list.innerHTML = '<p class="comments-empty">Ainda não há comentários neste post.</p>';
      } else {
        list.innerHTML = comments.slice().reverse().map(renderComment).join('');
      }
    })
    .catch(function () {
      updateCount(0);
    });

  /* ── Envio do formulário ─────────────────────────────── */
  document.getElementById('comment-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn      = document.getElementById('comment-btn');
    const msgEl    = document.getElementById('comment-msg');
    const username = document.getElementById('comment-username').value.trim();
    const text     = document.getElementById('comment-text').value.trim();

    msgEl.className = 'comment-form-msg';

    if (!username || !text) {
      msgEl.className = 'comment-form-msg error';
      msgEl.textContent = 'Preencha seu nome e o comentário antes de enviar.';
      return;
    }

    btn.disabled     = true;
    btn.textContent  = 'Enviando...';

    fetch(`${API}?post=${encodeURIComponent(slug)}`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ username: username, text: text })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.error) throw new Error(data.error);

        msgEl.className   = 'comment-form-msg success';
        msgEl.textContent = 'Comentário enviado com sucesso!';

        document.getElementById('comment-text').value = '';

        const list     = document.getElementById('comments-list');
        const emptyMsg = list.querySelector('.comments-empty');
        if (emptyMsg) emptyMsg.remove();
        list.insertAdjacentHTML('afterbegin', renderComment(data.comment));

        updateCount(commentCount + 1);
      })
      .catch(function (err) {
        msgEl.className   = 'comment-form-msg error';
        msgEl.textContent = err.message || 'Erro ao enviar comentário. Tente novamente.';
      })
      .finally(function () {
        btn.disabled  = false;
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Enviar comentário';
      });
  });
})();
