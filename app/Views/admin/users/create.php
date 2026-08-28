<?php $this->extend('layouts/admin'); $this->section('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('admin/users/store') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select name="role" id="roleSelect" class="form-select" required onchange="document.getElementById('deptWrap').style.display=this.value==='staff'?'block':'none'">
                        <?php foreach ($roles as $val => $label): ?>
                        <option value="<?= $val ?>" <?= old('role') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4" id="deptWrap" style="display:<?= old('role')==='staff'?'block':'none' ?>">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department" class="form-select">
                        <option value="">Select department</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d ?>" <?= old('department')===$d?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$d)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Controls what a Staff user can see by default (e.g. Developer never sees pricing).</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" minlength="8" required autocomplete="new-password">
                    <div class="form-text">Minimum 8 characters.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirm" class="form-control" required autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
