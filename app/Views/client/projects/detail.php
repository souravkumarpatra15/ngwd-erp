<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<?php
$statusColors = ['pending'=>'secondary','development'=>'primary','testing'=>'info','revision'=>'warning','completed'=>'success','on_hold'=>'danger','cancelled'=>'dark'];
$sc = $statusColors[$project['status']] ?? 'secondary';
$balance = ($project['budget'] ?? 0) - ($project['advance_paid'] ?? 0);
$taskStatusColors = ['todo'=>'secondary','in_progress'=>'primary','code_review'=>'warning','qa'=>'info','client_review'=>'warning','blocked'=>'danger','done'=>'success','cancelled'=>'dark'];
$taskStatusLabels = ['todo'=>'To Do','in_progress'=>'In Progress','code_review'=>'Code Review','qa'=>'QA','client_review'=>'Client Review','blocked'=>'Blocked','done'=>'Done','cancelled'=>'Cancelled'];
$taskCounts = [];
foreach (($tasks ?? []) as $task) { $taskCounts[$task['status']] = ($taskCounts[$task['status']] ?? 0) + 1; }
?>

<!-- Project header -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="row align-items-center">
      <div class="col-md-8">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-<?= $sc ?> fs-6"><?= ucwords(str_replace('_',' ',$project['status'])) ?></span>
          <span class="text-muted small"><?= esc($project['project_number']) ?></span>
          <span class="badge bg-light text-dark border small"><?= ucwords(str_replace('_',' ',$project['type'] ?? '')) ?></span>
        </div>
        <h5 class="fw-bold mb-1"><?= esc($project['name']) ?></h5>
        <?php if ($project['description']): ?><p class="text-muted small mb-2"><?= esc($project['description']) ?></p><?php endif; ?>
        <div class="text-muted small">
          <?php if ($project['start_date'] ?? null): ?><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($project['start_date'])) ?><?php if ($project['delivery_date'] ?? null): ?> → <?= date('d M Y', strtotime($project['delivery_date'])) ?><?php endif; ?><?php endif; ?>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="fs-4 fw-bold text-primary mb-1"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['budget'] ?? 0, 0) ?></div>
        <div class="small text-muted">Advance paid: <span class="text-success fw-semibold"><?= currencySymbol($project['currency'] ?? 'INR') ?><?= number_format($project['advance_paid'] ?? 0, 0) ?></span></div>
      </div>
    </div>
    <div class="mt-3 pt-3 border-top">
      <div class="d-flex justify-content-between align-items-center mb-1"><span class="text-muted small">Progress</span><span class="fw-semibold small"><?= (int) $progress ?>%</span></div>
      <div class="progress" style="height:6px"><?php $pc = $progress >= 100 ? 'success' : ($progress >= 60 ? 'info' : ($progress >= 30 ? 'warning' : 'danger')); ?><div class="progress-bar bg-<?= $pc ?>" style="width:<?= (int) $progress ?>%"></div></div>
    </div>
  </div>
</div>

