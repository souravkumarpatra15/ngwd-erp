<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group flex-wrap">
    <button class="btn btn-sm btn-outline-secondary filter-cat active" data-cat="">All</button>
    <?php
    // Exact enum values from DB
    $cats = ['proposal','agreement','invoice','contract','screenshot','client_file','project_file','other'];
    foreach ($cats as $cat): ?>
    <button class="btn btn-sm btn-outline-secondary filter-cat" data-cat="<?= $cat ?>">
      <?= ucwords(str_replace('_',' ',$cat)) ?>
    </button>
    <?php endforeach; ?>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
    <i class="bi bi-upload me-1"></i>Upload Document
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="docsTable" class="table table-hover align-middle mb-0 w-100">
      <thead class="table-light">
        <tr><th>Title / File</th><th>Category</th><th>Client</th><th>Project</th><th>Size</th><th>Uploaded</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($documents as $doc):
          $ext  = strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION));
          $icon = match($ext) {
            'pdf'            => 'bi-file-earmark-pdf text-danger',
            'doc', 'docx'   => 'bi-file-earmark-word text-primary',
            'xls','xlsx','csv' => 'bi-file-earmark-excel text-success',
            'png','jpg','jpeg','gif','webp' => 'bi-file-earmark-image text-info',
            'zip','rar'      => 'bi-file-earmark-zip text-warning',
            default          => 'bi-file-earmark text-muted',
          };
        ?>
        <tr data-cat="<?= esc($doc['category'] ?? '') ?>">
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi <?= $icon ?> fs-5"></i>
              <div>
                <div class="fw-semibold small"><?= esc($doc['title'] ?: $doc['file_name']) ?></div>
                <div class="text-muted" style="font-size:11px"><?= esc($doc['file_name']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$doc['category'] ?? 'other')) ?></span></td>
          <td class="small text-muted"><?= esc($doc['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($doc['project_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= $doc['file_size'] ? round($doc['file_size']/1024, 1).' KB' : '—' ?></td>
          <td class="small text-muted"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= base_url('admin/documents/download/'.$doc['id']) ?>" class="btn btn-xs btn-outline-secondary" title="Download"><i class="bi bi-download"></i></a>
              <button class="btn btn-xs btn-outline-danger btn-del-doc"
                data-id="<?= $doc['id'] ?>"
                data-confirm-title="Delete Document?"
                data-confirm="Delete '<?= esc($doc['title'] ?: $doc['file_name']) ?>'? The file will be removed from the server."
                data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-semibold"><i class="bi bi-upload me-2"></i>Upload Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= base_url('admin/documents/upload') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body row g-3">
          <div class="col-12">
            <label class="form-label small fw-semibold">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Document title (optional)">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Category</label>
            <select name="category" class="form-select">
              <?php foreach ($cats as $cat): ?>
              <option value="<?= $cat ?>"><?= ucwords(str_replace('_',' ',$cat)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Client</label>
            <select name="client_id" id="docClientSel" class="form-select select2">
              <option value="">— None —</option>
              <?php foreach ($clients as $c): ?>
              <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Project <span class="text-muted">(optional)</span></label>
            <select name="project_id" id="docProjectSel" class="form-select">
              <option value="">— Select client first —</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">File <span class="text-danger">*</span></label>
            <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.xlsx,.xls,.csv,.txt,.zip">
            <div class="form-text">PDF, DOC, DOCX, PNG, JPG, XLSX, CSV, TXT, ZIP · Max 10MB</div>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;

const dt = $('#docsTable').DataTable({
  pageLength: 25,
  order: [
    [5, 'desc']
  ],
  language: {
    emptyTable: "No documents yet. Upload one above."
  }
});

// Category filter
$('.filter-cat').on('click', function() {
  $('.filter-cat').removeClass('active'); $(this).addClass('active');
  const cat = $(this).data('cat');
  // Filter on the data-cat attribute column (index 1)
  dt.column(1).search(cat ? $(this).text().trim() : '', false, false).draw();
});

// Dynamic project dropdown in upload modal
$('#docClientSel').on('change', function() {
  const cid = $(this).val();
  const sel = $('#docProjectSel');
  if (!cid) { sel.html('<option value="">— Select client first —</option>'); return; }
  $.get(`${BASE}admin/ajax/projects/${cid}`, data => {
    let opts = '<option value="">— No Project —</option>';
    data.forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
    sel.html(opts);
  });
});

// Select2 in modal
$('#uploadDocModal').on('shown.bs.modal', function() {
  if (typeof $.fn.select2 !== 'undefined') {
    $('#docClientSel').select2({ theme:'bootstrap-5', width:'100%', dropdownParent: $('#uploadDocModal') });
  }
});

// Delete
let delId = null;
$(document).on('click', '.btn-del-doc', function() {
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
  $.post(`${BASE}admin/documents/delete/${delId}`, {csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') location.reload();
  }, 'json');
  delId = null;
});
</script>
<?= $this->endSection() ?>
