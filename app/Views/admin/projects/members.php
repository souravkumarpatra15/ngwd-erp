<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h5 class="mb-1 fw-bold">Project Team</h5>
    <div class="text-muted small"><?= esc($project['name']) ?></div>
  </div>
  <a href="<?= base_url('admin/projects/' . $project['id']) ?>" class="btn btn-sm btn-outline-secondary">Back to Project</a>
</div>

<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0">Add Team Member</h6></div>
      <div class="card-body">
        <form method="post" action="<?= base_url('admin/projects/' . $project['id'] . '/members/store') ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Internal User</label>
            <select name="user_id" class="form-select" required>
              <option value="">Select user</option>
              <?php foreach ($availableUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> — <?= esc($u['role']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Project Role</label>
            <select name="project_role" class="form-select">
              <option value="project_manager">Project Manager</option>
              <option value="developer" selected>Developer</option>
              <option value="designer">Designer</option>
              <option value="qa">QA</option>
              <option value="member">Member</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Access</label>
            <select name="access_level" class="form-select">
              <option value="view">View</option>
              <option value="edit" selected>Edit</option>
              <option value="manage">Manage</option>
            </select>
          </div>
          <button class="btn btn-primary w-100">Add Member</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0">Current Team</h6></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4">Member</th>
                <th>Role &amp; Access</th>
                <th>Status</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($members)): ?>
                <tr><td colspan="4" class="text-center text-muted py-5">No project members yet.</td></tr>
              <?php else: ?>
                <?php foreach ($members as $m): ?>
                  <tr data-member-row="<?= $m['id'] ?>">
                    <td class="ps-4">
                      <div class="fw-semibold small"><?= esc($m['name']) ?></div>
                      <div class="text-muted" style="font-size:11px"><?= esc($m['email']) ?></div>
                    </td>
                    <td>
                      <div class="d-flex gap-2 align-items-center flex-wrap">
                        <select class="form-select form-select-sm member-role-select" style="min-width:145px">
                          <?php foreach (['project_manager' => 'Project Manager', 'developer' => 'Developer', 'designer' => 'Designer', 'qa' => 'QA', 'member' => 'Member'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $m['project_role'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                          <?php endforeach; ?>
                        </select>
                        <select class="form-select form-select-sm member-access-select" style="min-width:100px">
                          <?php foreach (['view' => 'View', 'edit' => 'Edit', 'manage' => 'Manage'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $m['access_level'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td class="text-end pe-4">
                      <button type="button" class="btn btn-xs btn-outline-primary me-1 btn-save-member" data-id="<?= $m['id'] ?>">Save</button>
                      <button type="button" class="btn btn-xs btn-outline-danger btn-remove-member" data-id="<?= $m['id'] ?>" data-name="<?= esc($m['name'], 'attr') ?>">Remove</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const projectId = <?= (int) $project['id'] ?>;

  $(document).on('click', '.btn-save-member', function () {
    const id = $(this).data('id');
    const row = $(`tr[data-member-row="${id}"]`);
    const role = row.find('.member-role-select').val();
    const access = row.find('.member-access-select').val();

    showLoader('Saving member...');
    $.post(`<?= base_url('admin/projects/') ?>${projectId}/members/${id}/update`, {
      project_role: role,
      access_level: access,
      csrf_test_name: getCsrfToken()
    }, res => {
      hideLoader();
      if (res.status === 'success') showToast(res.message, 'success');
      else showToast(res.message || 'Failed to update member', 'error');
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });

  let removeMemberId = null;
  $(document).on('click', '.btn-remove-member', function () {
    removeMemberId = $(this).data('id');
    $('#ngConfirmTitle').text('Remove Team Member?');
    $('#ngConfirmMessage').text(`Remove ${$(this).data('name')} from this project's team?`);
    $('#ngConfirmYes').text('Yes, Remove');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  });
  $('#ngConfirmYes').off('click.member').on('click.member', function () {
    if (!removeMemberId) return;
    const id = removeMemberId;
    bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal'))?.hide();
    showLoader('Removing member...');
    $.post(`<?= base_url('admin/projects/') ?>${projectId}/members/${id}/delete`, {
      csrf_test_name: getCsrfToken()
    }, res => {
      hideLoader();
      if (res.status === 'success') {
        showToast(res.message, 'success');
        $(`tr[data-member-row="${id}"]`).fadeOut(200, function () { $(this).remove(); });
      } else {
        showToast(res.message || 'Failed to remove member', 'error');
      }
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
    removeMemberId = null;
  });
</script>
<?= $this->endSection() ?>
