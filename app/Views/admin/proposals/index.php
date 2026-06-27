<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
    <button class="btn btn-sm btn-outline-secondary filter-btn" data-status="draft">Draft</button>
    <button class="btn btn-sm btn-outline-info filter-btn" data-status="sent">Sent</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-status="accepted">Accepted</button>
    <button class="btn btn-sm btn-outline-warning filter-btn" data-status="revision">Revision</button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-status="rejected">Rejected</button>
  </div>
  <a href="<?= base_url('admin/proposals/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Proposal</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="proposalsTable" class="table table-hover mb-0 w-100">
      <thead class="table-light">
        <tr><th>Proposal #</th><th>Title</th><th>Client</th><th>Amount</th><th>Valid Until</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
let currentStatus = '';
const table = $('#proposalsTable').DataTable({
  processing: true, serverSide: true,
  ajax: { url: `${BASE}admin/proposals/datatable`, data: d => { d.status = currentStatus; } },
  columns: [
    { data: 'proposal_number', render: (d,t,r) => `<a href="${BASE}admin/proposals/${r.id}" class="fw-semibold text-decoration-none small">${d}</a>` },
    { data: 'title' },
    { data: 'client_name', render: d => `<span class="small">${d||'—'}</span>` },
    { data: 'total_amount', render: d => `<span class="fw-semibold">₹${parseFloat(d||0).toLocaleString('en-IN',{maximumFractionDigits:0})}</span>` },
    { data: 'valid_until', render: d => d && d !== '0000-00-00' ? new Date(d).toLocaleDateString('en-IN') : '—' },
    { data: 'status', render: d => {
      const m={draft:'secondary',sent:'info',accepted:'success',revision:'warning',rejected:'danger'};
      return `<span class="badge bg-${m[d]||'secondary'}">${d}</span>`;
    }},
    { data: null, orderable: false, width: '160px', render: (d,t,r) => `
      <div class="d-flex gap-1">
        <a href="${BASE}admin/proposals/${r.id}" class="btn btn-xs btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
        <a href="${BASE}admin/proposals/edit/${r.id}" class="btn btn-xs btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
        <a href="${BASE}admin/proposals/pdf/${r.id}" class="btn btn-xs btn-outline-secondary" target="_blank" title="PDF"><i class="bi bi-file-pdf"></i></a>
        <button class="btn btn-xs btn-outline-success btn-email-prop" data-id="${r.id}" title="Email"><i class="bi bi-envelope"></i></button>
        <button class="btn btn-xs btn-outline-success btn-wa-prop" data-id="${r.id}" title="WhatsApp" style="color:#25D366;border-color:#25D366"><i class="bi bi-whatsapp"></i></button>
        <button class="btn btn-xs btn-outline-danger btn-del-prop"
          data-id="${r.id}" data-confirm-title="Delete Proposal?" data-confirm="Delete this proposal?" data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button>
      </div>` },
  ],
  order: [[0,'desc']], pageLength: 25,
  language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div>' },
});
$('.filter-btn').on('click', function() { currentStatus=$(this).data('status'); $('.filter-btn').removeClass('active'); $(this).addClass('active'); table.ajax.reload(); });
$(document).on('click', '.btn-email-prop', function() {
  const id = $(this).data('id');
  showLoader('Sending email...');
  $.post(`${BASE}admin/proposals/send-email/${id}`, {csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message, res.status); }, 'json');
});
$(document).on('click', '.btn-wa-prop', function() {
  const id = $(this).data('id');
  showLoader('Sending WhatsApp...');
  $.post(`${BASE}admin/proposals/send-whatsapp/${id}`, {csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message, res.status); }, 'json');
});
let delId = null;
$(document).on('click', '.btn-del-prop', function() {
  delId=$(this).data('id'); $('#ngConfirmTitle').text($(this).data('confirm-title')); $('#ngConfirmMessage').text($(this).data('confirm')); $('#ngConfirmYes').text($(this).data('confirm-yes'));
  bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
});
$('#ngConfirmYes').off('click').on('click', function() {
  if (!delId) return;
  bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
  showLoader('Deleting...');
  $.post(`${BASE}admin/proposals/delete/${delId}`, {csrf_test_name:CSRF}, res => { hideLoader(); showToast(res.message, res.status); if (res.status==='success') table.ajax.reload(null,false); }, 'json');
  delId=null;
});
</script>
<?= $this->endSection() ?>
