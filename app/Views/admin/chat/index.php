<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<style>
.chat-wrap { height: calc(100vh - 170px); min-height: 480px; }
.chat-list { max-height: 100%; overflow-y: auto; }
.chat-msgs { max-height: 100%; overflow-y: auto; background: #efeae2; }
.bubble { max-width: 75%; padding: 8px 12px; border-radius: 12px; word-break: break-word; }
.bubble.me { background: #d9fdd3; margin-left: auto; border-top-right-radius: 2px; }
.bubble.them { background: #fff; margin-right: auto; border-top-left-radius: 2px; box-shadow: 0 1px 1px rgba(0,0,0,.08); }
.conv-item.active { background: #f0f2f5; }
</style>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="row g-0 chat-wrap">
      <!-- Conversations -->
      <div class="col-md-4 border-end d-flex flex-column">
        <div class="p-3 border-bottom fw-semibold"><i class="bi bi-whatsapp me-2 text-success"></i>Chat</div>
        <div class="chat-list flex-grow-1 p-2" id="convList">
          <?php if (empty($conversations)): ?>
            <div class="text-center text-muted py-5 small"><i class="bi bi-chat-text fs-3 d-block mb-2 opacity-25"></i>No conversations yet.</div>
          <?php else: foreach ($conversations as $c): $uid = (int) $c['user_id']; ?>
            <a href="<?= base_url('admin/chat?user_id=' . $uid) ?>" data-uid="<?= $uid ?>"
               class="conv-item d-flex align-items-center gap-2 text-decoration-none text-dark p-2 rounded <?= $uid === (int) $selectedUserId ? 'active' : '' ?>">
              <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px"><?= esc(strtoupper(substr($c['user_name'] ?? 'U', 0, 1))) ?></span>
              <span class="flex-grow-1 overflow-hidden">
                <span class="d-block fw-semibold small text-truncate"><?= esc($c['user_name'] ?? ('User #' . $uid)) ?></span>
                <span class="d-block text-muted text-truncate" style="font-size:12px"><?= esc($c['last_message']['message'] ?? 'No messages yet') ?></span>
              </span>
              <?php if (! empty($c['unread_count'])): ?><span class="badge bg-success rounded-pill"><?= (int) $c['unread_count'] ?></span><?php endif; ?>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <!-- Messages -->
      <div class="col-md-8 d-flex flex-column">
        <?php if (empty($selectedUser)): ?>
          <div class="d-flex align-items-center justify-content-center h-100 text-muted small py-5"><i class="bi bi-chat-dots me-2"></i>Select a conversation to start messaging.</div>
        <?php else: ?>
          <div class="p-3 border-bottom d-flex align-items-center gap-2">
            <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px"><?= esc(strtoupper(substr($selectedUser['name'] ?? 'U', 0, 1))) ?></span>
            <div><div class="fw-semibold small"><?= esc($selectedUser['name']) ?></div><div class="text-muted" style="font-size:11px">User #<?= (int) $selectedUser['id'] ?></div></div>
          </div>
          <div class="chat-msgs flex-grow-1 p-3 d-flex flex-column gap-2" id="msgList">
            <?php foreach ($messages as $m): ?>
              <div class="bubble <?= ! empty($m['is_me']) ? 'me' : 'them' ?>" data-mid="<?= (int) $m['id'] ?>">
                <?php if (! empty($m['image_src'])): ?><a href="<?= esc($m['image_src']) ?>" target="_blank"><img src="<?= esc($m['image_src']) ?>" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a><?php endif; ?>
                <?php if (trim((string) ($m['message'] ?? '')) !== ''): ?><div class="small"><?= esc($m['message']) ?></div><?php endif; ?>
                <div class="text-end text-muted" style="font-size:10px"><?= esc($m['time'] ?? '') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="p-3 border-top">
            <div class="d-flex gap-2">
              <label class="btn btn-outline-secondary mb-0" title="Send image"><i class="bi bi-image"></i><input type="file" id="imgFile" accept="image/png,image/jpeg,image/gif,image/webp" class="d-none"></label>
              <input type="text" id="msgInput" class="form-control" placeholder="Type a message..." maxlength="2000" autocomplete="off">
              <button class="btn btn-success" id="sendBtn"><i class="bi bi-send"></i></button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (! empty($selectedUser)): ?>
<script>
(function () {
  const UID = <?= (int) $selectedUserId ?>;
  const BASE = '<?= base_url('admin/chat') ?>';
  const list = document.getElementById('msgList');
  const input = document.getElementById('msgInput');
  const sendBtn = document.getElementById('sendBtn');
  const imgFile = document.getElementById('imgFile');
  list.scrollTop = list.scrollHeight;

  function bubble(m) {
    const d = document.createElement('div');
    d.className = 'bubble ' + (m.is_me ? 'me' : 'them');
    d.dataset.mid = m.id;
    let h = '';
    if (m.image_src) h += '<a href="' + m.image_src + '" target="_blank"><img src="' + m.image_src + '" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a>';
    if (m.message) { const p = document.createElement('div'); p.className = 'small'; p.textContent = m.message; h += p.outerHTML; }
    h += '<div class="text-end text-muted" style="font-size:10px">' + (m.time || '') + '</div>';
    d.innerHTML = h;
    return d;
  }
  function refresh() {
    fetch(BASE + '/messages?user_id=' + UID, { headers: csrfHeaders() })
      .then(r => r.json()).then(res => {
        if (res.status !== 'success') return;
        list.innerHTML = '';
        (res.data || []).forEach(m => list.appendChild(bubble(m)));
        list.scrollTop = list.scrollHeight;
      }).catch(e => console.error(e));
  }
  function send() {
    const v = input.value.trim();
    if (! v) return;
    sendBtn.disabled = true;
    fetch(BASE + '/send', { method: 'POST', headers: Object.assign({ 'Content-Type': 'application/json' }, csrfHeaders()), body: JSON.stringify({ message: v, user_id: UID }) })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) { input.value = ''; list.appendChild(bubble(res.data)); list.scrollTop = list.scrollHeight; }
        else alert(res.message || 'Send failed.');
      }).catch(() => alert('Send failed.')).finally(() => { sendBtn.disabled = false; });
  }
  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); send(); } });
  imgFile.addEventListener('change', () => {
    if (! imgFile.files.length) return;
    const fd = new FormData();
    fd.append('image', imgFile.files[0]);
    fd.append('user_id', UID);
    fd.append('caption', input.value.trim());
    fd.append('csrf_test_name', getCsrfToken());
    fetch(BASE + '/upload-image', { method: 'POST', headers: csrfHeaders(), body: fd })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) { input.value = ''; imgFile.value = ''; list.appendChild(bubble(res.data)); list.scrollTop = list.scrollHeight; }
        else alert(res.message || 'Upload failed.');
      }).catch(() => alert('Upload failed.'));
  });
  setInterval(refresh, 15000);
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
