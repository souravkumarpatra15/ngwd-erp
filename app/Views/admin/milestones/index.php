<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= base_url('admin/projects') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-folder2-open me-1"></i>View Projects</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Milestone</th>
          <th>Project</th>
          <th>Client</th>
          <?php if (!empty($canViewFinancials)): ?><th>Amount</th><?php endif; ?>
          <th>Due Date</th>
          <th>Due Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($milestones)): ?>
        <tr><td colspan="<?= !empty($canViewFinancials) ? 8 : 7 ?>" class="text-center text-muted py-5"><i class="bi bi-flag fs-3 d-block mb-2 opacity-25"></i>No milestones found</td></tr>
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
          <?php if (!empty($canViewFinancials)): ?><td class="fw-semibold text-primary"><?= currencySymbol($ms['currency'] ?? 'INR') ?><?= number_format($ms['amount'] ?? 0, 0) ?></td><?php endif; ?>
          <td class="<?= $overdue ? 'text-danger fw-semibold' : '' ?> small">
            <?= $ms['due_date'] && $ms['due_date'] !== '0000-00-00' ? date('d M Y', strtotime($ms['due_date'])) : '—' ?>
            <?php if ($overdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
          </td>
          <td class="small text-muted"><?= !empty($ms['due_time']) ? date('h:i A', strtotime($ms['due_time'])) : '—' ?></td>
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
              <button class="btn btn-xs btn-outline-info btn-ms-notes" data-id="<?= $ms['id'] ?>" data-title="<?= esc($ms['title']) ?>" title="Notes / Q&A"><i class="bi bi-chat-left-text"></i></button>
              <button class="btn btn-xs btn-outline-warning btn-edit-ms" data-id="<?= $ms['id'] ?>" data-title="<?= esc($ms['title'], 'attr') ?>" data-description="<?= esc($ms['description'] ?? '', 'attr') ?>" data-amount="<?= esc($ms['amount'] ?? 0, 'attr') ?>" data-currency="<?= esc($ms['currency'] ?? 'INR', 'attr') ?>" data-due-date="<?= esc($ms['due_date'] ?? '', 'attr') ?>" data-due-time="<?= esc(substr((string)($ms['due_time'] ?? ''), 0, 5), 'attr') ?>" title="Edit Milestone"><i class="bi bi-pencil"></i></button>
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

<!-- Edit Milestone Modal -->
<div class="modal fade" id="editMilestoneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0"><h5 class="modal-title fw-semibold">Edit Milestone</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form id="editMsForm">
        <?= csrf_field() ?>
        <input type="hidden" name="ms_id" id="editMsId" value="">
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label small fw-semibold">Title *</label><input name="title" id="editMsTitle" class="form-control" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Description</label><textarea name="description" id="editMsDescription" class="form-control" rows="2"></textarea></div>
          <?php if (!empty($canViewFinancials)): ?>
          <div class="col-md-7"><label class="form-label small fw-semibold">Amount</label><input type="number" step="0.01" min="0" name="amount" id="editMsAmount" class="form-control"></div>
          <div class="col-md-5"><label class="form-label small fw-semibold">Currency</label><select name="currency" id="editMsCurrency" class="form-select"><?php foreach (['INR' => '₹ INR', 'USD' => '$ USD', 'EUR' => '€ EUR', 'GBP' => '£ GBP', 'AED' => 'د.إ AED', 'CAD' => 'C$ CAD', 'AUD' => 'A$ AUD', 'SGD' => 'S$ SGD'] as $code => $label): ?><option value="<?= $code ?>"><?= $label ?></option><?php endforeach; ?></select></div>
          <?php endif; ?>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Date</label><input type="date" name="due_date" id="editMsDueDate" class="form-control"></div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Time</label><input type="time" name="due_time" id="editMsDueTime" class="form-control"></div>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?= view('admin/milestones/partials/notes_modal') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;

$(document).on('click', '.btn-edit-ms', function() {
  const d = $(this).data();
  $('#editMsId').val(d.id);
  $('#editMsTitle').val(d.title || '');
  $('#editMsDescription').val(d.description || '');
  if ($('#editMsAmount').length) $('#editMsAmount').val(d.amount || 0);
  if ($('#editMsCurrency').length) $('#editMsCurrency').val(d.currency || 'INR');
  $('#editMsDueDate').val(d.dueDate || '');
  $('#editMsDueTime').val(d.dueTime || '');
  bootstrap.Modal.getOrCreateInstance(document.getElementById('editMilestoneModal')).show();
});
$('#editMsForm').on('submit', function(e) {
  e.preventDefault();
  const id = $('#editMsId').val();
  showLoader('Saving...');
  $.post(`${BASE}admin/milestones/update/${id}`, $(this).serialize(), res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') { bootstrap.Modal.getInstance(document.getElementById('editMilestoneModal')).hide(); setTimeout(() => location.reload(), 500); }
  }, 'json');
});

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
