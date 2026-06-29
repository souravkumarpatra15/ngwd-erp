<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= base_url('admin/projects') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-folder2-open me-1"></i>View Projects</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Milestone</th>
          <th>Project</th>
          <th>Client</th>
          <th>Amount</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($milestones)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-flag fs-3 d-block mb-2 opacity-25"></i>No milestones found</td></tr>
        <?php else: ?>
        <?php foreach ($milestones as $ms): ?>
        <?php
          $sc = ['pending'=>'secondary','in_progress'=>'info','completed'=>'success','paid'=>'success'][$ms['status']] ?? 'secondary';
          $overdue = $ms['due_date'] && $ms['due_date'] !== '0000-00-00' && strtotime($ms['due_date']) < time() && $ms['status'] === 'pending';
        ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= esc($ms['title']) ?></div>
            <?php if ($ms['description']): ?><div class="text-muted small"><?= esc(substr($ms['description'],0,60)) ?>...</div><?php endif; ?>
          </td>
          <td><a href="<?= base_url('admin/projects/'.$ms['project_id']) ?>" class="text-decoration-none small"><?= esc($ms['project_name'] ?? '—') ?></a></td>
          <td class="small text-muted"><?= esc($ms['client_name'] ?? '—') ?></td>
          <td class="fw-semibold text-primary">₹<?= number_format($ms['amount'] ?? 0, 0) ?></td>
          <td class="<?= $overdue ? 'text-danger fw-semibold' : '' ?> small">
            <?= $ms['due_date'] && $ms['due_date'] !== '0000-00-00' ? date('d M Y', strtotime($ms['due_date'])) : '—' ?>
            <?php if ($overdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
          </td>
          <td>
            <select class="form-select form-select-sm ms-status-select" data-id="<?= $ms['id'] ?>" style="width:120px">
              <?php foreach (['pending','in_progress','completed','paid'] as $s): ?>
              <option value="<?= $s ?>" <?= $ms['status'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= base_url('admin/projects/'.$ms['project_id']) ?>" class="btn btn-xs btn-outline-primary" title="View Project"><i class="bi bi-folder2-open"></i></a>
              <?php if (!in_array($ms['status'], ['completed','paid'])): ?>
              <button class="btn btn-xs btn-outline-success btn-pay-link-ms" data-id="<?= $ms['id'] ?>" title="Generate Payment Link"><i class="bi bi-credit-card"></i></button>
              <?php endif; ?>
              <button class="btn btn-xs btn-outline-danger btn-del-ms"
                data-id="<?= $ms['id'] ?>"
                data-confirm-title="Delete Milestone?"
                data-confirm="Delete '<?= esc($ms['title']) ?>'?"
                data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Payment link result modal -->
<div class="modal fade" id="msPayLinkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h6 class="modal-title fw-semibold"><i class="bi bi-credit-card me-2 text-success"></i>Payment Link Ready</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="small text-muted mb-2">Share this link with the client so they can pay this milestone directly:</div>
        <div class="input-group">
          <input type="text" class="form-control form-control-sm" id="msPayLinkUrl" readonly>
          <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('msPayLinkUrl').value).then(()=>showToast('Copied!','success'))">Copy</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;

$('.ms-status-select').on('change', function() {
  const id = $(this).data('id'), status = $(this).val();
  $.post(`${BASE}admin/milestones/status/${id}`, {status, csrf_test_name: CSRF}, res => {
    showToast(res.message, res.status);
  }, 'json');
});

$(document).on('click', '.btn-pay-link-ms', function() {
  const id = $(this).data('id');
  showLoader('Creating Razorpay order...');
  $.post(`${BASE}admin/milestones/payment-link/${id}`, {csrf_test_name: CSRF}, res => {
    hideLoader();
    if (res.status === 'success' && res.data && res.data.url) {
      document.getElementById('msPayLinkUrl').value = res.data.url;
      bootstrap.Modal.getOrCreateInstance(document.getElementById('msPayLinkModal')).show();
    } else {
      showToast(res.message, res.status);
    }
  }, 'json');
});

let delId = null;
$(document).on('click', '.btn-del-ms', function() {
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
  $.post(`${BASE}admin/milestones/delete/${delId}`, {csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') location.reload();
  }, 'json');
  delId = null;
});
</script>
<?= $this->endSection() ?>
