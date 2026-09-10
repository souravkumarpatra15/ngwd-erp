<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$statusColors = ['pending'=>'secondary','development'=>'primary','testing'=>'info','revision'=>'warning','completed'=>'success','on_hold'=>'danger','cancelled'=>'dark'];
$sc = $statusColors[$project['status']] ?? 'secondary';
$balance = ($project['budget'] ?? 0) - ($project['total_paid'] ?? 0);
?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col-md-7">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-<?= $sc ?> fs-6"><?= ucwords(str_replace('_',' ',$project['status'])) ?></span>
          <span class="text-muted small"><?= esc($project['project_number']) ?></span>
          <span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$project['type'])) ?></span>
        </div>
        <h5 class="mb-1 fw-bold"><?= esc($project['name']) ?></h5>
        <div class="text-muted small"><i class="bi bi-person me-1"></i><?= esc($project['client_name'] ?? '—') ?><?php if ($project['start_date'] ?? null): ?><span class="ms-3"><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($project['start_date'])) ?><?php if ($project['delivery_date'] ?? null): ?> → <?= date('d M Y', strtotime($project['delivery_date'])) ?><?php endif; ?></span><?php endif; ?></div>
      </div>
      <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <?php if (!empty($canViewFinancials)): ?>
        <div class="mb-2"><span class="fs-5 fw-bold text-primary"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['budget'] ?? 0, 0) ?></span><span class="text-muted small ms-1">Budget</span></div>
        <div class="small text-muted mb-2">Paid: <span class="text-success fw-semibold"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['total_paid'] ?? 0, 0) ?></span> &nbsp;|&nbsp; Balance: <span class="text-danger fw-semibold"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($balance, 0) ?></span></div>
        <?php endif; ?>
        <div class="d-flex gap-2 justify-content-md-end">
          <a href="<?= base_url('admin/projects/'.$project['id'].'?dashboard=1') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
          <a href="<?= base_url('admin/projects/'.$project['id'].'/members') ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-people me-1"></i>Team</a>
          <a href="<?= base_url('admin/deliverables?project_id='.$project['id']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-box-seam me-1"></i>Deliverables</a>
          <a href="<?= base_url('admin/projects/edit/'.$project['id']) ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
          <div class="dropdown"><button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Change Status</button><ul class="dropdown-menu dropdown-menu-end"><?php foreach (['pending','development','testing','revision','completed','on_hold','cancelled'] as $s): ?><li><a class="dropdown-item <?= $project['status']==$s?'active':'' ?>" href="#" data-status="<?= $s ?>"><?= ucwords(str_replace('_',' ',$s)) ?></a></li><?php endforeach; ?></ul></div>
        </div>
      </div>
    </div>
    <div class="mt-3 pt-3 border-top"><div class="d-flex justify-content-between align-items-center mb-1"><span class="text-muted small">Milestone Progress</span><span class="fw-semibold small"><?= (int) $progress ?>%</span></div><div class="progress" style="height:6px"><?php $pc = $progress >= 100 ? 'success' : ($progress >= 60 ? 'info' : ($progress >= 30 ? 'warning' : 'danger')); ?><div class="progress-bar bg-<?= $pc ?>" style="width:<?= (int) $progress ?>%"></div></div></div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"><h6 class="mb-0 fw-semibold"><i class="bi bi-flag me-2 text-warning"></i>Milestones</h6><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMilestoneModal"><i class="bi bi-plus-lg me-1"></i>Add</button></div><div class="card-body p-0"><?php if (empty($milestones)): ?><div class="text-center text-muted py-4 small"><i class="bi bi-flag fs-4 d-block mb-2 opacity-25"></i>No milestones yet</div><?php else: ?><div class="list-group list-group-flush"><?php foreach ($milestones as $ms): ?><?php $msc = ['pending'=>'secondary','in_progress'=>'primary','completed'=>'success','paid'=>'success'][$ms['status']] ?? 'secondary'; ?><div class="list-group-item px-4 py-3"><div class="d-flex justify-content-between align-items-center"><div><div class="d-flex align-items-center gap-2 mb-1"><span class="badge bg-<?= $msc ?> badge-sm"><?= ucfirst($ms['status']) ?></span><strong class="small"><?= esc($ms['title']) ?></strong></div><?php if ($ms['due_date'] && $ms['due_date'] !== '0000-00-00'): ?><div class="text-muted" style="font-size:11px"><i class="bi bi-calendar me-1"></i><?= date('d M Y',strtotime($ms['due_date'])) ?><?php if (!empty($ms['due_time'])): ?> <i class="bi bi-clock ms-1 me-1"></i><?= date('h:i A',strtotime($ms['due_time'])) ?><?php endif; ?></div><?php endif; ?></div><div class="d-flex align-items-center gap-2"><?php if (!empty($canViewFinancials)): ?><span class="fw-bold text-primary small"><?= currencySymbol($ms['currency'] ?? 'INR') ?><?= number_format($ms['amount'],0) ?></span><?php endif; ?><button class="btn btn-xs btn-outline-secondary btn-ms-add-task" data-id="<?= $ms['id'] ?>" title="Add change/issue task for this milestone"><i class="bi bi-plus-square"></i></button><button class="btn btn-xs btn-outline-info btn-ms-notes" data-id="<?= $ms['id'] ?>" data-title="<?= esc($ms['title']) ?>" title="Notes / Q&A"><i class="bi bi-chat-left-text"></i></button><button class="btn btn-xs btn-outline-warning btn-edit-ms" data-id="<?= $ms['id'] ?>" data-title="<?= esc($ms['title'], 'attr') ?>" data-description="<?= esc($ms['description'] ?? '', 'attr') ?>" data-amount="<?= esc($ms['amount'] ?? 0, 'attr') ?>" data-currency="<?= esc($ms['currency'] ?? 'INR', 'attr') ?>" data-due-date="<?= esc($ms['due_date'] ?? '', 'attr') ?>" data-due-time="<?= esc(substr((string)($ms['due_time'] ?? ''), 0, 5), 'attr') ?>" title="Edit Milestone"><i class="bi bi-pencil"></i></button><button class="btn btn-xs btn-outline-danger btn-del-ms" data-id="<?= $ms['id'] ?>" data-confirm-title="Delete Milestone?" data-confirm="Delete '<?= esc($ms['title']) ?>'?" data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button></div></div></div><?php endforeach; ?></div><?php endif; ?></div></div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"><h6 class="mb-0 fw-semibold"><i class="bi bi-check2-square me-2 text-info"></i>Tasks</h6><div class="d-flex gap-2"><a href="<?= base_url('admin/tasks/kanban?project_id='.$project['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-kanban me-1"></i>Kanban</a><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="bi bi-plus-lg me-1"></i>Add</button></div></div><div class="card-body p-0"><?php if (empty($tasks)): ?><div class="text-center text-muted py-4 small"><i class="bi bi-check2-square fs-4 d-block mb-2 opacity-25"></i>No tasks yet</div><?php else: ?><div class="list-group list-group-flush"><?php foreach ($tasks as $task): ?><?php $tc = ['todo'=>'secondary','in_progress'=>'primary','review'=>'info','completed'=>'success','hold'=>'warning'][$task['status']] ?? 'secondary'; $pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'][$task['priority']] ?? 'secondary'; ?><div class="list-group-item px-4 py-2"><div class="d-flex align-items-center gap-3"><div class="flex-grow-1"><div class="d-flex align-items-center gap-2"><span class="badge bg-<?= $tc ?> badge-sm"><?= ucwords(str_replace('_',' ',$task['status'])) ?></span><span class="badge bg-<?= $pc ?> badge-sm"><?= ucfirst($task['priority']) ?></span><span class="small fw-semibold"><?= esc($task['title']) ?></span><?php if (!empty($task['milestone_title'])): ?><span class="badge bg-light text-dark border" style="font-size:10px"><i class="bi bi-flag me-1"></i><?= esc($task['milestone_title']) ?></span><?php endif; ?></div><?php if ($task['due_date'] && $task['due_date'] !== '0000-00-00'): ?><div class="text-muted" style="font-size:11px;margin-top:2px"><i class="bi bi-calendar me-1"></i><?= date('d M Y',strtotime($task['due_date'])) ?></div><?php endif; ?></div><button class="btn btn-xs btn-outline-danger btn-del-task" data-id="<?= $task['id'] ?>" data-confirm-title="Delete Task?" data-confirm="Delete '<?= esc($task['title']) ?>'?" data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button></div></div><?php endforeach; ?></div><?php endif; ?></div></div>

    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"><h6 class="mb-0 fw-semibold"><i class="bi bi-folder me-2 text-secondary"></i>Documents</h6><button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploadDocModal"><i class="bi bi-upload me-1"></i>Upload</button></div><div class="card-body p-0"><?php if (empty($documents)): ?><div class="text-center text-muted py-4 small"><i class="bi bi-folder fs-4 d-block mb-2 opacity-25"></i>No documents</div><?php else: ?><div class="list-group list-group-flush"><?php foreach ($documents as $doc): ?><div class="list-group-item px-4 py-2 d-flex align-items-center gap-3"><i class="bi bi-file-earmark fs-5 text-muted"></i><div class="flex-grow-1"><div class="small fw-semibold"><?= esc($doc['title'] ?: $doc['file_name']) ?></div><div class="text-muted" style="font-size:11px"><?= esc($doc['category'] ?? '') ?></div></div><a href="<?= base_url('admin/documents/download/'.$doc['id']) ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-download"></i></a></div><?php endforeach; ?></div><?php endif; ?></div></div>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Project Info</h6></div><div class="card-body"><table class="table table-sm table-borderless mb-0"><?php if (!empty($canViewFinancials)): ?><tr><td class="text-muted small">Budget</td><td class="fw-semibold"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['budget']??0,0) ?></td></tr><tr><td class="text-muted small">Advance</td><td class="fw-semibold text-info"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['advance_paid']??0,0) ?></td></tr><tr><td class="text-muted small">Total Paid</td><td class="fw-semibold text-success"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['total_paid']??0,0) ?></td></tr><tr><td class="text-muted small">Balance</td><td class="fw-semibold text-danger"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($balance,0) ?></td></tr><tr><td colspan="2"><hr class="my-1"></td></tr><?php endif; ?><tr><td class="text-muted small">Start</td><td class="small"><?= ($project['start_date']??null) ? date('d M Y',strtotime($project['start_date'])) : '—' ?></td></tr><tr><td class="text-muted small">Delivery</td><td class="small"><?= ($project['delivery_date']??null) ? date('d M Y',strtotime($project['delivery_date'])) : '—' ?></td></tr></table><?php if ($project['description']??null): ?><hr class="my-2"><div class="small text-muted"><?= nl2br(esc($project['description'])) ?></div><?php endif; ?></div></div>
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center"><h6 class="mb-0 fw-semibold"><i class="bi bi-people me-2"></i>Project Team</h6><a href="<?= base_url('admin/projects/'.$project['id'].'/members') ?>" class="btn btn-xs btn-outline-success">Manage</a></div><div class="card-body p-0"><?php if (empty($members)): ?><div class="text-center text-muted py-4 small">No team members assigned.</div><?php else: ?><div class="list-group list-group-flush"><?php foreach (array_slice($members, 0, 6) as $member): ?><div class="list-group-item px-3 py-2 d-flex align-items-center gap-2"><div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width:34px;height:34px"><i class="bi bi-person text-muted"></i></div><div class="flex-grow-1"><div class="small fw-semibold"><?= esc($member['name']) ?></div><div class="text-muted" style="font-size:10px"><?= ucwords(str_replace('_',' ',$member['project_role'])) ?> · <?= ucfirst($member['access_level']) ?></div></div></div><?php endforeach; ?><?php if (count($members) > 6): ?><div class="text-center py-2"><a class="small" href="<?= base_url('admin/projects/'.$project['id'].'/members') ?>">View all <?= count($members) ?> members</a></div><?php endif; ?></div><?php endif; ?></div></div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Activity</h6></div><div class="card-body p-0" style="max-height:300px;overflow-y:auto"><?php if (empty($activities)): ?><div class="text-center text-muted py-3 small">No activity yet</div><?php else: ?><?php foreach ($activities as $act): ?><div class="px-3 py-2 border-bottom"><div class="small"><?= esc($act['description'] ?? $act['action']) ?></div><div class="text-muted" style="font-size:10px"><?= date('d M Y H:i',strtotime($act['created_at'])) ?></div></div><?php endforeach; ?><?php endif; ?></div></div>
  </div>
