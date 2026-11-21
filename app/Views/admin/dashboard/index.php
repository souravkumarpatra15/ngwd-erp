<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
<?php
$cards = [
  ['label'=>'Total Leads','value'=>$total_leads,'icon'=>'graph-up-arrow','color'=>'warning','link'=>'admin/leads'],
  ['label'=>'Total Clients','value'=>$total_clients,'icon'=>'people','color'=>'success','link'=>'admin/clients'],
  ['label'=>'Active Projects','value'=>$active_projects,'icon'=>'folder2-open','color'=>'warning','link'=>'admin/projects'],
  ['label'=>'Completed Projects','value'=>$completed_projects,'icon'=>'check2-circle','color'=>'info','link'=>'admin/projects'],
];
if (!empty($canViewFinancials)) {
  $cards[] = ['label'=>'Monthly Revenue','value'=>renderCurrencyBreakdown($monthly_revenue_by_currency ?? []),'icon'=>'cash-stack','color'=>'success','link'=>'admin/reports/revenue'];
  $cards[] = ['label'=>'Pending Payments','value'=>renderCurrencyBreakdown($pending_payments_by_currency ?? []),'icon'=>'exclamation-circle','color'=>'danger','link'=>'admin/invoices'];
}
$cards[] = ['label'=>'Domain Renewals','value'=>$domain_renewals,'icon'=>'globe','color'=>'secondary','link'=>'admin/domains'];
$cards[] = ['label'=>'Hosting Renewals','value'=>$hosting_renewals,'icon'=>'server','color'=>'dark','link'=>'admin/hostings'];
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

<!-- My Work: personal task breakdown for the logged-in internal user -->
<?php if (!empty($my_work) && (int) ($my_work['total_open'] ?? 0) > 0): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <div><h6 class="mb-0 fw-semibold"><i class="bi bi-person-check me-2 text-primary"></i>My Work</h6><div class="text-muted small mt-1">Tasks assigned to you across all projects</div></div>
    <a href="<?= base_url('admin/tasks?assigned_to=' . session()->get('user_id')) ?>" class="btn btn-sm btn-outline-primary">View All Mine</a>
  </div>
  <div class="card-body">
    <div class="row g-3 mb-3">
      <?php $mw = [['label' => 'Due Today', 'key' => 'today', 'color' => 'warning'], ['label' => 'Overdue', 'key' => 'overdue', 'color' => 'danger'], ['label' => 'Upcoming (7d)', 'key' => 'upcoming', 'color' => 'info'], ['label' => 'Blocked', 'key' => 'blocked', 'color' => 'dark'], ['label' => 'In Review', 'key' => 'review', 'color' => 'primary']]; ?>
      <?php foreach ($mw as $m): ?>
        <div class="col-6 col-md">
          <div class="border rounded-3 p-2 text-center h-100">
            <div class="fs-5 fw-bold text-<?= $m['color'] ?>"><?= count($my_work[$m['key']] ?? []) ?></div>
            <div class="text-muted" style="font-size:11px"><?= $m['label'] ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($my_work['overdue']) || !empty($my_work['today'])): ?>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead><tr><th>Task</th><th>Project</th><th>Due</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach (array_slice(array_merge($my_work['overdue'], $my_work['today']), 0, 6) as $t): ?>
              <tr>
                <td class="small fw-semibold"><?= esc($t['title']) ?></td>
                <td class="small text-muted"><?= esc($t['project_name'] ?? '—') ?></td>
                <td class="small <?= (strtotime($t['due_date']) < strtotime(date('Y-m-d'))) ? 'text-danger fw-semibold' : '' ?>"><?= !empty($t['due_date']) ? date('d M', strtotime($t['due_date'])) : '—' ?></td>
                <td><span class="badge bg-secondary"><?= esc(ucwords(str_replace('_', ' ', $t['status']))) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- Charts Row -->
<div class="row g-3 mb-4">
<?php if (!empty($canViewFinancials)): ?>
  <div class="col-xl-8 col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Revenue — <?= date('Y') ?></h6>
          <div class="text-muted small mt-1">Payments received per month</div>
        </div>
      </div>
      <div class="card-body pt-0">
        <div class="ng-chart-box" style="height:260px"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
  </div>
  <div class="col-xl-4 col-lg-5">
<?php else: ?>
  <div class="col-12">
