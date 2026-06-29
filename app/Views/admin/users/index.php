<?php $this->extend('layouts/admin');
$this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add User
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php if ($user['avatar']): ?>
                                    <img src="<?= base_url('writable/' . $user['avatar']) ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover">
                                <?php else: ?>
                                    <span class="avatar-placeholder me-2 bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                                <?= esc($user['name']) ?>
                                <?php if ((int)$user['id'] === (int)session()->get('user_id')): ?>
                                    <span class="badge bg-secondary ms-1">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($user['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $user['role'] === 'superadmin' ? 'danger' : ($user['role'] === 'admin' ? 'primary' : 'secondary') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'warning' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= $user['last_login'] ? date('d M Y, H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                            <td class="text-end">
                                <a href="<?= base_url("admin/users/edit/{$user['id']}") ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                <?php if ((int)$user['id'] !== (int)session()->get('user_id')): ?>
                                    <button class="btn btn-outline-<?= $user['is_active'] ? 'warning' : 'success' ?> btn-sm btn-toggle-active"
                                        data-id="<?= $user['id'] ?>">
                                        <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm btn-del-user"
                                        data-id="<?= $user['id'] ?>"
                                        data-confirm-title="Delete User?"
                                        data-confirm="Delete '<?= esc($user['name']) ?>'? This cannot be undone."
                                        data-confirm-yes="Yes, Delete">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    const BASE = '<?= base_url() ?>';

    $(document).on('click', '.btn-toggle-active', function() {
        const id = $(this).data('id');
        showLoader('Updating...');
        $.post(`${BASE}admin/users/toggle-active/${id}`, {
            csrf_test_name: CSRF_TOKEN
        }, d => {
            hideLoader();
            if (d.status === 'success') location.reload();
            else showToast(d.message, 'error');
        }, 'json');
    });

    let delUserId = null;
    $(document).on('click', '.btn-del-user', function() {
        delUserId = $(this).data('id');
        $('#ngConfirmTitle').text($(this).data('confirm-title'));
        $('#ngConfirmMessage').text($(this).data('confirm'));
        $('#ngConfirmYes').text($(this).data('confirm-yes'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
    });
    $('#ngConfirmYes').off('click').on('click', function() {
        if (!delUserId) return;
        bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal')).hide();
        showLoader('Deleting...');
        $.post(`${BASE}admin/users/delete/${delUserId}`, {
            csrf_test_name: CSRF_TOKEN
        }, d => {
            hideLoader();
            if (d.status === 'success') location.reload();
            else showToast(d.message, 'error');
        }, 'json');
        delUserId = null;
    });
</script>
<?= $this->endSection() ?>