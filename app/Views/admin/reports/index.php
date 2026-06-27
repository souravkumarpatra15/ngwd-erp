<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-4">
  <?php
  $reports = [
    ['title'=>'Revenue Report','desc'=>'Payments received by month, client, and project.','icon'=>'bi-cash-stack','color'=>'success','url'=>'admin/reports/revenue'],
    ['title'=>'Lead Report','desc'=>'Lead sources, conversion rates, and follow-up stats.','icon'=>'bi-person-plus','color'=>'primary','url'=>'admin/reports/leads'],
    ['title'=>'Project Report','desc'=>'Project status, timelines, and delivery overview.','icon'=>'bi-folder2-open','color'=>'info','url'=>'admin/reports/projects'],
    ['title'=>'Invoice Report','desc'=>'Outstanding, overdue, and paid invoice summary.','icon'=>'bi-receipt','color'=>'warning','url'=>'admin/reports/invoices'],
    ['title'=>'Payment Report','desc'=>'All completed payments with method breakdown.','icon'=>'bi-credit-card','color'=>'success','url'=>'admin/reports/payments'],
    ['title'=>'Domain Report','desc'=>'Domains by expiry status, registrar, and renewal cost.','icon'=>'bi-globe','color'=>'danger','url'=>'admin/reports/domains'],
  ];
  foreach ($reports as $r):
  ?>
  <div class="col-md-4">
    <a href="<?= base_url($r['url']) ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 report-card">
        <div class="card-body d-flex align-items-start gap-3 py-4">
          <div class="rounded-3 p-3 bg-<?= $r['color'] ?> bg-opacity-10">
            <i class="bi <?= $r['icon'] ?> fs-4 text-<?= $r['color'] ?>"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1"><?= $r['title'] ?></h6>
            <p class="text-muted small mb-2"><?= $r['desc'] ?></p>
            <span class="text-<?= $r['color'] ?> small fw-semibold">View Report →</span>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<style>
.report-card { transition: transform .15s, box-shadow .15s; }
.report-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.1) !important; }
</style>

<?= $this->endSection() ?>
