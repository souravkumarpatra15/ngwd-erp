<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<?php
$sc = ['pending'=>'secondary','development'=>'primary','testing'=>'info','revision'=>'warning','completed'=>'success','on_hold'=>'danger'][$project['status']] ?? 'secondary';
?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$project['status'])) ?></span>
      <span class="text-muted small"><?= esc($project['project_number']) ?></span>
    </div>
    <h5 class="fw-bold mb-1"><?= esc($project['name']) ?></h5>
    <div class="text-muted small mb-3"><?= ucwords(str_replace('_',' ',$project['type'] ?? '')) ?></div>
    <div class="mb-2">
      <div class="d-flex justify-content-between small text-muted mb-1">
        <span>Overall Progress</span><span class="fw-semibold"><?= $project['progress'] ?? 0 ?>%</span>
      </div>
      <div class="progress" style="height:10px">
        <div class="progress-bar bg-<?= $sc ?>" style="width:<?= $project['progress'] ?? 0 ?>%"></div>
      </div>
    </div>
    <?php if ($project['start_date'] || $project['end_date']): ?>
    <div class="text-muted small mt-2">
      <i class="bi bi-calendar me-1"></i>
      <?= $project['start_date'] ? date('d M Y',strtotime($project['start_date'])) : '?' ?>
      → <?= $project['end_date'] ? date('d M Y',strtotime($project['end_date'])) : 'TBD' ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Milestones -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-0 py-3">
    <h6 class="mb-0 fw-semibold"><i class="bi bi-flag me-2 text-warning"></i>Project Milestones</h6>
  </div>
  <div class="card-body p-0">
    <?php if (empty($milestones)): ?>
    <div class="text-center text-muted py-4 small">No milestones have been set yet.</div>
    <?php else: ?>
    <div class="list-group list-group-flush">
      <?php foreach ($milestones as $ms): ?>
      <?php $msc = ['pending'=>'secondary','invoiced'=>'info','completed'=>'success','paid'=>'success'][$ms['status']] ?? 'secondary'; ?>
      <div class="list-group-item px-4 py-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <?php if (in_array($ms['status'],['completed','paid'])): ?>
              <i class="bi bi-check-circle-fill text-success"></i>
              <?php else: ?>
              <i class="bi bi-circle text-muted"></i>
              <?php endif; ?>
              <span class="fw-semibold small"><?= esc($ms['title']) ?></span>
              <span class="badge bg-<?= $msc ?> badge-sm"><?= ucfirst($ms['status']) ?></span>
            </div>
            <?php if ($ms['due_date'] && $ms['due_date'] !== '0000-00-00'): ?>
            <div class="text-muted" style="font-size:11px;margin-left:22px"><i class="bi bi-calendar me-1"></i><?= date('d M Y',strtotime($ms['due_date'])) ?></div>
            <?php endif; ?>
          </div>
          <div class="fw-bold text-primary">₹<?= number_format($ms['amount'] ?? 0, 0) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="text-center">
  <a href="<?= base_url('portal/projects') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Projects</a>
  <a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-outline-primary btn-sm ms-2"><i class="bi bi-headset me-1"></i>Raise Support Ticket</a>
</div>

<?= $this->endSection() ?>
