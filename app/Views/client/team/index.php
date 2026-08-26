<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h5 class="mb-1 fw-bold">My Team</h5>
    <div class="text-muted small">Manage who from your organization can access this portal.</div>
  </div>
  <?php if ($canManage): ?>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTeamMemberModal"><i class="bi bi-plus-lg me-1"></i>Add Member</button>
  <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach (session()->getFlashdata('errors') as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<?php if (!$canManage): ?>
  <div class="alert alert-secondary border-0"><i class="bi bi-info-circle me-2"></i>You have view-only access to the team list. Only the account Owner can add or edit members.</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th class="ps-4">Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <?php if ($canManage): ?><th class="pe-4 text-end">Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="<?= $canManage ? 5 : 4 ?>" class="text-center text-muted py-5">No team members yet.</td></tr>
          <?php else: ?>
            <?php $roleColors = ['owner' => 'primary', 'manager' => 'info', 'member' => 'secondary', 'viewer' => 'light']; ?>
            <?php foreach ($users as $u): ?>
              <tr>
                <td class="ps-4 fw-semibold small"><?= esc($u['name']) ?></td>
                <td class="small text-muted"><?= esc($u['email']) ?></td>
                <td><span class="badge bg-<?= $roleColors[$u['client_role']] ?? 'secondary' ?> <?= $u['client_role']==='viewer'?'text-dark border':'' ?>"><?= ucfirst($u['client_role']) ?></span></td>
                <td><span class="badge bg-<?= (int) $u['is_active'] ? 'success' : 'secondary' ?>"><?= (int) $u['is_active'] ? 'Active' : 'Disabled' ?></span></td>
                <?php if ($canManage): ?>
                <td class="pe-4 text-end">
                  <button type="button" class="btn btn-xs btn-outline-warning btn-edit-team-member"
                    data-id="<?= $u['id'] ?>" data-name="<?= esc($u['name'], 'attr') ?>" data-email="<?= esc($u['email'], 'attr') ?>" data-role="<?= esc($u['client_role'], 'attr') ?>">Edit</button>
                  <button type="button" class="btn btn-xs btn-outline-secondary btn-toggle-team-member" data-id="<?= $u['id'] ?>">
                    <?= (int) $u['is_active'] ? 'Disable' : 'Enable' ?>
                  </button>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($canManage): ?>
<!-- Add Member Modal -->
<div class="modal fade" id="addTeamMemberModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-semibold">Add Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="<?= base_url('portal/team/store') ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label small fw-semibold">Name</label><input name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Password</label><input type="password" name="password" class="form-control" minlength="8" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Confirm Password</label><input type="password" name="password_confirm" class="form-control" minlength="8" required></div>
          <div class="mb-1">
            <label class="form-label small fw-semibold">Role</label>
            <select name="client_role" class="form-select" required>
              <option value="viewer">Viewer — view only</option>
              <option value="member" selected>Member — can comment &amp; upload</option>
              <option value="manager">Manager — can also approve deliverables</option>
              <option value="owner">Owner — full account control</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Member</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editTeamMemberModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-semibold">Edit Team Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" id="editTeamMemberForm">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label small fw-semibold">Name</label><input name="name" id="editTmName" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" id="editTmEmail" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">New Password <span class="text-muted fw-normal">(optional)</span></label><input type="password" name="password" class="form-control" minlength="8"></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Confirm New Password</label><input type="password" name="password_confirm" class="form-control" minlength="8"></div>
          <div class="mb-1">
            <label class="form-label small fw-semibold">Role</label>
            <select name="client_role" id="editTmRole" class="form-select" required>
              <option value="viewer">Viewer — view only</option>
              <option value="member">Member — can comment &amp; upload</option>
              <option value="manager">Manager — can also approve deliverables</option>
              <option value="owner">Owner — full account control</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?php if ($canManage): ?>
<?= $this->section('scripts') ?>
<script>
  $(document).on('click', '.btn-edit-team-member', function () {
    const id = $(this).data('id');
    $('#editTeamMemberForm').attr('action', `<?= base_url('portal/team/') ?>${id}/update`);
    $('#editTmName').val($(this).data('name'));
    $('#editTmEmail').val($(this).data('email'));
    $('#editTmRole').val($(this).data('role'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editTeamMemberModal')).show();
  });

  $(document).on('click', '.btn-toggle-team-member', function () {
    const id = $(this).data('id');
    showLoader('Updating status...');
    $.post(`<?= base_url('portal/team/') ?>${id}/toggle`, { csrf_test_name: getCsrfToken() }, res => {
      hideLoader();
      if (res.status === 'success') { showToast(res.message, 'success'); setTimeout(() => location.reload(), 600); }
      else showToast(res.message || 'Failed to update status', 'error');
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });
</script>
<?= $this->endSection() ?>
<?php endif; ?>
