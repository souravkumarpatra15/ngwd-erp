<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h5 class="mb-1 fw-bold">Client Users</h5>
    <div class="text-muted small"><?= esc($client['name']) ?></div>
  </div>
  <a href="<?= base_url('admin/clients/' . $client['id']) ?>" class="btn btn-sm btn-outline-secondary">Back to Client</a>
</div>

<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach (session()->getFlashdata('errors') as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0">Add Portal User</h6></div>
      <div class="card-body">
        <form method="post" action="<?= base_url('admin/clients/' . $client['id'] . '/users/store') ?>">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label small fw-semibold">Name</label><input name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Password</label><input type="password" name="password" class="form-control" minlength="8" required></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Confirm Password</label><input type="password" name="password_confirm" class="form-control" minlength="8" required></div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Client Role</label>
            <select name="client_role" class="form-select" required>
              <option value="owner">Owner</option>
              <option value="manager">Manager</option>
              <option value="member" selected>Member</option>
              <option value="viewer">Viewer</option>
            </select>
          </div>
          <button class="btn btn-primary w-100">Add User</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0">Portal Users</h6>
        <div class="small text-muted">Manage access and client-side roles.</div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($users)): ?>
          <div class="text-center text-muted py-5">No client portal users found.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-4" style="min-width:260px">User</th>
                  <th style="min-width:130px">Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th class="text-end pe-4" style="min-width:170px">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td class="ps-4" colspan="5" style="padding:0">
                      <form method="post" action="<?= base_url('admin/clients/' . $client['id'] . '/users/' . $u['id'] . '/update') ?>" class="row g-2 align-items-center px-4 py-3 mb-0">
                        <?= csrf_field() ?>
                        <div class="col-12 col-xl-4">
                          <input name="name" class="form-control form-control-sm" value="<?= esc($u['name']) ?>" required>
                          <input type="email" name="email" class="form-control form-control-sm mt-1" value="<?= esc($u['email']) ?>" required>
                        </div>
                        <div class="col-12 col-xl-3">
                          <input type="password" name="password" class="form-control form-control-sm" placeholder="New password (optional)">
                          <input type="password" name="password_confirm" class="form-control form-control-sm mt-1" placeholder="Confirm new password">
                        </div>
                        <div class="col-6 col-xl-2">
                          <select name="client_role" class="form-select form-select-sm" required>
                            <option value="owner" <?= $u['client_role'] === 'owner' ? 'selected' : '' ?>>Owner</option>
                            <option value="manager" <?= $u['client_role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                            <option value="member" <?= $u['client_role'] === 'member' ? 'selected' : '' ?>>Member</option>
                            <option value="viewer" <?= $u['client_role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                          </select>
                        </div>
                        <div class="col-6 col-xl-1">
                          <span class="badge bg-<?= (int) $u['is_active'] ? 'success' : 'secondary' ?>"><?= (int) $u['is_active'] ? 'Active' : 'Inactive' ?></span>
                          <div class="text-muted mt-1" style="font-size:10px"><?= !empty($u['last_login']) ? esc($u['last_login']) : 'Never logged in' ?></div>
                        </div>
                        <div class="col-12 col-xl-2 text-xl-end">
                          <button class="btn btn-xs btn-outline-primary">Save</button>
                          <button type="button" class="btn btn-xs btn-outline-secondary btn-toggle-client-user" data-id="<?= $u['id'] ?>" data-active="<?= (int) $u['is_active'] ?>">
                            <?= (int) $u['is_active'] ? 'Disable' : 'Enable' ?>
                          </button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const clientId = <?= (int) $client['id'] ?>;

  $(document).on('click', '.btn-toggle-client-user', function () {
    const btn = $(this);
    const id = btn.data('id');
    showLoader('Updating status...');
    $.post(`<?= base_url('admin/clients/') ?>${clientId}/users/${id}/toggle`, {
      csrf_test_name: getCsrfToken()
    }, res => {
      hideLoader();
      if (res.status === 'success') {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 600);
      } else {
        showToast(res.message || 'Failed to update status', 'error');
      }
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });
</script>
<?= $this->endSection() ?>
