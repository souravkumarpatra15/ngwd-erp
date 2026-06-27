<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>New Support Ticket
  </a>
</div>

<?php if (empty($tickets)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-headset fs-2 d-block mb-2 opacity-25"></i>No support tickets yet.
  <div class="mt-2"><a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-sm btn-primary">Raise a Ticket</a></div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Ticket #</th><th>Subject</th><th>Priority</th><th>Status</th><th>Opened</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
        <?php
          $sc = ['open'=>'danger','in_progress'=>'primary','closed'=>'success','on_hold'=>'warning'][$t['status']] ?? 'secondary';
          $pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'][$t['priority']] ?? 'secondary';
        ?>
        <tr>
          <td class="fw-semibold small">#<?= esc($t['ticket_number']) ?></td>
          <td><a href="<?= base_url('portal/tickets/'.$t['id']) ?>" class="text-decoration-none fw-semibold"><?= esc($t['subject']) ?></a></td>
          <td><span class="badge bg-<?= $pc ?>"><?= ucfirst($t['priority']) ?></span></td>
          <td><span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$t['status'])) ?></span></td>
          <td class="small text-muted"><?= date('d M Y',strtotime($t['created_at'])) ?></td>
          <td><a href="<?= base_url('portal/tickets/'.$t['id']) ?>" class="btn btn-xs btn-outline-primary"><i class="bi bi-chat-text me-1"></i>View</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
