<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<?php $statusColors=['draft'=>'secondary','in_progress'=>'primary','submitted'=>'info','under_review'=>'warning','changes_requested'=>'danger','approved'=>'success','rejected'=>'dark']; $status=$deliverable['status']; $color=$statusColors[$status]??'secondary'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div><div class="text-muted small mb-1">Deliverable review</div><h5 class="fw-bold mb-0"><?= esc($deliverable['title']) ?></h5></div>
  <a href="<?= base_url('portal/projects/'.$deliverable['project_id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Project</a>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
      <div class="d-flex flex-wrap gap-2 mb-3"><span class="badge bg-<?= $color ?>"><?= esc(ucwords(str_replace('_',' ',$status))) ?></span><?php if(!empty($deliverable['version'])):?><span class="badge bg-light text-dark border">Version <?= esc($deliverable['version']) ?></span><?php endif;?></div>
      <?php if(!empty($deliverable['description'])):?><p class="text-muted"><?= nl2br(esc($deliverable['description'])) ?></p><?php else:?><p class="text-muted small">No description provided.</p><?php endif;?>
      <div class="row g-3 small"><div class="col-md-4"><div class="text-muted">Project</div><div class="fw-semibold"><?= esc($deliverable['project_name']) ?></div></div><div class="col-md-4"><div class="text-muted">Milestone</div><div class="fw-semibold"><?= esc($deliverable['milestone_title']??'—') ?></div></div><div class="col-md-4"><div class="text-muted">Due date</div><div class="fw-semibold"><?= !empty($deliverable['due_date'])?date('d M Y',strtotime($deliverable['due_date'])):'—' ?></div></div></div>
    </div></div>
    <?php if(in_array($status,['submitted','under_review','changes_requested'],true)): ?>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Client Review</h6><div class="text-muted small mt-1">Approve this deliverable or request changes from the project team.</div></div><div class="card-body">
      <form method="post" action="<?= base_url('portal/deliverables/'.$deliverable['id'].'/review') ?>">
        <?= csrf_field() ?>
        <label class="form-label small fw-semibold">Feedback <span class="text-muted fw-normal">(required when requesting changes)</span></label>
        <textarea name="comment" class="form-control mb-3" rows="4" maxlength="5000" placeholder="Add feedback or approval notes..."></textarea>
        <div class="d-flex gap-2"><button name="action" value="approved" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Approve Deliverable</button><button name="action" value="changes_requested" class="btn btn-outline-danger"><i class="bi bi-arrow-counterclockwise me-1"></i>Request Changes</button></div>
      </form>
    </div></div>
    <?php elseif($status==='approved'): ?>
      <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>This deliverable has been approved.</div>
    <?php endif; ?>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Review History</h6></div><div class="list-group list-group-flush">
      <?php if(empty($history)): ?><div class="p-4 text-muted small text-center">No review activity yet.</div><?php else: foreach($history as $h): ?><div class="list-group-item py-3"><div class="d-flex justify-content-between gap-2"><span class="fw-semibold small"><?= esc($h['user_name']??'Client') ?></span><span class="text-muted" style="font-size:11px"><?= !empty($h['created_at'])?date('d M Y H:i',strtotime($h['created_at'])):'' ?></span></div><div class="small mt-1"><?= esc(ucwords(str_replace('_',' ',$h['action']))) ?></div><?php if(!empty($h['comment'])):?><div class="text-muted small mt-1"><?= nl2br(esc($h['comment'])) ?></div><?php endif;?></div><?php endforeach; endif; ?>
    </div></div>
  </div>
</div>
<?= $this->endSection() ?>
