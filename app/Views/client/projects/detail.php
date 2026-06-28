<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php
$statusColors = ['pending'=>'secondary','development'=>'primary','testing'=>'info','revision'=>'warning','completed'=>'success','on_hold'=>'danger','cancelled'=>'dark'];
$sc = $statusColors[$project['status']] ?? 'secondary';
$balance = ($project['budget'] ?? 0) - ($project['advance_paid'] ?? 0);
?>

<!-- Project header -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col-md-8">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-<?= $sc ?> fs-6"><?= ucwords(str_replace('_',' ',$project['status'])) ?></span>
          <span class="text-muted small"><?= esc($project['project_number']) ?></span>
          <span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$project['type'] ?? '')) ?></span>
        </div>
        <h5 class="fw-bold mb-1"><?= esc($project['name']) ?></h5>
        <?php if ($project['description']): ?>
        <p class="text-muted small mb-2"><?= esc($project['description']) ?></p>
        <?php endif; ?>
        <div class="text-muted small">
          <?php if ($project['start_date'] ?? null): ?>
          <i class="bi bi-calendar me-1"></i>
          <?= date('d M Y', strtotime($project['start_date'])) ?>
          <?php if ($project['delivery_date'] ?? null): ?> → <?= date('d M Y', strtotime($project['delivery_date'])) ?><?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="fs-4 fw-bold text-primary mb-1">₹<?= number_format($project['budget'] ?? 0, 0) ?></div>
        <div class="small text-muted">
          Advance paid: <span class="text-success fw-semibold">₹<?= number_format($project['advance_paid'] ?? 0, 0) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Milestones -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-flag me-2 text-warning"></i>Project Milestones</h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($milestones)): ?>
        <div class="text-center text-muted py-5">
          <i class="bi bi-flag fs-2 d-block mb-2 opacity-25"></i>
          <div class="small">No milestones have been added yet.</div>
        </div>
        <?php else: ?>
        <div class="list-group list-group-flush">
          <?php
          $msColors = ['pending'=>'secondary','in_progress'=>'primary','completed'=>'success','paid'=>'success'];
          $msIcons  = ['pending'=>'bi-circle','in_progress'=>'bi-arrow-clockwise','completed'=>'bi-check-circle-fill','paid'=>'bi-check-circle-fill'];
          ?>
          <?php foreach ($milestones as $ms): ?>
          <?php
            $msc  = $msColors[$ms['status']] ?? 'secondary';
            $msi  = $msIcons[$ms['status']]  ?? 'bi-circle';
            $overdue = $ms['due_date'] && $ms['due_date'] !== '0000-00-00'
                    && strtotime($ms['due_date']) < time()
                    && $ms['status'] === 'pending';
          ?>
          <div class="list-group-item px-4 py-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div class="d-flex gap-3 align-items-start">
                <i class="bi <?= $msi ?> text-<?= $msc ?> mt-1 fs-5"></i>
                <div>
                  <div class="fw-semibold small"><?= esc($ms['title']) ?></div>
                  <?php if ($ms['description']): ?>
                  <div class="text-muted" style="font-size:12px"><?= esc($ms['description']) ?></div>
                  <?php endif; ?>
                  <div class="mt-1 d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge bg-<?= $msc ?> badge-sm"><?= ucfirst($ms['status']) ?></span>
                    <?php if ($ms['due_date'] && $ms['due_date'] !== '0000-00-00'): ?>
                    <span class="<?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>" style="font-size:11px">
                      <i class="bi bi-calendar me-1"></i><?= date('d M Y',strtotime($ms['due_date'])) ?>
                      <?php if ($overdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
                    </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="text-end flex-shrink-0">
                <div class="fw-bold text-primary">₹<?= number_format($ms['amount'] ?? 0, 0) ?></div>
                <?php if (in_array($ms['status'], ['pending','in_progress'])): ?>
                <a href="<?= base_url('portal/pay-milestone/'.$ms['id']) ?>" class="btn btn-xs btn-success mt-1">
                  <i class="bi bi-credit-card me-1"></i>Pay
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- Milestone totals -->
        <div class="px-4 py-3 border-top bg-light d-flex justify-content-between small">
          <span class="text-muted">Total Milestones Value</span>
          <span class="fw-bold text-primary">₹<?= number_format(array_sum(array_column($milestones,'amount')), 0) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-lg-5">
    <!-- Quick links -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Quick Links</h6></div>
      <div class="list-group list-group-flush">
        <a href="<?= base_url('portal/invoices') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2">
          <i class="bi bi-receipt text-warning"></i><span class="small">My Invoices</span>
        </a>
        <a href="<?= base_url('portal/payments') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2">
          <i class="bi bi-cash-stack text-success"></i><span class="small">Payment History</span>
        </a>
        <a href="<?= base_url('portal/documents') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2">
          <i class="bi bi-folder text-info"></i><span class="small">Documents</span>
        </a>
        <a href="<?= base_url('portal/tickets/create') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2">
          <i class="bi bi-headset text-primary"></i><span class="small">Raise Support Ticket</span>
        </a>
      </div>
    </div>

    <!-- Project summary -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Project Summary</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small">Project #</td><td class="fw-semibold small"><?= esc($project['project_number']) ?></td></tr>
          <tr><td class="text-muted small">Type</td><td class="small"><?= ucwords(str_replace('_',' ',$project['type']??'')) ?></td></tr>
          <tr><td class="text-muted small">Budget</td><td class="fw-semibold">₹<?= number_format($project['budget']??0,0) ?></td></tr>
          <tr><td class="text-muted small">Advance</td><td class="text-success fw-semibold">₹<?= number_format($project['advance_paid']??0,0) ?></td></tr>
          <tr><td colspan="2"><hr class="my-1"></td></tr>
          <tr><td class="text-muted small">Start</td><td class="small"><?= ($project['start_date']??null) ? date('d M Y',strtotime($project['start_date'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Delivery</td><td class="small"><?= ($project['delivery_date']??null) ? date('d M Y',strtotime($project['delivery_date'])) : 'TBD' ?></td></tr>
          <tr><td class="text-muted small">Milestones</td><td class="small"><?= count($milestones) ?> total</td></tr>
          <tr>
            <td class="text-muted small">Completed</td>
            <td class="small text-success fw-semibold">
              <?= count(array_filter($milestones, fn($m) => in_array($m['status'],['completed','paid']))) ?> / <?= count($milestones) ?>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="text-center mt-4">
  <a href="<?= base_url('portal/projects') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back to Projects
  </a>
</div>

<?= $this->endSection() ?>
