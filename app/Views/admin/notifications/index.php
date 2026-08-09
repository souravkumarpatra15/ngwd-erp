<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Notifications</h4>
    <p class="text-muted small mb-0">Everything that's happened across your leads, projects, and clients.</p>
  </div>
  <button type="button" class="btn btn-sm btn-outline-secondary" id="pageMarkAllRead"><i class="bi bi-check2-all me-1"></i>Mark all read</button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($notifications)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-25"></i>
        No notifications yet
      </div>
    <?php else: ?>
      <div class="list-group list-group-flush" id="pageNotifList">
        <?php foreach ($notifications as $n): ?>
          <?php
            $icon = [
              'new_ticket' => 'bi-headset text-info', 'ticket_reply' => 'bi-chat-dots text-info',
              'proposal_accepted' => 'bi-check-circle text-success', 'proposal_revision' => 'bi-arrow-repeat text-warning', 'proposal_sent' => 'bi-send text-primary',
              'agreement_signed' => 'bi-file-earmark-check text-success', 'agreement_rejected' => 'bi-file-earmark-x text-danger', 'agreement_sent' => 'bi-send text-primary',
              'invoice_sent' => 'bi-receipt text-primary',
              'payment_received' => 'bi-cash-coin text-success', 'payment_confirmed' => 'bi-cash-coin text-success', 'payment_due' => 'bi-credit-card text-warning',
              'milestone_note' => 'bi-chat-left-text text-info',
            ][$n['type']] ?? 'bi-bell text-secondary';

            $link = match ($n['reference_type']) {
              'invoice'   => base_url('admin/invoices/' . $n['reference_id']),
              'proposal'  => base_url('admin/proposals/' . $n['reference_id']),
              'agreement' => base_url('admin/agreements/' . $n['reference_id']),
              'ticket'    => base_url('admin/tickets/' . $n['reference_id']),
              'payment'   => base_url('admin/payments/' . $n['reference_id']),
              'milestone' => base_url('admin/milestones'),
              default     => null,
            };
          ?>
          <a href="<?= $link ?? '#' ?>" class="list-group-item list-group-item-action notif-page-item px-4 py-3 <?= $n['is_read'] == 0 ? 'bg-light' : '' ?>" data-id="<?= $n['id'] ?>">
            <div class="d-flex gap-3">
              <div class="fs-5"><i class="bi <?= $icon ?>"></i></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <strong class="small"><?= esc($n['title']) ?></strong>
                  <span class="text-muted" style="font-size:11px;white-space:nowrap"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                </div>
                <?php if (!empty($n['message'])): ?><div class="text-muted small mt-1"><?= esc($n['message']) ?></div><?php endif; ?>
              </div>
              <?php if ($n['is_read'] == 0): ?><span class="badge bg-primary align-self-start" style="font-size:9px">new</span><?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('pageMarkAllRead')?.addEventListener('click', () => {
  $.post('<?= base_url('admin/notifications/read-all') ?>', { csrf_test_name: getCsrfToken() }, () => {
    document.querySelectorAll('.notif-page-item').forEach(el => {
      el.classList.remove('bg-light');
      el.querySelector('.badge')?.remove();
    });
    showToast('All notifications marked as read', 'info');
  });
});
document.querySelectorAll('.notif-page-item').forEach(el => {
  el.addEventListener('click', () => {
    $.post(`<?= base_url('admin/notifications/read/') ?>${el.dataset.id}`, { csrf_test_name: getCsrfToken() });
  });
});
</script>
<?= $this->endSection() ?>
