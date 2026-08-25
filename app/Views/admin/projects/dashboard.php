<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$projectStatus = ucwords(str_replace('_',' ',$project['status'] ?? ''));
$stat = $stats;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <div class="text-muted small mb-1">Project PMS Dashboard</div>
    <h4 class="fw-bold mb-1"><?= esc($project['name']) ?></h4>
    <div class="small text-muted"><?= esc($project['client_name'] ?? '—') ?> · <?= esc($project['project_number'] ?? '') ?> · <?= esc($projectStatus) ?></div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/projects/'.$project['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Project</a>
    <a href="<?= base_url('admin/tasks/kanban?project_id='.$project['id']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-kanban me-1"></i>Open Kanban</a>
  </div>
</div>

<div class="row g-3 mb-4">
<?php foreach ([
  ['Total Tasks',$stat['total_tasks'],'primary','bi-check2-square'],
  ['Completed',$stat['done_tasks'],'success','bi-check-circle'],
  ['Overdue Tasks',$stat['overdue_tasks'],'danger','bi-exclamation-triangle'],
  ['Pending Approvals',$stat['pending_approvals'],'warning','bi-person-check']
] as $card): ?>
  <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small"><?= $card[0] ?></div><div class="fs-3 fw-bold mt-1"><?= (int)$card[1] ?></div></div><i class="bi <?= $card[3] ?> text-<?= $card[2] ?> fs-4"></i></div></div></div></div>
<?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between mb-2"><span class="fw-semibold">Task Progress</span><strong><?= (int)$stat['task_progress'] ?>%</strong></div><div class="progress mb-3" style="height:9px"><div class="progress-bar bg-success" style="width:<?= (int)$stat['task_progress'] ?>%"></div></div><div class="d-flex flex-wrap gap-2 small">
    <?php foreach ($stat['task_stats'] as $status=>$count): ?><span class="badge bg-light text-dark border"><?= esc(ucwords(str_replace('_',' ',$status))) ?>: <?= (int)$count ?></span><?php endforeach; ?>
  </div></div></div></div>
  <div class="col-lg-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between mb-2"><span class="fw-semibold">Milestone Progress</span><strong><?= (int)$stat['milestone_progress'] ?>%</strong></div><div class="progress mb-3" style="height:9px"><div class="progress-bar bg-info" style="width:<?= (int)$stat['milestone_progress'] ?>%"></div></div><div class="d-flex flex-wrap gap-2 small">
    <?php foreach ($stat['milestone_stats'] as $status=>$count): ?><span class="badge bg-light text-dark border"><?= esc(ucwords(str_replace('_',' ',$status))) ?>: <?= (int)$count ?></span><?php endforeach; ?>
  </div></div></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Delivery Health</h6></div><div class="card-body">
    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Overdue milestones</span><strong class="text-danger"><?= (int)$stat['overdue_milestones'] ?></strong></div>
    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Overdue tasks</span><strong class="text-danger"><?= (int)$stat['overdue_tasks'] ?></strong></div>
    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Next 7 days</span><strong class="text-info"><?= (int)$stat['upcoming_tasks'] ?></strong></div>
    <div class="d-flex justify-content-between py-2"><span class="text-muted small">Client approvals</span><strong class="text-warning"><?= (int)$stat['pending_approvals'] ?></strong></div>
  </div></div></div>
  <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Project Team</h6></div><div class="card-body p-0">
    <?php if (empty($members)): ?><div class="text-muted small text-center py-4">No project members</div><?php else: ?><?php foreach ($members as $member): ?><div class="px-3 py-2 border-bottom d-flex justify-content-between"><span class="small"><?= esc($member['name'] ?? $member['email'] ?? 'User') ?></span><span class="badge bg-light text-dark border"><?= esc(ucwords(str_replace('_',' ',$member['project_role'] ?? 'member'))) ?></span></div><?php endforeach; ?><?php endif; ?>
  </div></div></div>
  <div class="col-lg-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Recent Activity</h6></div><div class="card-body p-0">
    <?php if (empty($activities)): ?><div class="text-muted small text-center py-4">No recent activity</div><?php else: ?><?php foreach ($activities as $activity): ?><div class="px-3 py-2 border-bottom"><div class="small"><?= esc($activity['description'] ?? $activity['action'] ?? '') ?></div><div class="text-muted" style="font-size:10px"><?= !empty($activity['created_at']) ? date('d M Y H:i',strtotime($activity['created_at'])) : '' ?></div></div><?php endforeach; ?><?php endif; ?>
  </div></div></div>
</div>
<?= $this->endSection() ?>
