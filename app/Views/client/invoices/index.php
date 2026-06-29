<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php if (empty($invoices)): ?>
<div class="text-center text-muted py-5">
  <i class="bi bi-receipt fs-2 d-block mb-2 opacity-25"></i>No invoices yet.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Invoice #</th><th>For</th><th>Date</th><th>Due Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $inv): ?>
        <?php $sc = ['draft'=>'secondary','sent'=>'info','partial'=>'warning','paid'=>'success','overdue'=>'danger','cancelled'=>'dark'][$inv['status']] ?? 'secondary'; ?>
        <tr>
          <td><a href="<?= base_url('portal/invoices/'.$inv['id']) ?>" class="fw-semibold text-decoration-none small"><?= esc($inv['invoice_number']) ?></a></td>
          <td class="small">
            <?php if (!empty($inv['milestone_id'])): ?>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-flag me-1"></i>Milestone</span>
              <div class="text-muted" style="font-size:11px"><?= esc($inv['milestone_title'] ?? '') ?></div>
            <?php elseif (!empty($inv['domain_id'])): ?>
              <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-globe me-1"></i>Domain Renewal</span>
              <div class="text-muted" style="font-size:11px"><?= esc($inv['domain_name'] ?? '') ?></div>
            <?php elseif (!empty($inv['hosting_id'])): ?>
              <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-server me-1"></i>Hosting Renewal</span>
              <div class="text-muted" style="font-size:11px"><?= esc($inv['hosting_provider'] ?? '') ?><?= !empty($inv['hosting_package']) ? ' ('.esc($inv['hosting_package']).')' : '' ?></div>
            <?php elseif (!empty($inv['project_name'])): ?>
              <span class="text-muted"><?= esc($inv['project_name']) ?></span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="small"><?= date('d M Y',strtotime($inv['invoice_date'])) ?></td>
          <td class="small <?= strtotime($inv['due_date']) < time() && !in_array($inv['status'],['paid','cancelled']) ? 'text-danger fw-semibold' : '' ?>">
            <?= date('d M Y',strtotime($inv['due_date'])) ?>
          </td>
          <td class="fw-semibold">₹<?= number_format($inv['total'],0) ?></td>
          <td class="text-success small">₹<?= number_format($inv['paid_amount']??0,0) ?></td>
          <td class="<?= ($inv['balance_due']??0) > 0 ? 'text-danger fw-semibold' : 'text-success' ?> small">
            ₹<?= number_format($inv['balance_due']??($inv['total']-($inv['paid_amount']??0)),0) ?>
          </td>
          <td><span class="badge bg-<?= $sc ?>"><?= ucfirst($inv['status']) ?></span></td>
          <td>
            <?php if (!in_array($inv['status'],['paid','cancelled'])): ?>
            <a href="<?= base_url('portal/pay/'.$inv['id']) ?>" class="btn btn-xs btn-success"><i class="bi bi-credit-card me-1"></i>Pay</a>
            <?php endif; ?>
            <a href="<?= base_url('portal/invoices/'.$inv['id']) ?>" class="btn btn-xs btn-outline-secondary ms-1"><i class="bi bi-eye"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
