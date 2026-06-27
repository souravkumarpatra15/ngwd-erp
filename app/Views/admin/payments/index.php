<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/reports/export/revenue/excel') ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Export</a>
  </div>
  <a href="<?= base_url('admin/payments/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Record Payment</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="paymentsTable" class="table table-hover mb-0 w-100">
      <thead class="table-light">
        <tr><th>Payment #</th><th>Client</th><th>Project</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
$('#paymentsTable').DataTable({
  processing: true, serverSide: true,
  ajax: `${BASE}admin/payments/datatable`,
  columns: [
    { data: 'payment_number', render: (d,t,r) => `<a href="${BASE}admin/payments/${r.id}" class="fw-semibold text-decoration-none small">${d}</a>` },
    { data: 'client_name', render: d => `<span class="small">${d||'—'}</span>` },
    { data: 'project_name', render: d => `<span class="small text-muted">${d||'—'}</span>` },
    { data: 'amount', render: d => `<span class="fw-bold text-success">₹${parseFloat(d).toLocaleString('en-IN',{minimumFractionDigits:2})}</span>` },
    { data: 'method', render: d => `<span class="badge bg-light text-dark border">${(d||'').replace(/_/g,' ')}</span>` },
    { data: 'payment_date', render: d => d ? new Date(d).toLocaleDateString('en-IN') : '—' },
    { data: 'status', render: d => {
      const m={completed:'success',pending:'warning',failed:'danger',refunded:'info'};
      return `<span class="badge bg-${m[d]||'secondary'}">${d}</span>`;
    }},
    { data: null, orderable: false, width: '100px', render: (d,t,r) => `
      <div class="d-flex gap-1">
        <a href="${BASE}admin/payments/${r.id}" class="btn btn-xs btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
        <a href="${BASE}admin/payments/receipt/${r.id}" class="btn btn-xs btn-outline-secondary" target="_blank" title="Receipt"><i class="bi bi-receipt"></i></a>
      </div>` },
  ],
  order: [[5,'desc']], pageLength: 25,
  language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div>' },
});
</script>
<?= $this->endSection() ?>
