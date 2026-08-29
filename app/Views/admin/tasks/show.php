<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-start mb-3 gap-2 flex-wrap">
  <div><a href="<?= base_url('admin/tasks') ?>" class="text-muted small">← Back to Tasks</a><h3 class="mb-1 mt-1"><?= esc($task['title']) ?> <?php if(!empty($task['is_issue'])): ?><span class="badge bg-danger-subtle text-danger border border-danger-subtle align-middle" style="font-size:12px"><i class="bi bi-bug-fill me-1"></i>Issue</span><?php endif; ?></h3><div class="text-muted small"><?= esc($task['project_name'] ?? 'Project') ?><?= !empty($task['milestone_title']) ? ' · '.esc($task['milestone_title']) : '' ?></div></div>
  <span class="badge bg-primary fs-6"><?= ucwords(str_replace('_',' ',$task['status'])) ?></span>
</div>
<div class="row g-3">
 <div class="col-lg-8">
  <div class="card border-0 shadow-sm mb-3"><div class="card-body"><h6>Description</h6><div class="text-muted"><?= nl2br(esc($task['description'] ?? 'No description.')) ?></div></div></div>
  <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">Attachments <label class="btn btn-sm btn-outline-primary mb-0"><i class="bi bi-paperclip me-1"></i>Upload<input type="file" id="attachmentInput" class="d-none"></label></div><div class="card-body">
   <div id="attachmentsGrid" class="row g-2">
     <?php foreach($attachments as $a): ?>
       <div class="col-6 col-md-4 col-lg-3" data-attachment-row="<?= $a['id'] ?>">
         <?php if(!empty($a['is_image'])): ?>
           <a href="<?= base_url('admin/tasks/attachments/'.$a['id']) ?>" target="_blank"><img src="<?= base_url('admin/tasks/attachments/'.$a['id']) ?>" class="img-fluid rounded border" style="aspect-ratio:1;object-fit:cover;width:100%"></a>
         <?php else: ?>
           <a href="<?= base_url('admin/tasks/attachments/'.$a['id']) ?>" class="d-flex flex-column align-items-center justify-content-center border rounded text-decoration-none text-dark p-2" style="aspect-ratio:1"><i class="bi bi-file-earmark-text fs-2 text-muted"></i><span class="small text-truncate w-100 text-center"><?= esc($a['original_name']) ?></span></a>
         <?php endif; ?>
         <div class="d-flex justify-content-between align-items-center mt-1"><span class="text-muted" style="font-size:10px"><?= esc($a['uploader_name'] ?? '') ?></span><button type="button" class="btn btn-xs text-danger p-0 btn-del-attachment" data-id="<?= $a['id'] ?>"><i class="bi bi-trash"></i></button></div>
       </div>
     <?php endforeach; ?>
     <?php if(!$attachments): ?><div class="text-muted small px-2" id="noAttachmentsMsg">No files yet — click Upload to add screenshots, mockups, or documents.</div><?php endif; ?>
   </div>
  </div></div>
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
  <div class="card border-0 shadow-sm mb-3"><div class="card-body"><div class="small text-muted">Priority</div><div class="fw-semibold mb-3"><?= ucfirst($task['priority'] ?? 'medium') ?></div><div class="small text-muted">Due date</div><div class="fw-semibold mb-3"><?= !empty($task['due_date']) && $task['due_date']!=='0000-00-00' ? date('d M Y',strtotime($task['due_date'])) : '—' ?></div><div class="small text-muted">Assignee</div><div class="fw-semibold mb-3"><?= esc($task['assigned_name'] ?? 'Unassigned') ?></div><div class="form-check"><input class="form-check-input" type="checkbox" id="issueToggle" <?= !empty($task['is_issue'])?'checked':'' ?>><label class="form-check-label small fw-semibold" for="issueToggle"><i class="bi bi-bug text-danger me-1"></i>Mark as Issue / Bug</label></div></div></div>
  <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Activity</div><div class="card-body"><div class="small"> <?php foreach($activities as $a): ?><div class="mb-3"><div class="fw-semibold"><?= esc($a['action']) ?></div><div class="text-muted"><?= esc($a['user_name'] ?? 'System') ?> · <?= esc($a['created_at']) ?></div></div><?php endforeach; if(!$activities): ?><span class="text-muted">No activity yet.</span><?php endif; ?></div></div></div>
 </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script>
const BASE='<?= base_url() ?>', TASK_ID=<?= (int)$task['id'] ?>;
$('#subtaskForm').on('submit',function(e){e.preventDefault();$(this).find('input[name="csrf_test_name"]').val(getCsrfToken());$.post(`${BASE}admin/tasks/${TASK_ID}/subtasks`,$(this).serialize(),r=>{showToast(r.message,r.status);if(r.status==='success')location.reload()},'json')});
$('#commentForm').on('submit',function(e){e.preventDefault();$(this).find('input[name="csrf_test_name"]').val(getCsrfToken());$.post(`${BASE}admin/tasks/${TASK_ID}/comments`,$(this).serialize(),r=>{showToast(r.message,r.status);if(r.status==='success')location.reload()},'json')});
$(document).on('change','.subtask-toggle',function(){const id=$(this).data('id');$.post(`${BASE}admin/tasks/subtasks/${id}/toggle`,{csrf_test_name:getCsrfToken()},r=>{showToast(r.message,r.status);if(r.status!=='success')location.reload()},'json')});
$('#issueToggle').on('change',function(){const c=this.checked;$.post(`${BASE}admin/tasks/update/${TASK_ID}`,{_full_form:'1',is_issue:c?1:0,csrf_test_name:getCsrfToken()},r=>{showToast(r.message,r.status);if(r.status!=='success'){this.checked=!c;}},'json')});
$('#attachmentInput').on('change',function(){
  if(!this.files.length)return;const fd=new FormData();fd.append('file',this.files[0]);fd.append('csrf_test_name',getCsrfToken());
  showLoader('Uploading...');
  $.ajax({url:`${BASE}admin/tasks/${TASK_ID}/attachments`,method:'POST',data:fd,processData:false,contentType:false,dataType:'json'})
    .done(r=>{hideLoader();showToast(r.message,r.status);if(r.status==='success')location.reload();})
    .fail(()=>{hideLoader();showToast('Upload failed.','error');});
  this.value='';
});
let delAttId=null;$(document).on('click','.btn-del-attachment',function(){delAttId=$(this).data('id');bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();});
$('#ngConfirmYes').off('click.att').on('click.att',function(){if(!delAttId)return;bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal'))?.hide();showLoader('Removing...');$.post(`${BASE}admin/tasks/attachments/${delAttId}/delete`,{csrf_test_name:getCsrfToken()},r=>{hideLoader();showToast(r.message,r.status);if(r.status==='success')$(`[data-attachment-row="${delAttId}"]`).fadeOut(200,function(){$(this).remove();});},'json');delAttId=null;});
</script><?= $this->endSection() ?>
