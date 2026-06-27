<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php if (empty($proposals)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-file-earmark-text fs-2 d-block mb-2 opacity-25"></i>No proposals yet.
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($proposals as $p): ?>
  <?php $sc = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','revision'=>'warning','rejected'=>'danger'][$p['status']] ?? 'secondary'; ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-<?= $sc ?>"><?= ucfirst($p['status']) ?></span>
          <span class="text-muted small"><?= esc($p['proposal_number']) ?></span>
        </div>
        <h6 class="fw-bold mb-1"><?= esc($p['title']) ?></h6>
        <div class="text-muted small mb-3">
          Valid until: <?= $p['valid_until'] && $p['valid_until'] !== '0000-00-00' ? date('d M Y',strtotime($p['valid_until'])) : '—' ?>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="fw-bold text-primary fs-5">₹<?= number_format($p['total_amount']??0,0) ?></div>
          <a href="<?= base_url('portal/proposals/'.$p['id']) ?>" class="btn btn-sm btn-outline-primary">View Details</a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
