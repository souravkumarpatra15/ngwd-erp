<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$sc = ['open'=>'danger','in_progress'=>'primary','closed'=>'success','on_hold'=>'warning'][$ticket['status']] ?? 'secondary';
$pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'][$ticket['priority']] ?? 'secondary';
?>

<div class="row g-4">
  <!-- Thread -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 py-3">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$ticket['status'])) ?></span>
          <span class="badge bg-<?= $pc ?>"><?= ucfirst($ticket['priority']) ?></span>
          <span class="text-muted small">#<?= esc($ticket['ticket_number']) ?></span>
        </div>
        <h5 class="mb-0 fw-bold"><?= esc($ticket['subject']) ?></h5>
        <div class="text-muted small mt-1"><i class="bi bi-person me-1"></i><?= esc($ticket['client_name'] ?? '—') ?> · <?= date('d M Y H:i', strtotime($ticket['created_at'])) ?></div>
      </div>
      <div class="card-body">
        <!-- Original message -->
        <div class="d-flex gap-3 mb-4">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
               style="width:36px;height:36px;font-size:13px">
            <?= strtoupper(substr($ticket['client_name'] ?? 'C', 0, 1)) ?>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="fw-semibold small"><?= esc($ticket['client_name'] ?? 'Client') ?></span>
              <span class="text-muted" style="font-size:11px"><?= date('d M Y H:i', strtotime($ticket['created_at'])) ?></span>
            </div>
            <div class="bg-light rounded p-3 small"><?= nl2br(esc($ticket['description'])) ?></div>
          </div>
        </div>

        <!-- Replies -->
        <?php foreach ($replies as $reply): ?>
        <div class="d-flex gap-3 mb-3 <?= $reply['is_admin'] ? 'flex-row-reverse' : '' ?>">
          <div class="rounded-circle <?= $reply['is_admin'] ? 'bg-success' : 'bg-secondary' ?> text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
               style="width:36px;height:36px;font-size:13px">
            <?= strtoupper(substr($reply['user_name'] ?? ($reply['is_admin'] ? 'A' : 'C'), 0, 1)) ?>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1 <?= $reply['is_admin'] ? 'justify-content-end' : '' ?>">
              <span class="fw-semibold small"><?= esc($reply['user_name'] ?? ($reply['is_admin'] ? 'Support' : 'Client')) ?></span>
              <?php if ($reply['is_admin']): ?><span class="badge bg-success badge-sm">Admin</span><?php endif; ?>
              <span class="text-muted" style="font-size:11px"><?= date('d M Y H:i', strtotime($reply['created_at'])) ?></span>
            </div>
            <div class="<?= $reply['is_admin'] ? 'bg-success bg-opacity-10 text-end' : 'bg-light' ?> rounded p-3 small">
              <?= nl2br(esc($reply['message'])) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Reply box -->
        <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="border-top pt-4 mt-3">
          <h6 class="fw-semibold mb-3">Write a Reply</h6>
          <form id="replyForm">
            <?= csrf_field() ?>
            <textarea name="message" class="form-control mb-3" rows="4" placeholder="Type your reply..." required></textarea>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Reply</button>
              <button type="button" class="btn btn-outline-success btn-close-ticket"><i class="bi bi-check-circle me-1"></i>Close Ticket</button>
            </div>
          </form>
        </div>
        <?php else: ?>
        <div class="border-top pt-3 mt-3 text-center text-muted small">
          <i class="bi bi-lock me-1"></i>This ticket is closed.
          <button class="btn btn-sm btn-outline-primary ms-2 btn-reopen">Reopen</button>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Ticket Info</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small">Client</td><td class="fw-semibold small"><?= esc($ticket['client_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Email</td><td class="small"><?= esc($ticket['client_email'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Priority</td><td><span class="badge bg-<?= $pc ?>"><?= ucfirst($ticket['priority']) ?></span></td></tr>
          <tr><td class="text-muted small">Status</td><td><span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$ticket['status'])) ?></span></td></tr>
          <tr><td class="text-muted small">Opened</td><td class="small"><?= date('d M Y', strtotime($ticket['created_at'])) ?></td></tr>
          <?php if ($ticket['closed_at']): ?><tr><td class="text-muted small">Closed</td><td class="small"><?= date('d M Y', strtotime($ticket['closed_at'])) ?></td></tr><?php endif; ?>
        </table>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Actions</h6></div>
      <div class="card-body d-grid gap-2">
        <select class="form-select form-select-sm ticket-status-sel" data-id="<?= $ticket['id'] ?>">
          <?php foreach (['open','in_progress','on_hold','closed'] as $s): ?>
          <option value="<?= $s ?>" <?= $ticket['status']==$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
        <a href="<?= base_url('admin/tickets') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Tickets</a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;

$('#replyForm').on('submit', function(e) {
  e.preventDefault();
  showLoader('Sending reply...');
  $.post(`${BASE}admin/tickets/reply/<?= $ticket['id'] ?>`, $(this).serialize(), res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') setTimeout(() => location.reload(), 600);
  }, 'json');
});

$('.btn-close-ticket').on('click', function() {
  showLoader('Closing ticket...');
  $.post(`${BASE}admin/tickets/status/<?= $ticket['id'] ?>`, {status:'closed', csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') setTimeout(() => location.reload(), 600);
  }, 'json');
});

$('.btn-reopen').on('click', function() {
  $.post(`${BASE}admin/tickets/status/<?= $ticket['id'] ?>`, {status:'open', csrf_test_name: CSRF}, res => {
    showToast(res.message, res.status);
    if (res.status === 'success') setTimeout(() => location.reload(), 600);
  }, 'json');
});

$('.ticket-status-sel').on('change', function() {
  $.post(`${BASE}admin/tickets/status/${$(this).data('id')}`, {status: $(this).val(), csrf_test_name: CSRF}, res => {
    showToast(res.message, res.status);
  }, 'json');
});
</script>
<?= $this->endSection() ?>
