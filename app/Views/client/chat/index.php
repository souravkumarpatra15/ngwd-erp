<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<style>
.chat-msgs { max-height: calc(100vh - 320px); min-height: 320px; overflow-y: auto; background: #efeae2; }
.bubble { max-width: 75%; padding: 8px 12px; border-radius: 12px; word-break: break-word; }
.bubble.me { background: #d9fdd3; margin-left: auto; border-top-right-radius: 2px; }
.bubble.them { background: #fff; margin-right: auto; border-top-left-radius: 2px; box-shadow: 0 1px 1px rgba(0,0,0,.08); }
</style>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 py-3 fw-semibold"><i class="bi bi-whatsapp me-2 text-success"></i>Chat with Support</div>
  <div class="card-body p-0 d-flex flex-column">
    <div class="chat-msgs flex-grow-1 p-3 d-flex flex-column gap-2" id="msgList">
      <?php if (empty($messages)): ?>
        <div class="text-center text-muted small py-5" id="emptyMsg"><i class="bi bi-chat-dots fs-3 d-block mb-2 opacity-25"></i>Say hello — we usually reply quickly.</div>
      <?php else: foreach ($messages as $m): ?>
        <div class="bubble <?= ! empty($m['is_me']) ? 'me' : 'them' ?>">
          <?php if (! empty($m['image_src'])): ?><a href="<?= esc($m['image_src']) ?>" target="_blank"><img src="<?= esc($m['image_src']) ?>" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a><?php endif; ?>
          <?php if (trim((string) ($m['message'] ?? '')) !== ''): ?><div class="small"><?= esc($m['message']) ?></div><?php endif; ?>
          <div class="text-end text-muted" style="font-size:10px"><?= esc($m['time'] ?? '') ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <div class="p-3 border-top">
      <div class="d-flex gap-2">
        <input type="text" id="msgInput" class="form-control" placeholder="Type a message..." maxlength="2000" autocomplete="off">
        <button class="btn btn-success" id="sendBtn"><i class="bi bi-send"></i></button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const BASE = '<?= base_url('portal/chat') ?>';
  const list = document.getElementById('msgList');
  const input = document.getElementById('msgInput');
  const sendBtn = document.getElementById('sendBtn');
  const emptyMsg = document.getElementById('emptyMsg');
  list.scrollTop = list.scrollHeight;

  function bubble(m) {
    const d = document.createElement('div');
    d.className = 'bubble ' + (m.is_me ? 'me' : 'them');
    let h = '';
    if (m.image_src) h += '<a href="' + m.image_src + '" target="_blank"><img src="' + m.image_src + '" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a>';
    if (m.message) { const p = document.createElement('div'); p.className = 'small'; p.textContent = m.message; h += p.outerHTML; }
    h += '<div class="text-end text-muted" style="font-size:10px">' + (m.time || '') + '</div>';
    d.innerHTML = h;
    return d;
  }
  function send() {
    const v = input.value.trim();
    if (! v) return;
    sendBtn.disabled = true;
    fetch(BASE + '/send', { method: 'POST', headers: Object.assign({ 'Content-Type': 'application/json' }, csrfHeaders()), body: JSON.stringify({ message: v }) })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) { emptyMsg?.remove(); input.value = ''; list.appendChild(bubble(res.data)); list.scrollTop = list.scrollHeight; }
        else alert(res.message || 'Send failed.');
      }).catch(() => alert('Send failed.')).finally(() => { sendBtn.disabled = false; });
  }
  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); send(); } });
  setInterval(() => {
    fetch(BASE + '/messages', { headers: csrfHeaders() })
      .then(r => r.json()).then(res => {
        if (res.status !== 'success') return;
        list.innerHTML = '';
        (res.data || []).forEach(m => list.appendChild(bubble(m)));
        list.scrollTop = list.scrollHeight;
      }).catch(e => console.error(e));
  }, 15000);
})();
</script>
<?= $this->endSection() ?>