<?php endif; ?>
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-start">
        <div>
          <h6 class="mb-0 fw-semibold"><i class="bi bi-pie-chart me-2 text-success"></i>Lead Status</h6>
          <div class="text-muted small mt-1">Pipeline distribution</div>
        </div>
        <span class="badge rounded-pill text-bg-light border"><?= (int) array_sum($lead_conversion_chart ?? []) ?> total</span>
      </div>
      <div class="card-body pt-1">
        <?php if ((int) array_sum($lead_conversion_chart ?? []) === 0): ?>
          <div class="text-center py-5">
            <div class="empty-activity-icon mb-3"><i class="bi bi-pie-chart"></i></div>
            <h6 class="fw-semibold mb-1">No leads yet</h6>
            <p class="text-muted small mb-3">Lead status will appear here once the first lead is added.</p>
            <a href="<?= base_url('admin/leads/create') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Lead</a>
          </div>
        <?php else: ?>
          <div class="ng-chart-box ng-doughnut-box" style="height:220px">
            <canvas id="leadChart"></canvas>
            <div class="ng-doughnut-center" aria-hidden="true">
              <span id="leadCenterTotal"><?= (int) array_sum($lead_conversion_chart ?? []) ?></span>
              <small>leads</small>
            </div>
          </div>
          <div id="leadLegend" class="ng-chart-legend"></div>
        <?php endif; ?>
      </div>
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
  <?php if (!empty($canViewFinancials)): ?>
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
  <?php endif; ?>

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
          <?php foreach (($recent_leads ?? []) as $l): ?>
          <tr>
            <td><div class="fw-semibold"><?= esc($l['name']) ?></div><div class="text-muted" style="font-size:11px"><?= esc($l['mobile']) ?></div></td>
            <td><span class="badge bg-info text-dark"><?= ucfirst(str_replace('_',' ',$l['source'])) ?></span></td>
            <td><span class="badge bg-<?= leadStatusColor($l['status']) ?>"><?= ucfirst(str_replace('_',' ',$l['status'])) ?></span></td>
            <td><a href="<?= base_url('admin/leads/'.$l['id']) ?>" class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recent_leads ?? [])): ?><tr><td colspan="4" class="text-center text-muted py-3">No leads yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
<?php if (!empty($canViewFinancials)): ?>
(function () {
  const el = document.getElementById('revenueChart');
  if (!el) return;
  const revenueData = <?= json_encode(array_values($monthly_revenue_chart ?? array_fill(0, 12, 0))) ?>;
  const compact = v => {
    const n = Number(v) || 0;
    if (n >= 10000000) return '₹' + (n / 10000000).toFixed(n % 10000000 ? 1 : 0) + 'Cr';
    if (n >= 100000) return '₹' + (n / 100000).toFixed(n % 100000 ? 1 : 0) + 'L';
    if (n >= 1000) return '₹' + Math.round(n / 1000) + 'K';
    return '₹' + n;
  };
  new Chart(el, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Revenue',
        data: revenueData,
        backgroundColor: 'rgba(79,70,229,.75)',
        hoverBackgroundColor: 'rgba(79,70,229,1)',
        borderRadius: 6,
        maxBarThickness: 34
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 350 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(17,24,39,.92)',
          padding: 10,
          cornerRadius: 8,
          callbacks: { label: ctx => ' ₹' + Number(ctx.parsed.y || 0).toLocaleString('en-IN') }
        }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(17,24,39,.06)' }, border: { display: false }, ticks: { callback: compact, font: { size: 11 } } },
        x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } }
      }
    }
  });
})();
<?php endif; ?>

(function () {
  const el = document.getElementById('leadChart');
  if (!el) return;

  const counts = <?= json_encode($lead_conversion_chart ?? []) ?>;
  const ORDER = ['new', 'contacted', 'follow_up', 'proposal_sent', 'negotiation', 'converted', 'lost'];
  const LABELS = {
    new: 'New', contacted: 'Contacted', follow_up: 'Follow-up',
    proposal_sent: 'Proposal sent', negotiation: 'Negotiation',
    converted: 'Converted', lost: 'Lost'
  };
  const COLORS = {
    new: '#6366f1', contacted: '#0ea5e9', follow_up: '#f59e0b',
    proposal_sent: '#8b5cf6', negotiation: '#14b8a6',
    converted: '#22c55e', lost: '#ef4444'
  };

  const keys = Object.keys(counts)
    .filter(k => Number(counts[k]) > 0)
    .sort((a, b) => ORDER.indexOf(a) - ORDER.indexOf(b));
  if (!keys.length) return;

  const labels = keys.map(k => LABELS[k] || k.replace(/_/g, ' '));
  const values = keys.map(k => Number(counts[k]));
  const colors = keys.map(k => COLORS[k] || '#94a3b8');
  const total = values.reduce((a, b) => a + b, 0);
  const pct = v => (total ? Math.round((v / total) * 100) : 0) + '%';

  document.getElementById('leadCenterTotal')?.replaceChildren(document.createTextNode(String(total)));

  const legend = document.getElementById('leadLegend');
  if (legend) {
    legend.innerHTML = keys.map((k, i) =>
      '<div class="ng-legend-item" title="' + labels[i] + ': ' + values[i] + '">' +
        '<span class="ng-legend-dot" style="background:' + colors[i] + '"></span>' +
        '<span class="ng-legend-label">' + labels[i] + '</span>' +
        '<span class="ng-legend-value">' + values[i] + '<small>' + pct(values[i]) + '</small></span>' +
      '</div>'
    ).join('');
  }

  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 2,
        hoverOffset: 6,
        spacing: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      animation: { duration: 350 },
      layout: { padding: 4 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(17,24,39,.92)',
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct(ctx.parsed) + ')'
          }
        }
      }
    }
  });
})();
</script>
<?= $this->endSection() ?>
