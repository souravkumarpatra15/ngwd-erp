<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $colors = ['draft'=>'secondary','in_progress'=>'primary','submitted'=>'info','under_review'=>'warning','changes_requested'=>'danger','approved'=>'success','rejected'=>'dark']; ?>
<?php $actionColors = ['approved'=>'success','changes_requested'=>'danger','submitted'=>'info']; ?>

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
  <div>
    <a href="<?= base_url('admin/deliverables?project_id='.$deliverable['project_id']) ?>" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Deliverables</a>
    <h5 class="mb-1 fw-bold mt-2"><?= esc($deliverable['title']) ?></h5>
    <div class="text-muted small">
      <?= esc($deliverable['project_name'] ?? '—') ?>
      <?php if (!empty($deliverable['milestone_title'])): ?> · Milestone: <?= esc($deliverable['milestone_title']) ?><?php endif; ?>
      · v<?= esc($deliverable['version']) ?>
    </div>
  </div>
  <div class="text-end">
    <span class="badge bg-<?= $colors[$deliverable['status']] ?? 'secondary' ?> fs-6 mb-2 d-inline-block" id="dStatusBadge"><?= ucwords(str_replace('_',' ',$deliverable['status'])) ?></span>
    <?php if (!empty($canManage)): ?>
    <div>
      <select class="form-select form-select-sm d-inline-block" id="dStatusSelect" style="width:180px">
        <?php foreach ($colors as $s => $c): ?><option value="<?= $s ?>" <?= $deliverable['status']===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($deliverable['status'] === 'changes_requested'): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
  <i class="bi bi-exclamation-triangle-fill fs-5"></i>
  <div><strong>The client requested changes on this deliverable.</strong> See their feedback in the timeline below.</div>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Details</h6></div>
      <div class="card-body">
        <?php if (!empty($deliverable['description'])): ?>
        <div class="mb-3 small"><?= nl2br(esc($deliverable['description'])) ?></div>
        <?php else: ?>
        <div class="text-muted small fst-italic mb-3">No description provided.</div>
        <?php endif; ?>
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted small" style="width:140px">Owner</td><td class="small fw-semibold"><?= esc($deliverable['owner_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted small">Due Date</td><td class="small"><?= $deliverable['due_date'] ? date('d M Y', strtotime($deliverable['due_date'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Submitted</td><td class="small"><?= $deliverable['submitted_at'] ? date('d M Y, h:i A', strtotime($deliverable['submitted_at'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Last Reviewed</td><td class="small"><?= $deliverable['reviewed_at'] ? date('d M Y, h:i A', strtotime($deliverable['reviewed_at'])) : '—' ?></td></tr>
          <tr><td class="text-muted small">Approved</td><td class="small"><?= $deliverable['approved_at'] ? date('d M Y, h:i A', strtotime($deliverable['approved_at'])).' by '.esc($deliverable['approved_by_name'] ?? '—') : '—' ?></td></tr>
        </table>
      </div>
    </div>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-paperclip me-2 text-success"></i>Files <span class="text-muted fw-normal small">· Images · Videos · PDF · Word · Excel · PPT · ZIP</span></h6>
        <?php if (!empty($canManage)): ?><label class="btn btn-sm btn-outline-primary mb-0"><i class="bi bi-upload me-1"></i>Upload<input type="file" id="deliverableFileInput" class="d-none" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.zip,.png,.jpg,.jpeg,.gif,.webp,.mp4,.mov,.avi,.webm,.mkv"></label><?php endif; ?>
      </div>
      <div class="card-body">
        <div id="deliverableFilesGrid" class="row g-2">
          <?php $files = $files ?? []; ?>
          <?php foreach ($files as $f): ?>
            <?php $fExt = strtolower(pathinfo($f['original_name'] ?? '', PATHINFO_EXTENSION)); $fIsVideo = !empty($f['is_video']) || in_array($fExt, ['mp4','mov','avi','webm','mkv'], true); ?>
            <div class="col-6 col-md-4" data-deliverable-file="<?= $f['id'] ?>">
              <?php if (!empty($f['is_image'])): ?>
                <a href="<?= base_url('admin/deliverables/files/'.$f['id']) ?>" target="_blank"><img src="<?= base_url('admin/deliverables/files/'.$f['id']) ?>" class="img-fluid rounded border w-100" style="aspect-ratio:16/9;object-fit:cover" loading="lazy"></a>
              <?php elseif ($fIsVideo): ?>
                <video src="<?= base_url('admin/deliverables/files/'.$f['id']) ?>" class="rounded border w-100" style="aspect-ratio:16/9;object-fit:cover;background:#000" controls preload="metadata"></video>
              <?php else: ?>
                <a href="<?= base_url('admin/deliverables/files/'.$f['id']) ?>" class="d-flex flex-column align-items-center justify-content-center border rounded text-decoration-none text-dark p-2" style="aspect-ratio:16/9"><i class="bi bi-file-earmark-text fs-2 text-muted"></i><span class="small text-truncate w-100 text-center"><?= esc($f['original_name']) ?></span></a>
              <?php endif; ?>
              <div class="mt-1"><div class="small text-truncate" title="<?= esc($f['original_name']) ?>"><?= esc($f['original_name']) ?></div>
              <div class="d-flex justify-content-between align-items-center"><span class="text-muted" style="font-size:10px"><?= esc($f['uploader_name'] ?? '') ?></span><span class="d-flex gap-1"><a href="<?= base_url('admin/deliverables/files/'.$f['id']) ?>" class="btn btn-xs text-primary p-0" <?= ($fIsVideo || !empty($f['is_image'])) ? 'target="_blank"' : '' ?> title="View / Download"><i class="bi bi-download"></i></a><?php if (!empty($canManage)): ?><button type="button" class="btn btn-xs text-danger p-0 btn-del-dfile" data-id="<?= $f['id'] ?>" title="Remove"><i class="bi bi-trash"></i></button><?php endif; ?></span></div></div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($files)): ?><div class="text-muted small px-2" id="noDeliverableFilesMsg">No files yet — upload videos, images, PDFs, Word/Excel docs, or ZIPs (max 100MB each).</div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-chat-square-text me-2 text-info"></i>Client Feedback &amp; Review History</h6></div>
      <div class="card-body p-0" style="max-height:520px;overflow-y:auto">
        <?php if (empty($history)): ?>
        <div class="text-center text-muted py-5 small"><i class="bi bi-chat-square-text fs-3 d-block mb-2 opacity-25"></i>No client feedback yet.<br>This shows up as soon as the client approves or requests changes from their portal.</div>
        <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($history as $h): ?>
          <div class="list-group-item px-4 py-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <span class="badge bg-<?= $actionColors[$h['action']] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$h['action'])) ?></span>
              <span class="text-muted" style="font-size:11px"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></span>
            </div>
            <div class="small fw-semibold mb-1"><?= esc($h['user_name'] ?? 'Client') ?></div>
            <?php if (!empty($h['comment'])): ?>
            <div class="small text-muted"><?= nl2br(esc($h['comment'])) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?php if (!empty($canManage)): ?>
<?= $this->section('scripts') ?>
<script>
$('#dStatusSelect').on('change', function () {
  const status = $(this).val();
  const statusColors = <?= json_encode($colors) ?>;
  showLoader('Updating status...');
  $.post(`<?= base_url('admin/deliverables/status/'.$deliverable['id']) ?>`, { status, csrf_test_name: getCsrfToken() }, res => {
    hideLoader();
    if (res.status === 'success') {
      showToast(res.message, 'success');
      const badge = $('#dStatusBadge');
      badge.attr('class', 'badge bg-' + (statusColors[status] || 'secondary') + ' fs-6 mb-2 d-inline-block');
      badge.text(status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
    } else {
      showToast(res.message || 'Failed to update status', 'error');
    }
  }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
});
$('#deliverableFileInput').on('change', function () {
  if (!this.files.length) return;
  const fd = new FormData();
  for (const f of this.files) fd.append('files[]', f);
  fd.append('csrf_test_name', getCsrfToken());
  showLoader(this.files.length > 1 ? `Uploading ${this.files.length} files...` : 'Uploading...');
  $.ajax({ url: `<?= base_url('admin/deliverables/'.$deliverable['id'].'/files') ?>`, method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
    .done(r => { hideLoader(); showToast(r.message, r.status); if (r.status === 'success') location.reload(); })
    .fail(() => { hideLoader(); showToast('Upload failed.', 'error'); });
  this.value = '';
});
let delDFileId = null;
$(document).on('click', '.btn-del-dfile', function () { delDFileId = $(this).data('id'); bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show(); });
$('#ngConfirmYes').off('click.dfile').on('click.dfile', function () {
  if (!delDFileId) return;
  bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal'))?.hide();
  showLoader('Removing...');
  $.post(`<?= base_url('admin/deliverables/files/') ?>${delDFileId}/delete`, { csrf_test_name: getCsrfToken() }, r => { hideLoader(); showToast(r.message, r.status); if (r.status === 'success') $(`[data-deliverable-file="${delDFileId}"]`).fadeOut(200, function () { $(this).remove(); }); }, 'json');
  delDFileId = null;
});
</script>
<?= $this->endSection() ?>
<?php endif; ?>
