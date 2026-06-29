<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
    <button class="btn btn-sm btn-outline-secondary filter-btn" data-status="draft">Draft</button>
    <button class="btn btn-sm btn-outline-info filter-btn" data-status="sent">Sent</button>
    <button class="btn btn-sm btn-outline-warning filter-btn" data-status="partial">Partial</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-status="paid">Paid</button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-status="overdue">Overdue</button>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/reports/export/invoices/excel') ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Export</a>
    <a href="<?= base_url('admin/invoices/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Invoice</a>
  </div>
</div>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="invoicesTable" class="table table-hover mb-0 w-100">
      <thead class="table-light">
        <tr><th>Invoice #</th><th>Client</th><th>For</th><th>Date</th><th>Due Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let currentStatus = '';
const invoicesTable = $('#invoicesTable').DataTable({
  processing: true, serverSide: true,
  ajax: { url: '<?= base_url('admin/invoices/datatable') ?>', data: d => { d.status = currentStatus; } },
  columns: [
    { data: 'invoice_number', render: (d,t,r) => `<a href="<?= base_url('admin/invoices/') ?>${r.id}" class="fw-semibold text-decoration-none">${d}</a>` },
    { data: 'client_name' },
    { data: null, render: (d,t,r) => {
        if (r.milestone_id) return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-flag me-1"></i>${r.milestone_title||'Milestone'}</span>`;
        if (r.domain_id) return `<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-globe me-1"></i>${r.domain_name||'Domain'}</span>`;
        if (r.hosting_id) return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-server me-1"></i>${r.hosting_provider||'Hosting'}</span>`;
        return '<span class="text-muted small">—</span>';
      } },
    { data: 'invoice_date', render: d => new Date(d).toLocaleDateString('en-IN') },
    { data: 'due_date', render: d => new Date(d).toLocaleDateString('en-IN') },
    { data: 'total', render: d => '₹'+parseFloat(d).toLocaleString('en-IN',{minimumFractionDigits:2}) },
    { data: 'paid_amount', render: d => `<span class="text-success">₹${parseFloat(d).toLocaleString('en-IN',{minimumFractionDigits:2})}</span>` },
    { data: 'balance_due', render: d => d > 0 ? `<span class="text-danger fw-semibold">₹${parseFloat(d).toLocaleString('en-IN',{minimumFractionDigits:2})}</span>` : '<span class="text-success">₹0.00</span>' },
    { data: 'status', render: d => { const m={draft:'secondary',sent:'info',paid:'success',partial:'warning',overdue:'danger',cancelled:'dark'}; return `<span class="badge bg-${m[d]||'secondary'}">${d}</span>`; } },
    { data: null, orderable: false, render: (d,t,r) => `<div class="d-flex gap-1"><a href="<?= base_url('admin/invoices/') ?>${r.id}" class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i></a><a href="<?= base_url('admin/invoices/pdf/') ?>${r.id}" class="btn btn-xs btn-outline-secondary" target="_blank"><i class="bi bi-file-pdf"></i></a><button class="btn btn-xs btn-outline-success btn-email-inv" data-id="${r.id}"><i class="bi bi-envelope"></i></button><button class="btn btn-xs btn-outline-info btn-wa-inv" data-id="${r.id}"><i class="bi bi-whatsapp"></i></button></div>` },
  ],
  order: [[2,'desc']], pageLength: 25,
});
$('.filter-btn').on('click', function() { currentStatus=$(this).data('status'); $('.filter-btn').removeClass('active'); $(this).addClass('active'); invoicesTable.ajax.reload(); });
$(document).on('click', '.btn-email-inv', function() {
  const id = $(this).data('id');
  if (!confirm('Send invoice by email?')) return;
  $.post(`<?= base_url('admin/invoices/send-email/') ?>${id}`,{csrf_test_name:CSRF_TOKEN}, res => showToast(res.message, res.status));
});
$(document).on('click', '.btn-wa-inv', function() {
  const id = $(this).data('id');
  if (!confirm('Send invoice via WhatsApp?')) return;
  $.post(`<?= base_url('admin/invoices/send-whatsapp/') ?>${id}`,{csrf_test_name:CSRF_TOKEN}, res => showToast(res.message, res.status));
});
</script>
<?= $this->endSection() ?>
