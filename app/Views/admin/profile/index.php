<?php $this->extend('layouts/admin'); ?>
<?php $this->section('content'); ?>

<style>
    .profile-page {
        padding-bottom: 30px;
    }

    .profile-hero {
        background: linear-gradient(135deg, #0f172a, #1e40af, #7c3aed);
        border-radius: 26px;
        padding: 30px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .profile-hero::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .14);
        top: -80px;
        right: -70px;
    }

    .profile-hero::after {
        content: "";
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
        bottom: -60px;
        left: 40px;
    }

    .profile-hero-content {
        position: relative;
        z-index: 2;
    }

    .profile-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
        overflow: hidden;
        background: #fff;
    }

    .profile-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .profile-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .profile-card-body {
        padding: 24px;
    }

    .avatar-box {
        text-align: center;
        background: linear-gradient(180deg, #f8fafc, #eef2ff);
        border: 1px dashed #cbd5e1;
        border-radius: 22px;
        padding: 24px 18px;
        margin-bottom: 22px;
    }

    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .18);
    }

    .avatar-initial {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 800;
        border: 5px solid #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .18);
    }

    .form-label {
        color: #334155;
        font-size: 14px;
    }

    .form-control {
        height: 50px;
        border-radius: 15px;
        border: 1px solid #dbe3ef;
        background: #f8fafc;
    }

    .form-control:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .13);
    }

    .form-control:disabled {
        background: #eef2f7;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        z-index: 2;
    }

    .input-icon .form-control {
        padding-left: 44px;
    }

    .btn-gradient {
        height: 48px;
        border: 0;
        border-radius: 15px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        box-shadow: 0 12px 25px rgba(37, 99, 235, .28);
    }

    .btn-gradient:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-warning-soft {
        height: 48px;
        border: 0;
        border-radius: 15px;
        font-weight: 700;
        color: #7c2d12;
        background: linear-gradient(135deg, #facc15, #fb923c);
        box-shadow: 0 12px 25px rgba(251, 146, 60, .28);
    }

    .alert {
        border-radius: 16px;
    }

    .security-note {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 16px;
        padding: 14px 16px;
        font-size: 13px;
    }

    @media(max-width: 767px) {
        .profile-hero {
            padding: 24px;
            border-radius: 22px;
        }

        .profile-card-body {
            padding: 20px;
        }
    }
</style>

<div class="profile-page">

    <div class="profile-hero">
        <div class="profile-hero-content">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold mb-1"><?= esc($title) ?></h3>
                    <p class="mb-0 opacity-75">
                        Manage your personal information, avatar and account security.
                    </p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= base_url('writable/' . $user['avatar']) ?>"
                            class="profile-avatar"
                            alt="Profile Avatar">
                    <?php else: ?>
                        <div class="avatar-initial">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h5 class="fw-bold mb-0"><?= esc($user['name']) ?></h5>
                        <small class="opacity-75"><?= esc($user['email']) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-card-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Profile Information</h6>
                        <small class="text-muted">Update your account details</small>
                    </div>
                </div>

                <div class="profile-card-body">
                    <form action="<?= base_url('admin/profile/update') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="avatar-box">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= base_url('writable/' . $user['avatar']) ?>"
                                    class="profile-avatar mb-3"
                                    alt="Profile Avatar">
                            <?php else: ?>
                                <div class="avatar-initial mb-3">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <label class="form-label fw-semibold d-block">Change Avatar</label>
                            <input type="file"
                                name="avatar"
                                class="form-control form-control-sm"
                                accept="image/*">
                            <small class="text-muted d-block mt-2">JPG, PNG or WebP image supported.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <div class="input-icon">
                                <i class="fa fa-user"></i>
                                <input type="text"
                                    name="name"
                                    class="form-control"
                                    value="<?= esc($user['name']) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-icon">
                                <i class="fa fa-envelope"></i>
                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= esc($user['email']) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Role</label>
                            <div class="input-icon">
                                <i class="fa fa-shield-halved"></i>
                                <input type="text"
                                    class="form-control"
                                    value="<?= ucfirst(str_replace('_', ' ', $user['role'])) ?>"
                                    disabled>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gradient px-4">
                            <i class="fa fa-save me-1"></i>
                            Save Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-card-icon">
                        <i class="fa fa-lock"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Change Password</h6>
                        <small class="text-muted">Keep your account secure</small>
                    </div>
                </div>

                <div class="profile-card-body">
                    <div class="security-note mb-4">
                        <i class="fa fa-circle-info me-1"></i>
                        Use a strong password with at least 8 characters.
                    </div>

                    <form action="<?= base_url('admin/profile/change-password') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <div class="input-icon">
                                <i class="fa fa-key"></i>
                                <input type="password"
                                    name="current_password"
                                    class="form-control"
                                    required
                                    autocomplete="current-password">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <div class="input-icon">
                                <i class="fa fa-lock"></i>
                                <input type="password"
                                    name="new_password"
                                    class="form-control"
                                    minlength="8"
                                    required
                                    autocomplete="new-password">
                            </div>
                            <div class="form-text">At least 8 characters.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <div class="input-icon">
                                <i class="fa fa-shield-alt"></i>
                                <input type="password"
                                    name="confirm_password"
                                    class="form-control"
                                    required
                                    autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning-soft px-4">
                            <i class="fa fa-lock me-1"></i>
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<?php $this->endSection(); ?>