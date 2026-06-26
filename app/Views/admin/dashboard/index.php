<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
<?php
$cards = [
  ['label'=>'Total Leads','value'=>$total_leads,'icon'=>'person-plus','color'=>'primary','link'=>'admin/leads'],
  ['label'=>'Total Clients','value'=>$total_clients,'icon'=>'people','color'=>'success','link'=>'admin/clients'],
  ['label'=>'Active Projects','value'=>$active_projects,'icon'=>'folder2-open','color'=>'warning','link'=>'admin/projects'],
  ['label'=>'Completed Projects','value'=>$completed_projects,'icon'=>'check2-circle','color'=>'info','link'=>'admin/projects'],
  ['label'=>'Monthly Revenue','value'=>'₹'.number_format($monthly_revenue,0),'icon'=>'cash-stack','color'=>'success','link'=>'admin/reports/revenue'],
  ['label'=>'Pending Payments','value'=>'₹'.number_format($pending_payments,0),'icon'=>'exclamation-circle','color'=>'danger','link'=>'admin/invoices'],
  ['label'=>'Domain Renewals','value'=>$domain_renewals,'icon'=>'globe','color'=>'secondary','link'=>'admin/domains'],
  ['label'=>'Hosting Renewals','value'=>$hosting_renewals,'icon'=>'server','color'=>'dark','link'=>'admin/hostings'],
];
foreach ($cards as $c): ?>
<div class="col-6 col-md-3">
  <div class="card border-0 shadow-sm h-100 card-hover">
    <div class="card-body d-flex align-items-center gap-3 py-3">
      <div class="rounded-circle bg-<?= $c['color'] ?> bg-opacity-10 p-3 flex-shrink-0" style="width:52px;height:52px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-<?= $c['icon'] ?> text-<?= $c['color'] ?> fs-5"></i>
      </div>
      <div class="overflow-hidden">
        <div class="fs-4 fw-bold text-truncate"><?= $c['value'] ?></div>
        <div class="text-muted small"><?= $c['label'] ?></div>
      </div>
    </div>
    <a href="<?= base_url($c['link']) ?>" class="card-footer bg-transparent border-top text-<?= $c['color'] ?> small text-decoration-none py-2">
      View All <i class="bi bi-arrow-right ms-1"></i>
    </a>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Revenue — <?= date('Y') ?></h6>
      </div>
      <div class="card-body pt-0"><canvas id="revenueChart" height="80"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-pie-chart me-2 text-success"></i>Lead Status</h6>
      </div>
      <div class="card-body pt-0"><canvas id="leadChart" height="140"></canvas></div>
    </div>
  </div>
</div>

<!-- Widgets Row -->
<div class="row g-3">
  <!-- Today's Follow-ups -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-telephone me-2 text-warning"></i>Today's Follow-Ups</h6>
        <span class="badge bg-warning text-dark"><?= count($todays_followups) ?></span>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($todays_followups)): ?>
        <div class="list-group-item text-center text-muted py-4 small">🎉 No follow-ups today!</div>
        <?php else: ?>
        <?php foreach (array_slice($todays_followups,0,6) as $f): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
          <div>
            <div class="fw-semibold small"><?= esc($f['name']) ?></div>
            <div class="text-muted" style="font-size:12px"><?= esc($f['mobile']) ?> &bull; <?= ucfirst($f['source']) ?></div>
          </div>
          <div class="d-flex gap-1">
            <a href="tel:<?= $f['mobile'] ?>" class="btn btn-xs btn-outline-success"><i class="bi bi-telephone"></i></a>
            <a href="<?= base_url('admin/leads/'.$f['id']) ?>" class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Upcoming Renewals -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-event me-2 text-danger"></i>Upcoming Renewals (30 days)</h6>
        <span class="badge bg-danger"><?= count($upcoming_renewals) ?></span>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($upcoming_renewals)): ?>
        <div class="list-group-item text-center text-muted py-4 small">No renewals due soon</div>
        <?php else: ?>
        <?php foreach (array_slice($upcoming_renewals,0,7) as $r): ?>
        <?php $days = daysUntil($r['expiry_date']); ?>
        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
          <div>
            <div class="fw-semibold small"><?= esc($r['domain_name'] ?? $r['provider']) ?></div>
            <div class="text-muted" style="font-size:12px"><?= esc($r['client_name'] ?? '') ?></div>
          </div>
          <span class="badge bg-<?= $days <= 7 ? 'danger' : ($days <= 15 ? 'warning text-dark' : 'secondary') ?>"><?= $days ?>d left</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Payments -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-cash me-2 text-success"></i>Recent Payments</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Client</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($recent_payments as $p): ?>
          <tr>
            <td class="fw-semibold"><?= esc($p['client_name']) ?></td>
            <td class="text-success fw-bold">₹<?= number_format($p['amount'],0) ?></td>
            <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_',' ',$p['method'])) ?></span></td>
            <td class="text-muted"><?= date('d M',strtotime($p['payment_date'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent_payments)): ?><tr><td colspan="4" class="text-center text-muted py-3">No payments yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Leads -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-person-plus me-2 text-primary"></i>Recent Leads</h6>
        <a href="<?= base_url('admin/leads/create') ?>" class="btn btn-xs btn-outline-primary">+ Add</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Name</th><th>Source</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($recent_leads as $l): ?>
          <tr>
            <td><div class="fw-semibold"><?= esc($l['name']) ?></div><div class="text-muted" style="font-size:11px"><?= esc($l['mobile']) ?></div></td>
            <td><span class="badge bg-info text-dark"><?= ucfirst(str_replace('_',' ',$l['source'])) ?></span></td>
            <td><span class="badge bg-<?= leadStatusColor($l['status']) ?>"><?= ucfirst(str_replace('_',' ',$l['status'])) ?></span></td>
            <td><a href="<?= base_url('admin/leads/'.$l['id']) ?>" class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent_leads)): ?><tr><td colspan="4" class="text-center text-muted py-3">No leads yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const revenueData = <?= json_encode(array_values($monthly_revenue_chart)) ?>;
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    datasets: [{ label:'Revenue (₹)', data: revenueData, backgroundColor:'rgba(13,110,253,.7)', borderRadius:4 }]
  },
  options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{callback:v=>'₹'+(v/1000).toFixed(0)+'K'}}}}
});

const leadData = <?= json_encode($lead_conversion_chart) ?>;
new Chart(document.getElementById('leadChart'), {
  type: 'doughnut',
  data: { labels: Object.keys(leadData).map(k=>k.replace(/_/g,' ')),
    datasets: [{ data: Object.values(leadData), backgroundColor:['#0d6efd','#198754','#ffc107','#17a2b8','#dc3545','#6f42c1','#fd7e14'] }]
  },
  options: { responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:10,font:{size:11}}}} }
});
</script>
<?= $this->endSection() ?>