<!-- Client-visible task progress -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <div><h6 class="mb-0 fw-semibold"><i class="bi bi-kanban me-2 text-primary"></i>Project Work</h6><div class="text-muted small mt-1">Current delivery status of project tasks</div></div>
    <span class="badge bg-light text-dark border"><?= count($tasks ?? []) ?> tasks</span>
  </div>
  <div class="card-body">
    <?php if (empty($tasks)): ?>
      <div class="text-center text-muted py-4"><i class="bi bi-kanban fs-2 d-block mb-2 opacity-25"></i><div class="small">No project tasks are visible yet.</div></div>
    <?php else: ?>
      <div class="row g-2 mb-3">
        <?php foreach ($taskStatusLabels as $status => $label): if (!empty($taskCounts[$status])): ?><div class="col-6 col-md-auto"><span class="badge bg-<?= $taskStatusColors[$status] ?> me-1"><?= $label ?></span><span class="small text-muted"><?= (int)$taskCounts[$status] ?></span></div><?php endif; endforeach; ?>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead><tr><th>Task</th><th>Milestone</th><th>Assigned To</th><th>Status</th><th>Due</th></tr></thead>
          <tbody>
          <?php foreach ($tasks as $task): $ts = $taskStatusColors[$task['status']] ?? 'secondary'; $tl = $taskStatusLabels[$task['status']] ?? ucwords(str_replace('_',' ',$task['status'])); $overdue = !empty($task['due_date']) && $task['due_date'] !== '0000-00-00' && strtotime($task['due_date']) < time() && !in_array($task['status'], ['done','cancelled'], true); ?>
            <tr>
              <td><div class="fw-semibold small"><?= esc($task['title']) ?></div><?php if (!empty($task['priority'])): ?><span class="text-muted" style="font-size:10px">Priority: <?= esc(strtoupper($task['priority'])) ?></span><?php endif; ?></td>
              <td class="small text-muted"><?= esc($task['milestone_title'] ?? '—') ?></td>
              <td class="small"><?= esc($task['assigned_name'] ?? 'Unassigned') ?></td>
              <td><span class="badge bg-<?= $ts ?>"><?= esc($tl) ?></span></td>
              <td class="small <?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>"><?= !empty($task['due_date']) && $task['due_date'] !== '0000-00-00' ? date('d M Y', strtotime($task['due_date'])) : '—' ?><?php if ($overdue): ?> <span class="badge bg-danger">Overdue</span><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="alert alert-light border mt-3 mb-0 small"><i class="bi bi-info-circle me-1"></i>Task status is read-only in the client portal. Your project team manages delivery status.</div>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <!-- Milestones -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-flag me-2 text-warning"></i>Project Milestones</h6></div>
      <div class="card-body p-0">
        <?php if (empty($milestones)): ?><div class="text-center text-muted py-5"><i class="bi bi-flag fs-2 d-block mb-2 opacity-25"></i><div class="small">No milestones have been added yet.</div></div>
        <?php else: ?><div class="list-group list-group-flush">
          <?php $msColors = ['pending'=>'secondary','in_progress'=>'primary','completed'=>'success','paid'=>'success']; $msIcons = ['pending'=>'bi-circle','in_progress'=>'bi-arrow-clockwise','completed'=>'bi-check-circle-fill','paid'=>'bi-check-circle-fill']; foreach ($milestones as $ms): $msc=$msColors[$ms['status']]??'secondary'; $msi=$msIcons[$ms['status']]??'bi-circle'; $overdue=$ms['due_date']&&$ms['due_date']!=='0000-00-00'&&strtotime($ms['due_date'])<time()&&$ms['status']==='pending'; ?>
          <div class="list-group-item px-4 py-3"><div class="d-flex justify-content-between align-items-start gap-3"><div class="d-flex gap-3 align-items-start"><i class="bi <?= $msi ?> text-<?= $msc ?> mt-1 fs-5"></i><div><div class="fw-semibold small"><?= esc($ms['title']) ?></div><?php if ($ms['description']): ?><div class="text-muted" style="font-size:12px"><?= esc($ms['description']) ?></div><?php endif; ?><div class="mt-1 d-flex gap-2 align-items-center flex-wrap"><span class="badge bg-<?= $msc ?> badge-sm"><?= ucfirst($ms['status']) ?></span><?php if ($ms['due_date']&&$ms['due_date']!=='0000-00-00'): ?><span class="<?= $overdue?'text-danger fw-semibold':'text-muted' ?>" style="font-size:11px"><i class="bi bi-calendar me-1"></i><?= date('d M Y',strtotime($ms['due_date'])) ?><?php if($overdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?></span><?php endif; ?></div></div></div><div class="text-end flex-shrink-0"><div class="fw-bold text-primary"><?= currencySymbol($ms['currency']??'INR') ?><?= number_format($ms['amount']??0,0) ?></div><button class="btn btn-xs btn-outline-info mt-1 btn-ms-notes" data-id="<?= $ms['id'] ?>" data-title="<?= esc($ms['title']) ?>" title="Notes / Ask a question"><i class="bi bi-chat-left-text"></i></button><?php if(in_array($ms['status'],['pending','in_progress'])): ?><a href="<?= base_url('portal/pay-milestone/'.$ms['id']) ?>" class="btn btn-xs btn-success mt-1"><i class="bi bi-credit-card me-1"></i>Pay</a><?php endif; ?></div></div></div>
          <?php endforeach; ?></div>
          <?php $msTotalsByCur=[]; foreach($milestones as $ms){$c=$ms['currency']??'INR';$msTotalsByCur[$c]=($msTotalsByCur[$c]??0)+(float)($ms['amount']??0);} ?><div class="px-4 py-3 border-top bg-light d-flex justify-content-between small"><span class="text-muted">Total Milestones Value</span><span class="fw-bold text-primary"><?php foreach($msTotalsByCur as $c=>$amt): ?><?= currencySymbol($c) ?><?= number_format($amt,0) ?><?= end($msTotalsByCur)!==$amt?' + ':'' ?><?php endforeach; ?></span></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Quick Links</h6></div><div class="list-group list-group-flush"><a href="<?= base_url('portal/invoices') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2"><i class="bi bi-receipt text-warning"></i><span class="small">My Invoices</span></a><a href="<?= base_url('portal/payments') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2"><i class="bi bi-cash-stack text-success"></i><span class="small">Payment History</span></a><a href="<?= base_url('portal/documents') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2"><i class="bi bi-folder text-info"></i><span class="small">Documents</span></a><a href="<?= base_url('portal/tickets/create') ?>" class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2"><i class="bi bi-headset text-primary"></i><span class="small">Raise Support Ticket</span></a></div></div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Project Summary</h6></div><div class="card-body"><div class="table-responsive"><table class="table table-sm table-borderless mb-0"><tr><td class="text-muted small">Project #</td><td class="fw-semibold small"><?= esc($project['project_number']) ?></td></tr><tr><td class="text-muted small">Type</td><td class="small"><?= ucwords(str_replace('_',' ',$project['type']??'')) ?></td></tr><tr><td class="text-muted small">Budget</td><td class="fw-semibold"><?= currencySymbol($project['currency']??'INR') ?><?= number_format($project['budget']??0,0) ?></td></tr><tr><td class="text-muted small">Advance</td><td class="text-success fw-semibold"><?= currencySymbol($project['currency']??'INR') ?><?= number_format($project['advance_paid']??0,0) ?></td></tr><tr><td colspan="2"><hr class="my-1"></td></tr><tr><td class="text-muted small">Start</td><td class="small"><?= ($project['start_date']??null)?date('d M Y',strtotime($project['start_date'])):'—' ?></td></tr><tr><td class="text-muted small">Delivery</td><td class="small"><?= ($project['delivery_date']??null)?date('d M Y',strtotime($project['delivery_date'])):'TBD' ?></td></tr><tr><td class="text-muted small">Milestones</td><td class="small"><?= count($milestones) ?> total</td></tr><tr><td class="text-muted small">Tasks</td><td class="small"><?= count($tasks??[]) ?> total</td></tr><tr><td class="text-muted small">Completed</td><td class="small text-success fw-semibold"><?= count(array_filter($milestones,fn($m)=>in_array($m['status'],['completed','paid']))) ?> / <?= count($milestones) ?></td></tr></table></div></div></div>
  </div>
</div>

<div class="text-center mt-4"><a href="<?= base_url('portal/projects') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Projects</a></div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?= view('client/projects/partials/notes_modal') ?>
<?= $this->endSection() ?>