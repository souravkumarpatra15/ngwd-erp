<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h5 class="mb-1 fw-bold">Module Permissions</h5>
    <div class="text-muted small"><?= esc($user['name']) ?> — <?= esc($user['email']) ?> <span class="badge bg-secondary ms-1"><?= esc(ucfirst($user['role'])) ?></span><?php if (!empty($user['department'])): ?> <span class="badge bg-info text-dark"><?= esc(ucfirst(str_replace('_',' ',$user['department']))) ?></span><?php endif; ?></div>
  </div>
  <a href="<?= base_url('admin/users') ?>" class="btn btn-sm btn-outline-secondary">Back to Users</a>
</div>

<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

<div class="alert alert-info border-0">
  <i class="bi bi-info-circle me-2"></i>
  Checking a box here always overrides this user's default access for that module — it works both ways: you can grant a Staff user access to something outside their normal PMS view, or restrict a Manager/Client Owner below their usual default. Leaving every box in a row unchecked falls back to their role's normal default.
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <form method="post" action="<?= base_url('admin/users/' . $user['id'] . '/permissions/update') ?>">
      <?= csrf_field() ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4">Module</th>
              <th class="text-center">View</th>
              <th class="text-center">Create</th>
              <th class="text-center">Edit</th>
              <th class="text-center pe-4">Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($modules as $key => $label): $row = $current[$key] ?? null; ?>
              <tr>
                <td class="ps-4 fw-semibold small"><?= esc($label) ?></td>
                <?php foreach (['view', 'create', 'edit', 'delete'] as $action): ?>
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input" name="perms[<?= $key ?>][<?= $action ?>]" value="1" <?= !empty($row['can_' . $action]) ? 'checked' : '' ?>>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="p-3 border-top text-end"><button class="btn btn-primary">Save Permissions</button></div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
