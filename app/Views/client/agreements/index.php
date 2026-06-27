<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php if (empty($agreements)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-file-earmark-check fs-2 d-block mb-2 opacity-25"></i>No agreements yet.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Agreement #</th><th>Title</th><th>Date</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($agreements as $a): ?>
        <?php $sc = ['draft'=>'secondary','sent'=>'info','signed'=>'success','cancelled'=>'danger','rejected'=>'danger'][$a['status']] ?? 'secondary'; ?>
        <tr>
          <td class="fw-semibold small"><?= esc($a['agreement_number']) ?></td>
          <td class="fw-semibold"><?= esc($a['title']) ?></td>
          <td class="small text-muted"><?= date('d M Y',strtotime($a['created_at'])) ?></td>
          <td><span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span></td>
          <td>
            <?php if ($a['status'] === 'sent'): ?>
            <a href="<?= base_url('portal/agreements/sign/'.$a['id']) ?>" class="btn btn-sm btn-success">
              <i class="bi bi-pen me-1"></i>Review &amp; Sign
            </a>
            <?php elseif ($a['status'] === 'signed'): ?>
            <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Signed <?= $a['signed_at'] ? date('d M Y',strtotime($a['signed_at'])) : '' ?></span>
            <?php else: ?>
            <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
