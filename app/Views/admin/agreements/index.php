<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
    <button class="btn btn-sm btn-outline-secondary filter-btn" data-status="draft">Draft</button>
    <button class="btn btn-sm btn-outline-info filter-btn" data-status="sent">Sent</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-status="signed">Signed</button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-status="rejected">Rejected</button>
  </div>
  <a href="<?= base_url('admin/agreements/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>New Agreement
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="agreementsTable" class="table table-hover align-middle mb-0 w-100">
      <thead class="table-light">
        <tr>
          <th>Agr #</th>
          <th>Title</th>
          <th>Client</th>
          <th>Project</th>
          <th>Status</th>
          <th>Signed At</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($agreements as $a): ?>
          <?php
          $sc = ['draft' => 'secondary', 'sent' => 'info', 'signed' => 'success', 'rejected' => 'danger'][$a['status']] ?? 'secondary';
          ?>
          <tr data-status="<?= $a['status'] ?>">
            <td><a href="<?= base_url('admin/agreements/' . $a['id']) ?>" class="fw-semibold text-decoration-none small"><?= esc($a['agreement_number']) ?></a></td>
            <td>
              <a href="<?= base_url('admin/agreements/' . $a['id']) ?>" class="text-decoration-none fw-semibold">
                <?= esc($a['title']) ?>
              </a>
            </td>
            <td class="small text-muted"><?= esc($a['client_name'] ?? '—') ?></td>
            <td class="small text-muted"><?= esc($a['project_name'] ?? '—') ?></td>
            <td><span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="small text-muted">
              <?= $a['signed_at'] ? date('d M Y', strtotime($a['signed_at'])) : '—' ?>
            </td>
            <td class="small text-muted"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= base_url('admin/agreements/' . $a['id']) ?>" class="btn btn-xs btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                <a href="<?= base_url('admin/agreements/edit/' . $a['id']) ?>" class="btn btn-xs btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <a href="<?= base_url('admin/agreements/pdf/' . $a['id']) ?>" class="btn btn-xs btn-outline-secondary" target="_blank" title="PDF"><i class="bi bi-file-pdf"></i></a>
                <button class="btn btn-xs btn-outline-primary btn-send-email" data-id="<?= $a['id'] ?>" title="Send Email"><i class="bi bi-envelope"></i></button>
                <button class="btn btn-xs btn-outline-success btn-send-wa" data-id="<?= $a['id'] ?>" title="WhatsApp" style="color:#25D366;border-color:#25D366"><i class="bi bi-whatsapp"></i></button>
                <?php if ($a['status'] !== 'signed'): ?>
                  <button class="btn btn-xs btn-outline-danger btn-del-agr"
                    data-id="<?= $a['id'] ?>"
                    data-confirm-title="Delete Agreement?"
                    data-confirm="Delete agreement '<?= esc($a['agreement_number']) ?>'? This cannot be undone."
                    data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  const BASE = '<?= base_url() ?>';
  const CSRF = CSRF_TOKEN;

  const dt = $('#agreementsTable').DataTable({
    pageLength: 25,
    order: [
      [6, 'desc']
    ]
  });

  $('.filter-btn').on('click', function() {
    const s = $(this).data('status');
    $('.filter-btn').removeClass('active');
    $(this).addClass('active');
    dt.column(4).search(s ? ucfirst(s) : '', false, false).draw();
  });

  function ucfirst(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
  }

  $(document).on('click', '.btn-send-email', function() {
    const id = $(this).data('id');
    showLoader('Sending email...');
    $.post(`${BASE}admin/agreements/send-email/${id}`, {
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      showToast(res.message, res.status);
    }, 'json');
  });

  $(document).on('click', '.btn-send-wa', function() {
    const id = $(this).data('id');
    showLoader('Sending WhatsApp...');
    $.post(`${BASE}admin/agreements/send-whatsapp/${id}`, {
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      showToast(res.message, res.status);
    }, 'json');
  });

  let delId = null;
  $(document).on('click', '.btn-del-agr', function() {
    delId = $(this).data('id');
    $('#ngConfirmTitle').text($(this).data('confirm-title'));
    $('#ngConfirmMessage').text($(this).data('confirm'));
    $('#ngConfirmYes').text($(this).data('confirm-yes'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  });
  $('#ngConfirmYes').off('click').on('click', function() {
    if (!delId) return;
    bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
    showLoader('Deleting...');
    $.post(`${BASE}admin/agreements/delete/${delId}`, {
      csrf_test_name: CSRF
    }, res => {
      hideLoader();
      showToast(res.message, res.status);
      if (res.status === 'success') dt.ajax.reload ? dt.ajax.reload(null, false) : location.reload();
    }, 'json');
    delId = null;
  });
</script>
<?= $this->endSection() ?>