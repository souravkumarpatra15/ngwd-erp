<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<style>
.chat-msgs { max-height: calc(100vh - 320px); min-height: 320px; overflow-y: auto; background: #efeae2; }
.bubble { max-width: 75%; padding: 7px 11px; border-radius: 12px; word-break: break-word; }
.bubble.me { background: #d9fdd3; margin-left: auto; border-top-right-radius: 2px; }
.bubble.them { background: #fff; margin-right: auto; border-top-left-radius: 2px; box-shadow: 0 1px 1px rgba(0,0,0,.08); }
.tick { font-style: normal; letter-spacing: -2px; }
</style>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 py-3 fw-semibold"><i class="bi bi-whatsapp me-2 text-success"></i>Chat with Support</div>
  <div class="card-body p-0 d-flex flex-column">
    <?php if (empty($conversation)): ?>
      <div class="text-center text-muted small py-5"><i class="bi bi-chat-dots fs-3 d-block mb-2 opacity-25"></i>No WhatsApp number on your client profile yet — ask support to link it.</div>
    <?php else: ?>
    <div class="chat-msgs flex-grow-1 p-3 d-flex flex-column gap-2" id="msgList">
      <?php if (empty($messages)): ?>
        <div class="text-center text-muted small py-5" id="emptyMsg"><i class="bi bi-chat-dots fs-3 d-block mb-2 opacity-25"></i>Say hello — we usually reply quickly.</div>
      <?php else: $lastDay = ''; foreach ($messages as $m): ?>
        <?php if (($m['day'] ?? '') !== $lastDay): $lastDay = $m['day'] ?? ''; ?><div class="align-self-center small px-3 py-1 rounded" style="background:#e2e6ea;color:#54656f;font-size:11px"><?= esc($lastDay) ?></div><?php endif; ?>
        <div class="bubble <?= ! empty($m['is_me']) ? 'me' : 'them' ?>">
          <?php if (($m['message_type'] ?? '') === 'image' && ! empty($m['media_url'])): ?>
            <?php $src = str_starts_with((string) $m['media_url'], 'http') ? $m['media_url'] : base_url('portal/chat/serve/' . (int) $m['id']); ?>
            <a href="<?= esc($src) ?>" target="_blank"><img src="<?= esc($src) ?>" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a>
          <?php endif; ?>
          <?php if (trim((string) ($m['message_text'] ?? '')) !== ''): ?><div class="small"><?= esc($m['message_text']) ?></div><?php endif; ?>
          <div class="text-end text-muted" style="font-size:10px"><?= esc($m['clock'] ?? $m['time'] ?? '') ?></div>
        </div>
      <?php endforeach; endif; ?>
      <button class="btn btn-sm btn-light border d-none align-self-center" id="newMsgPill">↓ <span id="newMsgCount">0</span> new</button>
    </div>
    <div class="p-3 border-top">
      <div class="d-flex gap-2">
        <input type="text" id="msgInput" class="form-control" placeholder="Type a message..." maxlength="2000" autocomplete="off">
        <button class="btn btn-success" id="sendBtn"><i class="bi bi-send"></i></button>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (! empty($conversation)): ?>
<script>
(function () {
  const BASE = '<?= base_url('portal/chat') ?>';
  const SERVE = '<?= base_url('portal/chat/serve') ?>';
  const list = document.getElementById('msgList');
  const input = document.getElementById('msgInput');
  const sendBtn = document.getElementById('sendBtn');
  const emptyMsg = document.getElementById('emptyMsg');
  const pill = document.getElementById('newMsgPill');
  const pillCount = document.getElementById('newMsgCount');
  let lastId = <?= empty($messages) ? 0 : (int) end($messages)['id'] ?>;
  let unread = 0;
  list.scrollTop = list.scrollHeight;

  function bubble(m) {
    const d = document.createElement('div');
    d.className = 'bubble ' + (m.is_me ? 'me' : 'them');
    let h = '';
    if (m.message_type === 'image' && m.media_url) {
      const src = m.media_url.indexOf('http') === 0 ? m.media_url : SERVE + '/' + m.id;
      h += '<a href="' + src + '" target="_blank"><img src="' + src + '" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a>';
    }
    if (m.message_text) { const p = document.createElement('div'); p.className = 'small'; p.textContent = m.message_text; h += p.outerHTML; }
    h += '<div class="text-end text-muted" style="font-size:10px">' + (m.clock || m.time || '') + '</div>';
    d.innerHTML = h;
    return d;
  }
  function nearBottom() { return list.scrollHeight - list.scrollTop - list.clientHeight < 120; }
  function send() {
    const v = input.value.trim();
    if (!v) return;
    sendBtn.disabled = true;
    fetch(BASE + '/send', { method: 'POST', headers: Object.assign({ 'Content-Type': 'application/json' }, csrfHeaders()), body: JSON.stringify({ message: v }) })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) { emptyMsg?.remove(); input.value = ''; list.insertBefore(bubble(res.data), pill); lastId = Math.max(lastId, parseInt(res.data.id, 10)); list.scrollTop = list.scrollHeight; }
        else alert(res.message || 'Send failed.');
      }).catch(() => alert('Send failed.')).finally(() => { sendBtn.disabled = false; });
  }
  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
  pill.addEventListener('click', () => { list.scrollTop = list.scrollHeight; unread = 0; pill.classList.add('d-none'); });
  setInterval(() => {
    fetch(BASE + '/messages', { headers: csrfHeaders() })
      .then(r => r.json()).then(res => {
        if (res.status !== 'success' || !res.data) return;
        const stick = nearBottom();
        let maxId = lastId;
        res.data.forEach(m => {
          if (parseInt(m.id, 10) <= lastId) return;
          list.insertBefore(bubble(m), pill);
          maxId = Math.max(maxId, parseInt(m.id, 10));
          if (!m.is_me && !stick) unread++;
        });
        lastId = maxId;
        if (stick) { list.scrollTop = list.scrollHeight; unread = 0; pill.classList.add('d-none'); }
        else if (unread > 0) { pillCount.textContent = unread; pill.classList.remove('d-none'); }
      }).catch(e => console.error(e));
  }, 8000);
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
