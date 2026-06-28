<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
      </div>
      <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/reports/export/leads/excel?from='.$from.'&to='.$to) ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="<?= base_url('admin/reports/export/leads/csv?from='.$from.'&to='.$to) ?>" class="btn btn-outline-secondary btn-sm ms-1"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
      </div>
    </form>
  </div>
</div>

<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-primary"><?= count($leads) ?></div>
      <div class="text-muted small">Total Leads</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-success"><?= $converted ?></div>
      <div class="text-muted small">Converted</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-warning"><?= count(array_filter($leads, fn($l)=>$l['status']==='lost')) ?></div>
      <div class="text-muted small">Lost</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-3 fw-bold text-info"><?= $conv_rate ?>%</div>
      <div class="text-muted small">Conversion Rate</div>
    </div>
  </div>
</div>

<!-- Source breakdown -->
<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Leads by Source</h6></div>
      <div class="card-body p-0">
        <?php
        $sources = array_count_values(array_column($leads,'source'));
        arsort($sources);
        $total = count($leads) ?: 1;
        $sourceColors = ['facebook'=>'primary','instagram'=>'danger','google_ads'=>'warning','website'=>'info','referral'=>'success','whatsapp'=>'success','phone'=>'secondary','manual'=>'dark'];
        ?>
        <?php foreach ($sources as $src => $cnt): ?>
        <div class="px-3 py-2 border-bottom d-flex align-items-center gap-3">
          <span class="badge bg-<?= $sourceColors[$src] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$src)) ?></span>
          <div class="flex-grow-1">
            <div class="progress" style="height:6px"><div class="progress-bar bg-<?= $sourceColors[$src] ?? 'secondary' ?>" style="width:<?= round($cnt/$total*100) ?>%"></div></div>
          </div>
          <span class="fw-semibold small"><?= $cnt ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Leads by Status</h6></div>
      <div class="card-body p-0">
        <?php
        $statuses = array_count_values(array_column($leads,'status'));
        $statusColors = ['new'=>'primary','contacted'=>'info','follow_up'=>'warning','proposal_sent'=>'secondary','negotiation'=>'warning','converted'=>'success','lost'=>'danger'];
        ?>
        <?php foreach ($statuses as $st => $cnt): ?>
        <div class="px-3 py-2 border-bottom d-flex align-items-center gap-3">
          <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$st)) ?></span>
          <div class="flex-grow-1">
            <div class="progress" style="height:6px"><div class="progress-bar bg-<?= $statusColors[$st] ?? 'secondary' ?>" style="width:<?= round($cnt/$total*100) ?>%"></div></div>
          </div>
          <span class="fw-semibold small"><?= $cnt ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Lead table -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Lead #</th><th>Name</th><th>Source</th><th>Mobile</th><th>Status</th><th>Follow Up</th><th>Created</th></tr>
      </thead>
      <tbody>
        <?php if (empty($leads)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No leads found for this period.</td></tr>
        <?php else: ?>
        <?php foreach ($leads as $l): ?>
        <tr>
          <td><a href="<?= base_url('admin/leads/'.$l['id']) ?>" class="fw-semibold text-decoration-none small"><?= esc($l['lead_number']) ?></a></td>
          <td>
            <div class="fw-semibold small"><?= esc($l['name']) ?></div>
            <?php if ($l['company_name']): ?><div class="text-muted" style="font-size:11px"><?= esc($l['company_name']) ?></div><?php endif; ?>
          </td>
          <td><span class="badge bg-<?= $sourceColors[$l['source']] ?? 'secondary' ?> badge-sm"><?= ucwords(str_replace('_',' ',$l['source'])) ?></span></td>
          <td class="small text-muted"><?= esc($l['mobile']) ?></td>
          <td><span class="badge bg-<?= $statusColors[$l['status']] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$l['status'])) ?></span></td>
          <td class="small text-muted"><?= $l['follow_up_date'] && $l['follow_up_date'] !== '0000-00-00' ? date('d M Y',strtotime($l['follow_up_date'])) : '—' ?></td>
          <td class="small text-muted"><?= date('d M Y',strtotime($l['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