</div>

<!-- Add / Edit Milestone Modal -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-semibold" id="addMsModalTitle">Add Milestone</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="addMsForm">
        <?= csrf_field() ?>
        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
        <input type="hidden" name="ms_id" id="msEditId" value="">
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label small fw-semibold">Title *</label><input name="title" id="msTitle" class="form-control" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Description</label><textarea name="description" id="msDescription" class="form-control" rows="2"></textarea></div>
          <?php if (!empty($canViewFinancials)): ?>
          <div class="col-md-7"><label class="form-label small fw-semibold">Amount</label><input type="number" step="0.01" min="0" name="amount" id="msAmount" class="form-control" value="0"></div>
          <div class="col-md-5"><label class="form-label small fw-semibold">Currency</label><select name="currency" id="msCurrency" class="form-select"><?php $currencies = ['INR' => '₹ INR', 'USD' => '$ USD', 'EUR' => '€ EUR', 'GBP' => '£ GBP', 'AED' => 'د.إ AED', 'CAD' => 'C$ CAD', 'AUD' => 'A$ AUD', 'SGD' => 'S$ SGD']; foreach ($currencies as $code => $label): ?><option value="<?= $code ?>" <?= $code==='INR'?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div>
          <?php endif; ?>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Date</label><input type="date" name="due_date" id="msDueDate" class="form-control"></div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Time</label><input type="time" name="due_time" id="msDueTime" class="form-control"></div>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="msSubmitBtn">Add Milestone</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0"><h5 class="modal-title fw-semibold">Add Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form id="addTaskForm">
        <?= csrf_field() ?>
        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
        <input type="hidden" name="milestone_id" id="addTaskMilestone" value="">
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label small fw-semibold">Title *</label><input name="title" class="form-control" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Priority</label><select name="priority" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control"></div>
          <div class="col-12"><label class="form-label small fw-semibold">Initial Status</label><select name="status" class="form-select"><option value="todo" selected>To Do</option><option value="in_progress">In Progress</option><option value="code_review">Code Review</option><option value="qa">QA</option><option value="client_review">Client Review</option><option value="blocked">Blocked</option></select></div>
          <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_issue" value="1" id="showTaskIsIssue"><label class="form-check-label small fw-semibold" for="showTaskIsIssue"><i class="bi bi-bug text-danger me-1"></i>Mark as Issue / Bug</label></div></div>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Task</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0"><h5 class="modal-title fw-semibold"><i class="bi bi-upload me-2"></i>Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="<?= base_url('admin/documents/upload') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
        <?php if (!empty($project['client_id'])): ?><input type="hidden" name="client_id" value="<?= $project['client_id'] ?>"><?php endif; ?>
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label small fw-semibold">Title</label><input type="text" name="title" class="form-control" placeholder="Document title (optional)"></div>
          <div class="col-12"><label class="form-label small fw-semibold">Category</label><select name="category" class="form-select"><?php foreach (['proposal','agreement','invoice','contract','screenshot','client_file','project_file','other'] as $cat): ?><option value="<?= $cat ?>" <?= $cat==='project_file'?'selected':'' ?>><?= ucwords(str_replace('_',' ',$cat)) ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><label class="form-label small fw-semibold">File <span class="text-danger">*</span></label><input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.xlsx,.xls,.csv,.txt,.zip"><div class="form-text">PDF, DOC, DOCX, PNG, JPG, XLSX, CSV, TXT, ZIP · Max 10MB</div></div>
          <div class="col-12"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Internal notes..."></textarea></div>
        </div>
        <div class="modal-footer border-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload</button></div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?= view('admin/milestones/partials/notes_modal') ?>
