<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<?php
$sc = ['open'=>'danger','in_progress'=>'primary','closed'=>'success','on_hold'=>'warning'][$ticket['status']] ?? 'secondary';
$pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'][$ticket['priority']] ?? 'secondary';
?>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$ticket['status'])) ?></span>
          <span class="badge bg-<?= $pc ?>"><?= ucfirst($ticket['priority']) ?></span>
          <span class="text-muted small">#<?= esc($ticket['ticket_number']) ?></span>
        </div>
        <h6 class="mb-0 fw-bold"><?= esc($ticket['subject']) ?></h6>
      </div>
      <div class="card-body">
        <!-- Original message -->
        <div class="d-flex gap-3 mb-4">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:36px;height:36px;font-size:13px">
            <?= strtoupper(substr($current_user['name']??'C',0,1)) ?>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="fw-semibold small"><?= esc($current_user['name']??'You') ?></span>
              <span class="text-muted" style="font-size:11px"><?= date('d M Y H:i',strtotime($ticket['created_at'])) ?></span>
            </div>
            <div class="bg-light rounded p-3 small"><?= nl2br(esc($ticket['description'])) ?></div>
          </div>
        </div>

        <!-- Replies -->
        <?php foreach ($replies as $reply): ?>
        <div class="d-flex gap-3 mb-3 <?= $reply['is_admin'] ? 'flex-row-reverse' : '' ?>">
          <div class="rounded-circle <?= $reply['is_admin'] ? 'bg-success' : 'bg-secondary' ?> text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:36px;height:36px;font-size:13px">
            <?= strtoupper(substr($reply['user_name']??($reply['is_admin']?'S':'C'),0,1)) ?>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1 <?= $reply['is_admin'] ? 'justify-content-end' : '' ?>">
              <span class="fw-semibold small"><?= esc($reply['user_name']??($reply['is_admin']?'Support':'You')) ?></span>
              <?php if ($reply['is_admin']): ?><span class="badge bg-success badge-sm">Support</span><?php endif; ?>
              <span class="text-muted" style="font-size:11px"><?= date('d M Y H:i',strtotime($reply['created_at'])) ?></span>
            </div>
            <div class="<?= $reply['is_admin'] ? 'bg-success bg-opacity-10' : 'bg-light' ?> rounded p-3 small">
              <?= nl2br(esc($reply['message'])) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Client reply box -->
        <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="border-top pt-4 mt-3">
          <form id="clientReplyForm">
            <?= csrf_field() ?>
            <textarea name="message" class="form-control mb-3" rows="3" placeholder="Add a reply..." required></textarea>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i>Send Reply</button>
          </form>
        </div>
        <?php else: ?>
        <div class="border-top pt-3 mt-3 text-center text-muted small">
          <i class="bi bi-lock me-1"></i>This ticket is closed. <a href="<?= base_url('portal/tickets/create') ?>">Open a new ticket</a> if you need more help.
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Ticket Info</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small">Status</td><td><span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$ticket['status'])) ?></span></td></tr>
          <tr><td class="text-muted small">Priority</td><td><span class="badge bg-<?= $pc ?>"><?= ucfirst($ticket['priority']) ?></span></td></tr>
          <tr><td class="text-muted small">Opened</td><td class="small"><?= date('d M Y',strtotime($ticket['created_at'])) ?></td></tr>
          <?php if ($ticket['closed_at']): ?><tr><td class="text-muted small">Closed</td><td class="small"><?= date('d M Y',strtotime($ticket['closed_at'])) ?></td></tr><?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$('#clientReplyForm').on('submit', function(e) {
  e.preventDefault();
  $.post('<?= base_url('portal/tickets/reply/'.$ticket['id']) ?>', $(this).serialize(), res => {
    if (res.status === 'success') setTimeout(() => location.reload(), 400);
    else alert(res.message);
  }, 'json');
});
</script>
<?= $this->endSection() ?>
