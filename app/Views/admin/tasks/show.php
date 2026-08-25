<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-start mb-3 gap-2 flex-wrap">
  <div><a href="<?= base_url('admin/tasks') ?>" class="text-muted small">← Back to Tasks</a><h3 class="mb-1 mt-1"><?= esc($task['title']) ?></h3><div class="text-muted small"><?= esc($task['project_name'] ?? 'Project') ?><?= !empty($task['milestone_title']) ? ' · '.esc($task['milestone_title']) : '' ?></div></div>
  <span class="badge bg-primary fs-6"><?= ucwords(str_replace('_',' ',$task['status'])) ?></span>
</div>
<div class="row g-3">
 <div class="col-lg-8">
  <div class="card border-0 shadow-sm mb-3"><div class="card-body"><h6>Description</h6><div class="text-muted"><?= nl2br(esc($task['description'] ?? 'No description.')) ?></div></div></div>
  <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Subtasks</div><div class="card-body">
   <form id="subtaskForm" class="d-flex gap-2 mb-3"><?= csrf_field() ?><input name="title" class="form-control" placeholder="Add a subtask..." required><button class="btn btn-primary">Add</button></form>
   <div id="subtasks"> <?php foreach($subtasks as $s): ?><div class="form-check mb-2"><input class="form-check-input subtask-toggle" type="checkbox" data-id="<?= $s['id'] ?>" <?= $s['is_completed']?'checked':'' ?>><label class="form-check-label <?= $s['is_completed']?'text-decoration-line-through text-muted':'' ?>"><?= esc($s['title']) ?></label></div><?php endforeach; if(!$subtasks): ?><div class="text-muted small">No subtasks yet.</div><?php endif; ?></div>
  </div></div>
  <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Comments</div><div class="card-body">
   <?php foreach($comments as $c): ?><div class="border-bottom pb-2 mb-2"><div class="fw-semibold small"><?= esc($c['user_name'] ?? $c['user_email'] ?? 'User') ?> <span class="text-muted fw-normal">· <?= esc($c['created_at']) ?></span></div><div><?= nl2br(esc($c['body'])) ?></div></div><?php endforeach; if(!$comments): ?><div class="text-muted small mb-3">No comments yet.</div><?php endif; ?>
   <form id="commentForm"><?= csrf_field() ?><textarea name="body" class="form-control mb-2" rows="3" placeholder="Write a comment..." required></textarea><button class="btn btn-primary btn-sm">Comment</button></form>
  </div></div>
 </div>
 <div class="col-lg-4">
  <div class="card border-0 shadow-sm mb-3"><div class="card-body"><div class="small text-muted">Priority</div><div class="fw-semibold mb-3"><?= ucfirst($task['priority'] ?? 'medium') ?></div><div class="small text-muted">Due date</div><div class="fw-semibold mb-3"><?= !empty($task['due_date']) && $task['due_date']!=='0000-00-00' ? date('d M Y',strtotime($task['due_date'])) : '—' ?></div><div class="small text-muted">Assignee</div><div class="fw-semibold"><?= esc($task['assignee_name'] ?? 'Unassigned') ?></div></div></div>
  <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Activity</div><div class="card-body"><div class="small"> <?php foreach($activities as $a): ?><div class="mb-3"><div class="fw-semibold"><?= esc($a['action']) ?></div><div class="text-muted"><?= esc($a['user_name'] ?? 'System') ?> · <?= esc($a['created_at']) ?></div></div><?php endforeach; if(!$activities): ?><span class="text-muted">No activity yet.</span><?php endif; ?></div></div></div>
 </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script>
const BASE='<?= base_url() ?>', CSRF=CSRF_TOKEN, TASK_ID=<?= (int)$task['id'] ?>;
$('#subtaskForm').on('submit',function(e){e.preventDefault();$.post(`${BASE}admin/tasks/${TASK_ID}/subtasks`,$(this).serialize(),r=>{showToast(r.message,r.status);if(r.status==='success')location.reload()},'json')});
$('#commentForm').on('submit',function(e){e.preventDefault();$.post(`${BASE}admin/tasks/${TASK_ID}/comments`,$(this).serialize(),r=>{showToast(r.message,r.status);if(r.status==='success')location.reload()},'json')});
$(document).on('change','.subtask-toggle',function(){const id=$(this).data('id');$.post(`${BASE}admin/tasks/subtasks/${id}/toggle`,{csrf_test_name:CSRF},r=>{showToast(r.message,r.status);if(r.status!=='success')location.reload()},'json')});
</script><?= $this->endSection() ?>
