<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-secondary"><?= $pending ?></div>
      <div class="text-muted small">Pending</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-primary"><?= $active ?></div>
      <div class="text-muted small">In Development</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-warning"><?= $testing ?></div>
      <div class="text-muted small">Testing</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-success"><?= $completed ?></div>
      <div class="text-muted small">Completed</div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
    <button class="btn btn-sm btn-outline-secondary filter-btn" data-status="pending">Pending</button>
    <button class="btn btn-sm btn-outline-primary filter-btn" data-status="development">Development</button>
    <button class="btn btn-sm btn-outline-info filter-btn" data-status="testing">Testing</button>
    <button class="btn btn-sm btn-outline-warning filter-btn" data-status="revision">Revision</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-status="completed">Completed</button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-status="on_hold">On Hold</button>
  </div>
  <a href="<?= base_url('admin/projects/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>New Project
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="projectsTable" class="table table-hover mb-0 w-100">
      <thead class="table-light">
        <tr>
          <th>Project #</th>
          <th>Name</th>
          <th>Client</th>
          <th>Type</th>
          <th>Budget</th>
          <th>Progress</th>
          <th>Status</th>
          <th>Deadline</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
let currentStatus = '';
const projectsTable = $('#projectsTable').DataTable({
  processing: true, serverSide: true,
  ajax: { url: '<?= base_url('admin/projects/datatable') ?>', data: d => { d.status = currentStatus; } },
  columns: [
    { data: 'project_number', width: '110px', render: (d,t,r) => `<a href="<?= base_url('admin/projects/') ?>${r.id}" class="text-decoration-none fw-semibold small">${d}</a>` },
    { data: 'name', render: (d,t,r) => `<div><a href="<?= base_url('admin/projects/') ?>${r.id}" class="fw-semibold text-decoration-none">${d}</a><div class="text-muted" style="font-size:11px">${r.type ? r.type.replace(/_/g,' ') : ''}</div></div>` },
    { data: 'client_name', render: d => `<span class="small">${d||'—'}</span>` },
    { data: 'type', render: d => `<span class="badge bg-light text-dark border small">${(d||'').replace(/_/g,' ')}</span>` },
    { data: 'budget', render: d => d ? '₹'+parseFloat(d).toLocaleString('en-IN',{maximumFractionDigits:0}) : '—' },
    { data: 'progress', render: d => {
      const p = d || 0;
      const c = p >= 100 ? 'success' : p >= 60 ? 'info' : p >= 30 ? 'warning' : 'danger';
      return `<div style="min-width:90px"><div class="progress" style="height:6px"><div class="progress-bar bg-${c}" style="width:${p}%"></div></div><div class="text-muted" style="font-size:10px;margin-top:2px">${p}%</div></div>`;
    }},
    { data: 'status', render: d => {
      const m = {pending:'secondary',development:'primary',testing:'info',revision:'warning',completed:'success',on_hold:'danger',cancelled:'dark'};
      return `<span class="badge bg-${m[d]||'secondary'}">${(d||'').replace(/_/g,' ')}</span>`;
    }},
    { data: 'end_date', render: d => {
      if (!d || d === '0000-00-00') return '—';
      const dt = new Date(d); const today = new Date();
      const overdue = dt < today;
      return `<span class="${overdue?'text-danger fw-semibold':''}">${dt.toLocaleDateString('en-IN')}</span>`;
    }},
    { data: null, orderable: false, width: '110px', render: (d,t,r) => `
      <div class="d-flex gap-1">
        <a href="<?= base_url('admin/projects/') ?>${r.id}" class="btn btn-xs btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
        <a href="<?= base_url('admin/projects/edit/') ?>${r.id}" class="btn btn-xs btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
        <button class="btn btn-xs btn-outline-danger btn-del-project"
          data-id="${r.id}"
          data-confirm-title="Delete Project?"
          data-confirm="Delete project '${r.name}'? This cannot be undone."
          data-confirm-yes="Yes, Delete"
          title="Delete"><i class="bi bi-trash"></i></button>
      </div>`
    },
  ],
  order: [[7,'desc']], pageLength: 25,
  language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div>' },
});

$('.filter-btn').on('click', function() {
  currentStatus = $(this).data('status');
  $('.filter-btn').removeClass('active'); $(this).addClass('active');
  projectsTable.ajax.reload();
});

let delId = null;
$(document).on('click', '.btn-del-project', function() {
  delId = $(this).data('id');
  $('#ngConfirmTitle').text($(this).data('confirm-title'));
  $('#ngConfirmMessage').text($(this).data('confirm'));
  $('#ngConfirmYes').text($(this).data('confirm-yes'));
  bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
});
$('#ngConfirmYes').off('click').on('click', function() {
  if (!delId) return;
  bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
  showLoader('Deleting project...');
  $.post(`<?= base_url('admin/projects/delete/') ?>${delId}`, {csrf_test_name: CSRF_TOKEN}, res => {
    hideLoader();
    showToast(res.message, res.status);
    if (res.status === 'success') projectsTable.ajax.reload(null, false);
  }, 'json').fail(() => { hideLoader(); showToast('Server error', 'error'); });
  delId = null;
});
</script>
<?= $this->endSection() ?>
