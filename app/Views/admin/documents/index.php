<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-cat active" data-cat="">All</button>
    <?php foreach (['proposal','contract','design','source_code','invoice','other'] as $cat): ?>
    <button class="btn btn-sm btn-outline-secondary filter-cat" data-cat="<?= $cat ?>"><?= ucwords(str_replace('_',' ',$cat)) ?></button>
    <?php endforeach; ?>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
    <i class="bi bi-upload me-1"></i>Upload Document
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="docsTable" class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Title / File</th><th>Category</th><th>Client</th><th>Project</th><th>Size</th><th>Uploaded</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($documents as $doc): ?>
        <tr data-cat="<?= esc($doc['category'] ?? '') ?>">
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-<?= in_array(pathinfo($doc['file_name'],PATHINFO_EXTENSION),['pdf']) ? 'pdf text-danger' : (in_array(pathinfo($doc['file_name'],PATHINFO_EXTENSION),['doc','docx']) ? 'word text-primary' : 'text-muted') ?> fs-5"></i>
              <div>
                <div class="fw-semibold small"><?= esc($doc['title'] ?: $doc['file_name']) ?></div>
                <div class="text-muted" style="font-size:11px"><?= esc($doc['file_name']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$doc['category'] ?? 'other')) ?></span></td>
          <td class="small text-muted"><?= esc($doc['client_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= esc($doc['project_name'] ?? '—') ?></td>
          <td class="small text-muted"><?= round(($doc['file_size'] ?? 0)/1024, 1) ?> KB</td>
          <td class="small text-muted"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= base_url('admin/documents/download/'.$doc['id']) ?>" class="btn btn-xs btn-outline-secondary" title="Download"><i class="bi bi-download"></i></a>
              <button class="btn btn-xs btn-outline-danger btn-del-doc"
                data-id="<?= $doc['id'] ?>"
                data-confirm-title="Delete Document?"
                data-confirm="Delete '<?= esc($doc['title'] ?: $doc['file_name']) ?>'? File will be removed."
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
        <h5 class="modal-title fw-semibold">Upload Document</h5>
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
              <?php foreach (['proposal','contract','design','source_code','invoice','other'] as $cat): ?>
              <option value="<?= $cat ?>"><?= ucwords(str_replace('_',' ',$cat)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Client</label>
            <select name="client_id" class="form-select">
              <option value="">None</option>
              <?php foreach ($clients ?? [] as $c): ?>
              <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">File <span class="text-danger">*</span></label>
            <input type="file" name="file" class="form-control" required>
            <div class="form-text">PDF, DOC, DOCX, PNG, JPG, XLSX, CSV, TXT, ZIP · Max 10MB</div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
const dt = $('#docsTable').DataTable({ pageLength: 25, order: [[5,'desc']] });

$('.filter-cat').on('click', function() {
  const cat = $(this).data('cat');
  $('.filter-cat').removeClass('active'); $(this).addClass('active');
  dt.column(1).search(cat ? $(this).text().trim() : '').draw();
});

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
