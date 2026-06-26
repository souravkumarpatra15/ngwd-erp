<?php $this->extend('layouts/admin'); $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url("admin/users/update/{$user['id']}") ?>" method="POST">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= esc($user['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= esc($user['email']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <?php foreach ($roles as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $user['role'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password"
                           placeholder="Leave blank to keep current">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="password_confirm" class="form-control" autocomplete="new-password"
                           placeholder="Leave blank to keep current">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
