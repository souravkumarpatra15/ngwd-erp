<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><h5 class="mb-1 fw-bold">Deliverables</h5><div class="text-muted small">Milestone-based project delivery tracking</div></div><?php if ($projectId): ?><a href="<?= base_url('admin/deliverables/create?project_id='.$projectId) ?>" class="btn btn-sm btn-primary">Add Deliverable</a><?php endif; ?></div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<div class="card border-0 shadow-sm mb-3"><div class="card-body"><form method="get" class="row g-2 align-items-end"><div class="col-md-6"><label class="form-label small fw-semibold">Project</label><select name="project_id" class="form-select"><option value="">Select project</option><?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>" <?= $projectId == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">View</button></div></form></div></div>
<div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-4">Deliverable</th><th>Milestone</th><th>Owner</th><th>Due</th><th>Status</th><th class="pe-4 text-end">Action</th></tr></thead><tbody><?php if (!$projectId || empty($deliverables)): ?><tr><td colspan="6" class="text-center text-muted py-5"><?= $projectId ? 'No deliverables yet.' : 'Select a project to view deliverables.' ?></td></tr><?php else: ?><?php $colors=['draft'=>'secondary','in_progress'=>'primary','submitted'=>'info','under_review'=>'warning','changes_requested'=>'danger','approved'=>'success','rejected'=>'dark']; foreach ($deliverables as $d): ?><tr data-deliverable-row="<?= $d['id'] ?>"><td class="ps-4"><div class="fw-semibold small"><?= esc($d['title']) ?></div><div class="text-muted" style="font-size:11px">v<?= esc($d['version']) ?></div></td><td class="small"><?= esc($d['milestone_title'] ?? '—') ?></td><td class="small"><?= esc($d['owner_name'] ?? '—') ?></td><td class="small"><?= $d['due_date'] ? date('d M Y',strtotime($d['due_date'])) : '—' ?></td><td><span class="badge bg-<?= $colors[$d['status']] ?? 'secondary' ?> deliverable-status-badge"><?= ucwords(str_replace('_',' ',$d['status'])) ?></span></td><td class="pe-4 text-end"><select class="form-select form-select-sm d-inline-block deliverable-status-select" data-id="<?= $d['id'] ?>" style="width:150px"><?php foreach ($colors as $s => $c): ?><option value="<?= $s ?>" <?= $d['status']===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const statusColors = <?= json_encode($colors ?? ['draft'=>'secondary','in_progress'=>'primary','submitted'=>'info','under_review'=>'warning','changes_requested'=>'danger','approved'=>'success','rejected'=>'dark']) ?>;
  $(document).on('change', '.deliverable-status-select', function () {
    const id = $(this).data('id');
    const status = $(this).val();
    const row = $(`tr[data-deliverable-row="${id}"]`);
    showLoader('Updating status...');
    $.post(`<?= base_url('admin/deliverables/status/') ?>${id}`, {
      status,
      csrf_test_name: getCsrfToken()
    }, res => {
      hideLoader();
      if (res.status === 'success') {
        showToast(res.message, 'success');
        const badge = row.find('.deliverable-status-badge');
        badge.attr('class', 'badge bg-' + (statusColors[status] || 'secondary') + ' deliverable-status-badge');
        badge.text(status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
      } else {
        showToast(res.message || 'Failed to update status', 'error');
      }
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });
</script>
<?= $this->endSection() ?>
