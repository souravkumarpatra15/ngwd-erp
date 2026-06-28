<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Year</label>
        <select name="year" class="form-select form-select-sm">
          <?php for ($y = date('Y'); $y >= date('Y')-4; $y--): ?>
          <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach (['pending','development','testing','revision','completed','on_hold','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($status??'')==$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
      <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/reports/export/projects/excel?year='.$year.'&status='.($status??'')) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="<?= base_url('admin/reports/export/projects/csv?year='.$year.'&status='.($status??'')) ?>" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
      </div>
    </form>
  </div>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-secondary"><?= $counts['pending'] ?? 0 ?></div>
      <div class="text-muted small">Pending</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-primary"><?= $counts['development'] ?? 0 ?></div>
      <div class="text-muted small">In Development</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-success"><?= $counts['completed'] ?? 0 ?></div>
      <div class="text-muted small">Completed</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-info">₹<?= number_format($total_budget, 0) ?></div>
      <div class="text-muted small">Total Budget</div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Project #</th><th>Name</th><th>Client</th><th>Type</th><th>Budget</th><th>Advance</th><th>Status</th><th>Delivery</th></tr>
      </thead>
      <tbody>
        <?php if (empty($projects)): ?>
        <tr><td colspan="8" class="text-center text-muted py-5">No projects found for this period.</td></tr>
        <?php else: ?>
        <?php
        $sc = ['pending'=>'secondary','development'=>'primary','testing'=>'info','revision'=>'warning','completed'=>'success','on_hold'=>'danger','cancelled'=>'dark'];
        foreach ($projects as $p):
        ?>
        <tr>
          <td><a href="<?= base_url('admin/projects/'.$p['id']) ?>" class="fw-semibold text-decoration-none small"><?= esc($p['project_number']) ?></a></td>
          <td>
            <div class="fw-semibold small"><?= esc($p['name']) ?></div>
          </td>
          <td class="small text-muted"><?= esc($p['client_name'] ?? '—') ?></td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$p['type'] ?? '')) ?></span></td>
          <td class="fw-semibold small">₹<?= number_format($p['budget'] ?? 0, 0) ?></td>
          <td class="small text-success">₹<?= number_format($p['advance_paid'] ?? 0, 0) ?></td>
          <td><span class="badge bg-<?= $sc[$p['status']] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$p['status'])) ?></span></td>
          <td class="small text-muted"><?= ($p['delivery_date']??null) && $p['delivery_date'] !== '0000-00-00' ? date('d M Y',strtotime($p['delivery_date'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($projects)): ?>
      <tfoot class="table-light">
        <tr>
          <td colspan="4" class="text-end fw-bold small">Totals</td>
          <td class="fw-bold small">₹<?= number_format($total_budget, 0) ?></td>
          <td class="fw-bold small text-success">₹<?= number_format(array_sum(array_column($projects,'advance_paid')), 0) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
