<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2 align-items-center flex-wrap">
    <select id="projectFilter" class="form-select form-select-sm" style="width:200px">
      <option value="">All Projects</option>
      <?php foreach ($projects as $p): ?>
      <option value="<?= $p['id'] ?>" <?= ($currentProject ?? '') == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="btn-group">
      <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
      <button class="btn btn-sm btn-outline-secondary filter-btn" data-status="todo">To Do</button>
      <button class="btn btn-sm btn-outline-primary filter-btn" data-status="in_progress">In Progress</button>
      <button class="btn btn-sm btn-outline-info filter-btn" data-status="review">Review</button>
      <button class="btn btn-sm btn-outline-success filter-btn" data-status="completed">Done</button>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/tasks/kanban') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-kanban me-1"></i>Kanban</a>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="bi bi-plus-lg me-1"></i>Add Task</button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0" id="tasksTable">
      <thead class="table-light">
        <tr><th>Task</th><th>Project</th><th>Priority</th><th>Status</th><th>Due Date</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tasks as $task): ?>
        <?php
          $sc = ['todo'=>'secondary','in_progress'=>'primary','review'=>'info','completed'=>'success','hold'=>'warning'][$task['status']] ?? 'secondary';
          $pc = ['low'=>'success','normal'=>'secondary','high'=>'warning','urgent'=>'danger'][$task['priority'] ?? 'normal'] ?? 'secondary';
          $overdue = $task['due_date'] && $task['due_date'] !== '0000-00-00' && strtotime($task['due_date']) < time() && $task['status'] !== 'completed';
        ?>
        <tr>
          <td>
            <div class="fw-semibold"><?= esc($task['title']) ?></div>
            <?php if ($task['description']): ?><div class="text-muted small"><?= esc(substr($task['description'],0,60)) ?>...</div><?php endif; ?>
          </td>
          <td><a href="<?= base_url('admin/projects/'.$task['project_id']) ?>" class="text-decoration-none small"><?= esc($task['project_name'] ?? '—') ?></a></td>
          <td><span class="badge bg-<?= $pc ?>"><?= ucfirst($task['priority'] ?? 'normal') ?></span></td>
          <td>
            <select class="form-select form-select-sm task-status-sel" data-id="<?= $task['id'] ?>" style="width:120px">
              <?php foreach (['todo','in_progress','review','completed','hold'] as $s): ?>
              <option value="<?= $s ?>" <?= $task['status']==$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="<?= $overdue ? 'text-danger fw-semibold' : '' ?> small">
            <?= $task['due_date'] && $task['due_date'] !== '0000-00-00' ? date('d M Y',strtotime($task['due_date'])) : '—' ?>
            <?php if ($overdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
          </td>
          <td>
            <button class="btn btn-xs btn-outline-danger btn-del-task"
              data-id="<?= $task['id'] ?>"
              data-confirm-title="Delete Task?"
              data-confirm="Delete '<?= esc($task['title']) ?>'?"
              data-confirm-yes="Yes, Delete"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0"><h5 class="modal-title fw-semibold">Add Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form id="addTaskForm">
        <?= csrf_field() ?>
        <div class="modal-body row g-3">
          <div class="col-12"><label class="form-label small fw-semibold">Project *</label>
            <select name="project_id" class="form-select" required>
              <option value="">Select Project</option>
              <?php foreach ($projects as $p): ?>
              <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><label class="form-label small fw-semibold">Task Title *</label><input type="text" name="title" class="form-control" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Priority</label>
            <select name="priority" class="form-select">
              <option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option><option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control"></div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Task</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;

$('#tasksTable').DataTable({ pageLength: 25, order: [[4,'asc']] });

$('.task-status-sel').on('change', function() {
  $.post(`${BASE}admin/tasks/status/${$(this).data('id')}`, {status: $(this).val(), csrf_test_name: CSRF}, res => {
    showToast(res.message, res.status);
  }, 'json');
});

$('#addTaskForm').on('submit', function(e) {
  e.preventDefault();
  showLoader('Adding task...');
  $.post(`${BASE}admin/tasks/store`, $(this).serialize(), res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') { bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide(); setTimeout(() => location.reload(), 500); }
  }, 'json');
});

let delId = null;
$(document).on('click', '.btn-del-task', function() {
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
  $.post(`${BASE}admin/tasks/delete/${delId}`, {csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
    if (res.status === 'success') location.reload();
  }, 'json');
  delId = null;
});
</script>
<?= $this->endSection() ?>
