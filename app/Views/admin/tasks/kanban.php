<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2 align-items-center">
    <select id="projectFilter" class="form-select form-select-sm" style="width:200px" onchange="location.href='?project_id='+this.value">
      <option value="">All Projects</option>
      <?php foreach ($projects as $p): ?>
      <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/tasks') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-task me-1"></i>List View</a>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="bi bi-plus-lg me-1"></i>Add Task</button>
  </div>
</div>

<div class="row g-3 flex-nowrap overflow-auto pb-3" id="kanbanBoard" style="min-height:70vh">

  <?php
  $colConfig = [
    'todo'       => ['label'=>'To Do',      'color'=>'secondary', 'icon'=>'bi-circle'],
    'in_progress'=> ['label'=>'In Progress','color'=>'primary',   'icon'=>'bi-arrow-clockwise'],
    'review'     => ['label'=>'Review',     'color'=>'info',      'icon'=>'bi-search'],
    'completed'  => ['label'=>'Done',       'color'=>'success',   'icon'=>'bi-check-circle'],
    'hold'       => ['label'=>'On Hold',    'color'=>'warning',   'icon'=>'bi-pause-circle'],
  ];
  foreach ($columns as $status => $tasks):
    $cfg = $colConfig[$status] ?? ['label'=>ucfirst($status),'color'=>'secondary','icon'=>'bi-circle'];
  ?>
  <div class="col" style="min-width:240px;max-width:280px">
    <div class="card border-0 h-100" style="background:#f8f9fb">
      <div class="card-header border-0 py-2 px-3 bg-transparent">
        <div class="d-flex align-items-center gap-2">
          <i class="bi <?= $cfg['icon'] ?> text-<?= $cfg['color'] ?>"></i>
          <span class="fw-semibold small"><?= $cfg['label'] ?></span>
          <span class="badge bg-<?= $cfg['color'] ?> ms-auto"><?= count($tasks) ?></span>
        </div>
      </div>
      <div class="card-body px-2 py-1 kanban-col" data-status="<?= $status ?>" style="min-height:400px">
        <?php foreach ($tasks as $task): ?>
        <?php $pc = ['low'=>'success','normal'=>'secondary','high'=>'warning','urgent'=>'danger'][$task['priority']??'normal'] ?? 'secondary'; ?>
        <div class="card border-0 shadow-sm mb-2 kanban-card" data-id="<?= $task['id'] ?>">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <span class="badge bg-<?= $pc ?> badge-sm"><?= ucfirst($task['priority']??'normal') ?></span>
              <button class="btn btn-xs text-muted btn-del-task p-0"
                data-id="<?= $task['id'] ?>"
                data-confirm-title="Delete Task?"
                data-confirm="Delete '<?= esc($task['title']) ?>'?"
                data-confirm-yes="Yes, Delete"><i class="bi bi-x"></i></button>
            </div>
            <div class="fw-semibold small mb-1"><?= esc($task['title']) ?></div>
            <?php if ($task['project_name'] ?? null): ?><div class="text-muted" style="font-size:11px"><i class="bi bi-folder2 me-1"></i><?= esc($task['project_name']) ?></div><?php endif; ?>
            <?php if ($task['due_date'] && $task['due_date'] !== '0000-00-00'): ?>
            <?php $ov = strtotime($task['due_date']) < time() && $status !== 'completed'; ?>
            <div class="mt-1 <?= $ov ? 'text-danger' : 'text-muted' ?>" style="font-size:11px"><i class="bi bi-calendar me-1"></i><?= date('d M',strtotime($task['due_date'])) ?><?= $ov ? ' ⚠' : '' ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
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
              <?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><label class="form-label small fw-semibold">Title *</label><input type="text" name="title" class="form-control" required></div>
          <div class="col-12"><label class="form-label small fw-semibold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Priority</label>
            <select name="priority" class="form-select">
              <option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option><option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="col-md-6"><label class="form-label small fw-semibold">Due Date</label><input type="date" name="due_date" class="form-control"></div>
          <div class="col-12"><label class="form-label small fw-semibold">Initial Status</label>
            <select name="status" class="form-select">
              <option value="todo" selected>To Do</option><option value="in_progress">In Progress</option><option value="review">Review</option>
            </select>
          </div>
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

$('#addTaskForm').on('submit', function(e) {
  e.preventDefault();
  showLoader('Adding...');
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
    if (res.status === 'success') setTimeout(() => location.reload(), 400);
  }, 'json');
  delId = null;
});
</script>
<?= $this->endSection() ?>