<script>
const BASE = '<?= base_url() ?>';
document.querySelectorAll('[data-status]').forEach(el => { el.addEventListener('click', e => { e.preventDefault(); showLoader('Updating...'); $.post(`${BASE}admin/projects/status/<?= $project['id'] ?>`, {status: el.dataset.status, csrf_test_name: getCsrfToken()}, res => { hideLoader(); showToast(res.message, res.status); if (res.status === 'success') setTimeout(() => location.reload(), 700); }, 'json'); }); });
function resetMsForm() { $('#addMsForm')[0].reset(); $('#msEditId').val(''); $('#addMsModalTitle').text('Add Milestone'); $('#msSubmitBtn').text('Add Milestone'); }
$(document).on('click', '.card-header [data-bs-target="#addMilestoneModal"]', function() { resetMsForm(); });
$(document).on('click', '.btn-edit-ms', function() {
  const d = $(this).data();
  $('#msEditId').val(d.id);
  $('#msTitle').val(d.title || '');
  $('#msDescription').val(d.description || '');
  if ($('#msAmount').length) $('#msAmount').val(d.amount || 0);
  if ($('#msCurrency').length) $('#msCurrency').val(d.currency || 'INR');
  $('#msDueDate').val(d.dueDate || '');
  $('#msDueTime').val(d.dueTime || '');
  $('#addMsModalTitle').text('Edit Milestone');
  $('#msSubmitBtn').text('Save Changes');
  bootstrap.Modal.getOrCreateInstance(document.getElementById('addMilestoneModal')).show();
});
$('#addMsForm').on('submit', function(e) {
  e.preventDefault();
  const editId = $('#msEditId').val();
  const url = editId ? `${BASE}admin/milestones/update/${editId}` : `${BASE}admin/milestones/store`;
  showLoader(editId ? 'Saving...' : 'Adding...');
  $.post(url, $(this).serialize(), res => { hideLoader(); showToast(res.message, res.status); if (res.status === 'success') { bootstrap.Modal.getInstance(document.getElementById('addMilestoneModal')).hide(); setTimeout(() => location.reload(), 500); } }, 'json');
});
$('#addTaskForm').on('submit', function(e) { e.preventDefault(); showLoader('Adding...'); $.post(`${BASE}admin/tasks/store`, $(this).serialize(), res => { hideLoader(); showToast(res.message, res.status); if (res.status === 'success') { bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide(); setTimeout(() => location.reload(), 500); } }, 'json'); });
$(document).on('click', '.btn-ms-add-task', function() { $('#addTaskMilestone').val($(this).data('id')); bootstrap.Modal.getOrCreateInstance(document.getElementById('addTaskModal')).show(); });
let delType = null, delId = null; $(document).on('click', '.btn-del-ms, .btn-del-task', function() { delId = $(this).data('id'); delType = $(this).hasClass('btn-del-ms') ? 'milestones' : 'tasks'; $('#ngConfirmTitle').text($(this).data('confirm-title')); $('#ngConfirmMessage').text($(this).data('confirm')); $('#ngConfirmYes').text($(this).data('confirm-yes')); bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show(); });
$('#ngConfirmYes').off('click').on('click', function() { if (!delId) return; bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide(); showLoader('Deleting...'); $.post(`${BASE}admin/${delType}/delete/${delId}`, {csrf_test_name: getCsrfToken()}, res => { hideLoader(); showToast(res.message, res.status); if (res.status === 'success') setTimeout(() => location.reload(), 500); }, 'json'); delId = null; delType = null; });
</script>
<?= $this->endSection() ?>
