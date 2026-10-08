<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<style>
.wa-wrap { height: calc(100vh - 170px); min-height: 520px; }
.wa-list, .wa-msgs { overflow-y: auto; }
.wa-msgs { background: #efeae2; }
.bubble { max-width: 78%; padding: 7px 11px; border-radius: 12px; word-break: break-word; position: relative; }
.bubble.me { background: #d9fdd3; margin-left: auto; border-top-right-radius: 2px; }
.bubble.them { background: #fff; margin-right: auto; border-top-left-radius: 2px; box-shadow: 0 1px 1px rgba(0,0,0,.08); }
.conv-item.active { background: #f0f2f5; }
.tick { font-style: normal; letter-spacing: -2px; }
.tick.read { color: #53bdeb; }
.day-pill { align-self: center; background: #e2e6ea; color: #54656f; font-size: 11px; padding: 3px 12px; border-radius: 8px; }
#newMsgPill { position: sticky; bottom: 8px; align-self: center; z-index: 5; }
</style>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="row g-0 wa-wrap">
      <!-- LEFT: conversations -->
      <div class="col-md-4 border-end d-flex flex-column">
        <div class="p-2 border-bottom">
          <div class="fw-semibold px-1 mb-2"><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp Inbox</div>
          <input type="text" id="convSearch" class="form-control form-control-sm mb-2" placeholder="Search name or number..." value="<?= esc($search) ?>" autocomplete="off">
          <div class="btn-group btn-group-sm w-100" role="group">
            <?php foreach (['all' => 'All', 'unread' => 'Unread', 'clients' => 'Clients', 'leads' => 'Leads', 'unknown' => 'Unknown'] as $k => $label): ?>
              <button class="btn btn-sm filter-btn <?= $filter === $k ? 'btn-success' : 'btn-outline-secondary' ?>" data-filter="<?= $k ?>"><?= $label ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="wa-list flex-grow-1 p-2" id="convList">
          <?php if (empty($conversations)): ?>
            <div class="text-center text-muted py-5 small">No conversations yet.</div>
          <?php else: foreach ($conversations as $c): $cid = (int) $c['id']; ?>
            <a href="<?= base_url('admin/chat?c=' . $cid . '&filter=' . esc($filter)) ?>" data-cid="<?= $cid ?>"
               class="conv-item d-flex align-items-center gap-2 text-decoration-none text-dark p-2 rounded <?= $cid === (int) $selectedId ? 'active' : '' ?>">
              <span class="rounded-circle <?= ($c['contact_type'] ?? '') === 'client' ? 'bg-primary' : ((($c['contact_type'] ?? '') === 'lead') ? 'bg-warning text-dark' : 'bg-secondary') ?> text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px"><?= esc(strtoupper(substr($c['contact_name'] ?? '?', 0, 1))) ?></span>
              <span class="flex-grow-1 overflow-hidden">
                <span class="d-flex justify-content-between align-items-center">
                  <span class="fw-semibold small text-truncate"><?= esc($c['contact_name'] ?? $c['phone_number']) ?></span>
                  <span class="text-muted flex-shrink-0" style="font-size:10px"><?= esc(! empty($c['last_message_at']) ? date('d M h:i A', strtotime($c['last_message_at'])) : '') ?></span>
                </span>
                <span class="d-flex justify-content-between align-items-center gap-1">
                  <span class="text-muted text-truncate" style="font-size:12px"><?= ($c['last_message_direction'] ?? '') === 'outbound' ? '✓ ' : '' ?><?= esc($c['last_message_preview'] ?? '') ?></span>
                  <?php if (! empty($c['unread_count'])): ?><span class="badge bg-success rounded-pill flex-shrink-0"><?= (int) $c['unread_count'] ?></span><?php endif; ?>
                </span>
                <span class="badge bg-light text-muted border" style="font-size:10px"><?= esc(ucfirst($c['contact_type'] ?? 'unknown')) ?> · <?= esc($c['phone_number']) ?></span>
              </span>
            </a>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <!-- RIGHT: thread -->
      <div class="col-md-8 d-flex flex-column" style="min-width:0">
        <?php if (empty($selected)): ?>
          <div class="d-flex align-items-center justify-content-center h-100 text-muted small py-5"><i class="bi bi-chat-dots me-2"></i>Select a conversation.</div>
        <?php else: ?>
          <div class="p-2 px-3 border-bottom d-flex align-items-center gap-2">
            <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px"><?= esc(strtoupper(substr($selected['contact_name'] ?? '?', 0, 1))) ?></span>
            <div class="flex-grow-1 overflow-hidden">
              <div class="fw-semibold small text-truncate"><?= esc($selected['contact_name'] ?? $selected['phone_number']) ?></div>
              <div class="text-muted text-truncate" style="font-size:11px">+<?= esc($selected['phone_number']) ?> · <?= esc(ucfirst($selected['contact_type'] ?? 'unknown')) ?></div>
            </div>
            <button class="btn btn-sm btn-outline-secondary" id="contactToggle" title="Contact details"><i class="bi bi-person-lines-fill"></i></button>
          </div>
          <div class="d-flex flex-grow-1" style="min-height:0">
            <div class="d-flex flex-column flex-grow-1" style="min-width:0">
              <div class="wa-msgs flex-grow-1 p-3 d-flex flex-column gap-2" id="msgList">
                <?php $lastDay = ''; foreach ($messages as $m): ?>
                  <?php if (($m['day'] ?? '') !== $lastDay): $lastDay = $m['day'] ?? ''; ?>
                    <div class="day-pill"><?= esc($lastDay) ?></div>
                  <?php endif; ?>
                  <?= $this->include('admin/chat/_bubble', ['m' => $m]) ?>
                <?php endforeach; ?>
                <button class="btn btn-sm btn-light border d-none" id="newMsgPill">↓ <span id="newMsgCount">0</span> new</button>
              </div>
              <div class="p-2 px-3 border-top">
                <div class="btn-group btn-group-sm mb-2" role="group">
                  <button class="btn btn-sm mode-btn btn-success" data-mode="text">Text</button>
                  <button class="btn btn-sm mode-btn btn-outline-secondary" data-mode="template">Template</button>
                </div>
                <div id="textComposer" class="d-flex gap-2">
                  <label class="btn btn-outline-secondary mb-0" title="Send image"><i class="bi bi-image"></i><input type="file" id="imgFile" accept="image/png,image/jpeg,image/gif,image/webp" class="d-none"></label>
                  <input type="text" id="msgInput" class="form-control" placeholder="Type a message..." maxlength="2000" autocomplete="off">
                  <button class="btn btn-success" id="sendBtn"><i class="bi bi-send"></i></button>
                </div>
                <div id="tplComposer" class="d-none">
                  <div class="d-flex gap-2 mb-2">
                    <select id="tplSelect" class="form-select form-select-sm">
                      <?php foreach ($templates as $tname => $labels): ?>
                        <option value="<?= esc($tname) ?>"><?= esc($tname) ?> (<?= count($labels) ?> vars)</option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-success" id="tplSendBtn">Send Template</button>
                  </div>
                  <div id="tplVars" class="row g-2"></div>
                  <div class="form-text">Templates start a conversation outside the 24h window.</div>
                </div>
              </div>
            </div>
            <!-- Contact panel -->
            <div class="border-start p-3 d-none d-lg-block" id="contactPanel" style="width:250px;overflow-y:auto">
              <h6 class="fw-semibold small">Contact</h6>
              <div class="fw-semibold"><?= esc($selected['contact_name'] ?? '—') ?></div>
              <div class="text-muted small mb-2">+<?= esc($selected['phone_number']) ?></div>
              <div class="small mb-1"><span class="text-muted">Type:</span> <?= esc(ucfirst($selected['contact_type'] ?? 'unknown')) ?></div>
              <?php if (! empty($contact)): ?>
                <?php if (! empty($contact['email'])): ?><div class="small mb-1"><span class="text-muted">Email:</span> <?= esc($contact['email']) ?></div><?php endif; ?>
                <?php if (! empty($contact['company_name'])): ?><div class="small mb-1"><span class="text-muted">Company:</span> <?= esc($contact['company_name']) ?></div><?php endif; ?>
                <?php if (! empty($contact['mobile'])): ?><div class="small mb-1"><span class="text-muted">Mobile:</span> <?= esc($contact['mobile']) ?></div><?php endif; ?>
                <hr>
                <div class="small fw-semibold mb-1">CRM</div>
                <div class="d-grid gap-1">
                  <?php if (($contact['_kind'] ?? '') === 'Client'): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/clients/' . (int) $contact['id']) ?>">View Client</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/projects?client_id=' . (int) $contact['id']) ?>">View Projects</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/invoices?client_id=' . (int) $contact['id']) ?>">View Invoices</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/proposals?client_id=' . (int) $contact['id']) ?>">View Proposals</a>
                  <?php else: ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/leads/' . (int) $contact['id']) ?>">View Lead</a>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div class="text-muted small">Unknown contact — no CRM record for this number yet.</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (! empty($selected)): ?>
<script>
(function () {
  const CID = <?= (int) $selectedId ?>;
  const BASE = '<?= base_url('admin/chat') ?>';
  const TPLS = <?= json_encode($templates) ?>;
  const list = document.getElementById('msgList');
  const input = document.getElementById('msgInput');
  const sendBtn = document.getElementById('sendBtn');
  const imgFile = document.getElementById('imgFile');
  const pill = document.getElementById('newMsgPill');
  const pillCount = document.getElementById('newMsgCount');
  let lastId = <?= empty($messages) ? 0 : (int) end($messages)['id'] ?>;
  let unread = 0;
  list.scrollTop = list.scrollHeight;

  function tick(m) {
    if (!m.is_me) return '';
    if (m.status === 'failed') return ' <span class="text-danger" title="Failed">⚠</span>';
    if (m.status === 'read') return ' <i class="tick read" title="Read">✓✓</i>';
    if (m.status === 'delivered') return ' <i class="tick" title="Delivered">✓✓</i>';
    return ' <i class="tick text-muted" title="Sent">✓</i>';
  }
  function bubble(m, day) {
    const wrap = document.createElement('div');
    wrap.style.display = 'contents';
    let h = '';
    if (day) h += '<div class="day-pill">' + day + '</div>';
    const d = document.createElement('div');
    d.className = 'bubble ' + (m.is_me ? 'me' : 'them');
    d.dataset.mid = m.id;
    let inner = '';
    if (m.message_type === 'image' && m.media_url) {
      const src = m.media_url.indexOf('http') === 0 ? m.media_url : '<?= base_url('admin/chat/serve') ?>/' + m.id;
      inner += '<a href="' + src + '" target="_blank"><img src="' + src + '" class="img-fluid rounded mb-1" style="max-height:220px" alt=""></a>';
    }
    if (m.message_type === 'template') {
      inner += '<div class="small fw-semibold">' + (m.template_name || 'template') + '</div>';
      (m.template_params || []).forEach(p => { const x = document.createElement('div'); x.className = 'small'; x.textContent = p; inner += x.outerHTML; });
    } else if (m.message_text) { const p = document.createElement('div'); p.className = 'small'; p.textContent = m.message_text; inner += p.outerHTML; }
    inner += '<div class="text-end text-muted" style="font-size:10px">' + (m.clock || m.time || '') + tick(m) + '</div>';
    if (m.status === 'failed') inner += '<div><button class="btn btn-sm btn-link text-danger p-0 retry-btn" data-mid="' + m.id + '">Retry</button></div>';
    d.innerHTML = inner;
    const frag = document.createDocumentFragment();
    if (h) { const dp = document.createElement('div'); dp.className = 'day-pill'; dp.textContent = day; frag.appendChild(dp); }
    frag.appendChild(d);
    return frag;
  }
  function nearBottom() { return list.scrollHeight - list.scrollTop - list.clientHeight < 120; }
  function poll() {
    fetch(BASE + '/messages?c=' + CID + '&after_id=' + lastId, { headers: csrfHeaders() })
      .then(r => r.json()).then(res => {
        if (res.status !== 'success' || !res.data || !res.data.length) return;
        let lastDay = null;
        const days = list.querySelectorAll('.day-pill');
        if (days.length) lastDay = days[days.length - 1].textContent;
        const stick = nearBottom();
        res.data.forEach(m => {
          const showDay = m.day && m.day !== lastDay ? m.day : null;
          if (showDay) lastDay = m.day;
          list.insertBefore(bubble(m, showDay), pill);
          lastId = Math.max(lastId, parseInt(m.id, 10));
          if (!m.is_me && !stick) { unread++; }
        });
        if (stick) { list.scrollTop = list.scrollHeight; unread = 0; pill.classList.add('d-none'); }
        else if (unread > 0) { pillCount.textContent = unread; pill.classList.remove('d-none'); }
      }).catch(e => console.error(e));
  }
  pill.addEventListener('click', () => { list.scrollTop = list.scrollHeight; unread = 0; pill.classList.add('d-none'); });
  function send() {
    const v = input.value.trim();
    if (!v) return;
    sendBtn.disabled = true;
    fetch(BASE + '/send', { method: 'POST', headers: Object.assign({ 'Content-Type': 'application/json' }, csrfHeaders()), body: JSON.stringify({ c: CID, message: v }) })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) {
          input.value = '';
          list.insertBefore(bubble(res.data, null), pill);
          lastId = Math.max(lastId, parseInt(res.data.id, 10));
          list.scrollTop = list.scrollHeight;
        } else alert(res.message || 'Send failed.');
      }).catch(() => alert('Send failed.')).finally(() => { sendBtn.disabled = false; });
  }
  sendBtn.addEventListener('click', send);
  input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
  list.addEventListener('click', e => {
    const b = e.target.closest('.retry-btn');
    if (!b) return;
    fetch(BASE + '/retry/' + b.dataset.mid, { method: 'POST', headers: csrfHeaders() })
      .then(r => r.json()).then(res => {
        if (res.status === 'success') location.reload();
        else alert(res.message || 'Retry failed.');
      }).catch(() => alert('Retry failed.'));
  });
  imgFile.addEventListener('change', () => {
    if (!imgFile.files.length) return;
    const fd = new FormData();
    fd.append('image', imgFile.files[0]);
    fd.append('c', CID);
    fd.append('caption', input.value.trim());
    fd.append('csrf_test_name', getCsrfToken());
    fetch(BASE + '/upload-image', { method: 'POST', headers: csrfHeaders(), body: fd })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) {
          input.value = ''; imgFile.value = '';
          list.insertBefore(bubble(res.data, null), pill);
          lastId = Math.max(lastId, parseInt(res.data.id, 10));
          list.scrollTop = list.scrollHeight;
        } else alert(res.message || 'Upload failed.');
      }).catch(() => alert('Upload failed.'));
  });
  // Template composer
  const tplSelect = document.getElementById('tplSelect');
  const tplVars = document.getElementById('tplVars');
  function renderVars() {
    const labels = TPLS[tplSelect.value] || [];
    tplVars.innerHTML = '';
    labels.forEach((lb, i) => {
      const col = document.createElement('div');
      col.className = 'col-md-6';
      col.innerHTML = '<label class="form-label small fw-semibold"></label><input class="form-control form-control-sm" data-i="' + i + '">';
      col.querySelector('label').textContent = (i + 1) + '. ' + lb;
      tplVars.appendChild(col);
    });
  }
  tplSelect.addEventListener('change', renderVars);
  renderVars();
  document.querySelectorAll('.mode-btn').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.mode-btn').forEach(x => { x.classList.remove('btn-success'); x.classList.add('btn-outline-secondary'); });
    b.classList.remove('btn-outline-secondary'); b.classList.add('btn-success');
    const tpl = b.dataset.mode === 'template';
    document.getElementById('textComposer').classList.toggle('d-none', tpl);
    document.getElementById('tplComposer').classList.toggle('d-none', !tpl);
  }));
  document.getElementById('tplSendBtn').addEventListener('click', () => {
    const params = Array.from(tplVars.querySelectorAll('input')).map(i => i.value.trim());
    fetch(BASE + '/send-template', { method: 'POST', headers: Object.assign({ 'Content-Type': 'application/json' }, csrfHeaders()), body: JSON.stringify({ c: CID, template: tplSelect.value, params: params }) })
      .then(r => r.json()).then(res => {
        if (res.status === 'success' && res.data) {
          list.insertBefore(bubble(res.data, null), pill);
          lastId = Math.max(lastId, parseInt(res.data.id, 10));
          list.scrollTop = list.scrollHeight;
        } else alert(res.message || 'Template send failed.');
      }).catch(() => alert('Template send failed.'));
  });
  // Filters + search (page reload keeps it simple and robust)
  document.querySelectorAll('.filter-btn').forEach(b => b.addEventListener('click', () => {
    const q = document.getElementById('convSearch').value.trim();
    location.href = '<?= base_url('admin/chat') ?>?filter=' + b.dataset.filter + (q ? '&q=' + encodeURIComponent(q) : '');
  }));
  document.getElementById('convSearch').addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      const f = document.querySelector('.filter-btn.btn-success');
      location.href = '<?= base_url('admin/chat') ?>?filter=' + (f ? f.dataset.filter : 'all') + '&q=' + encodeURIComponent(e.target.value.trim());
    }
  });
  document.getElementById('contactToggle').addEventListener('click', () => {
    document.getElementById('contactPanel').classList.toggle('d-none');
  });
  setInterval(poll, 5000);
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
