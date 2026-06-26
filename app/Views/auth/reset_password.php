<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — NGWebD ERP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Inter", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(124, 58, 237, .35), transparent 35%),
                linear-gradient(135deg, #0f172a, #1e1b4b);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .brand-box {
            text-align: center;
            color: #fff;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 78px;
            height: 78px;
            margin: 0 auto 14px;
            border-radius: 24px;
            background: rgba(255, 255, 255, .95);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .25);
        }

        .brand-logo img {
            max-width: 58px;
            max-height: 58px;
            object-fit: contain;
        }

        .brand-logo i {
            font-size: 34px;
            color: #2563eb;
        }

        .auth-card {
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(18px);
            border-radius: 26px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .35);
            overflow: hidden;
        }

        .auth-card-header {
            padding: 28px 28px 10px;
            text-align: center;
        }

        .auth-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 26px;
        }

        .auth-card-body {
            padding: 18px 28px 30px;
        }

        .form-label {
            color: #334155;
            font-size: 14px;
        }

        .input-box {
            position: relative;
        }

        .input-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            z-index: 2;
        }

        .form-control {
            height: 52px;
            border-radius: 15px;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            padding-left: 44px;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .15);
            background: #fff;
        }

        .btn-auth {
            height: 52px;
            border: 0;
            border-radius: 15px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            box-shadow: 0 14px 28px rgba(37, 99, 235, .32);
        }

        .btn-auth:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(37, 99, 235, .38);
        }

        .back-link {
            color: rgba(255, 255, 255, .82);
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            color: #fff;
        }

        .alert {
            border-radius: 15px;
            font-size: 14px;
        }

        @media(max-width: 480px) {
            .auth-card-header {
                padding: 24px 22px 8px;
            }

            .auth-card-body {
                padding: 16px 22px 26px;
            }

            .brand-logo {
                width: 68px;
                height: 68px;
            }
        }
    </style>
</head>

<body>

    <div class="auth-wrapper">

        <div class="brand-box">
            <div class="brand-logo">
                <img src="<?= base_url('assets/images/' . ($settings['company_logo'] ?? 'logo/logo.png')) ?>"
                    alt="<?= esc($settings['company_name'] ?? 'NGWebD ERP') ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <i class="bi bi-building" style="display:none;"></i>
            </div>

            <h4 class="fw-bold mb-1">NGWebD ERP</h4>
            <p class="mb-0 small opacity-75">Create a secure new password</p>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach (session()->getFlashdata('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="auth-card">
            <div class="auth-card-header">
                <div class="auth-icon">
                    <i class="bi bi-key"></i>
                </div>

                <h4 class="fw-bold mb-2">Reset Password</h4>
                <p class="text-muted small mb-0">
                    Enter your new password below. Use at least 8 characters.
                </p>
            </div>

            <div class="auth-card-body">
                <form action="<?= base_url("reset-password/{$token}") ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <div class="input-box">
                            <i class="bi bi-lock"></i>
                            <input type="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                placeholder="Minimum 8 characters"
                                required
                                autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-box">
                            <i class="bi bi-shield-check"></i>
                            <input type="password"
                                name="password_confirm"
                                class="form-control"
                                placeholder="Re-enter new password"
                                required
                                autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-auth w-100">
                        <i class="bi bi-check-circle me-1"></i>
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="<?= base_url('login') ?>" class="back-link">
                <i class="bi bi-arrow-left me-1"></i>
                Back to login
            </a>
        </div>

    </div>

</body>

</html>