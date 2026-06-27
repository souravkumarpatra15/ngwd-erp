<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="row g-3">
  <?php if (empty($projects)): ?>
  <div class="col-12 text-center text-muted py-5">
    <i class="bi bi-folder2-open fs-2 d-block mb-2 opacity-25"></i>
    No projects yet. Your projects will appear here once work begins.
  </div>
  <?php else: ?>
  <?php foreach ($projects as $p): ?>
  <?php
    $sc = ['pending'=>'secondary','development'=>'primary','testing'=>'info','revision'=>'warning','completed'=>'success','on_hold'=>'danger'][$p['status']] ?? 'secondary';
  ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge bg-<?= $sc ?>"><?= ucwords(str_replace('_',' ',$p['status'])) ?></span>
          <span class="text-muted small"><?= esc($p['project_number']) ?></span>
        </div>
        <h6 class="fw-bold mb-1"><?= esc($p['name']) ?></h6>
        <div class="text-muted small mb-3"><?= ucwords(str_replace('_',' ',$p['type'] ?? '')) ?></div>

        <div class="mb-3">
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Progress</span><span><?= $p['progress'] ?? 0 ?>%</span>
          </div>
          <div class="progress" style="height:6px">
            <div class="progress-bar bg-<?= $sc ?>" style="width:<?= $p['progress'] ?? 0 ?>%"></div>
          </div>
        </div>

        <div class="row text-center small g-2 mb-3">
          <div class="col-6">
            <div class="text-muted">Start</div>
            <div class="fw-semibold"><?= $p['start_date'] ? date('d M Y',strtotime($p['start_date'])) : '—' ?></div>
          </div>
          <div class="col-6">
            <div class="text-muted">Deadline</div>
            <div class="fw-semibold"><?= $p['end_date'] ? date('d M Y',strtotime($p['end_date'])) : '—' ?></div>
          </div>
        </div>

        <a href="<?= base_url('portal/projects/'.$p['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
          <i class="bi bi-eye me-1"></i>View Details
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
