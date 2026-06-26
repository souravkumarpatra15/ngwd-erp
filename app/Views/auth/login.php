<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Login' ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/ng-ui.css') ?>">

  <style>
    :root {
      --primary: #5b21b6;
      --primary-dark: #2e1065;
      --accent: #f59e0b;
      --text: #111827;
      --muted: #6b7280;
      --card: rgba(255, 255, 255, .92);
    }

    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      font-family: "Segoe UI", system-ui, sans-serif;
      display: flex;
      align-items: center;
      overflow-x: hidden;
      background:
        radial-gradient(circle at top left, rgba(245, 158, 11, .28), transparent 30%),
        radial-gradient(circle at bottom right, rgba(91, 33, 182, .45), transparent 35%),
        linear-gradient(135deg, #0f172a 0%, #312e81 45%, #581c87 100%);
      position: relative;
    }

    body::before,
    body::after {
      content: "";
      position: fixed;
      width: 360px;
      height: 360px;
      border-radius: 50%;
      filter: blur(8px);
      opacity: .35;
      z-index: 0;
    }

    body::before {
      background: #f59e0b;
      top: -120px;
      right: -100px;
    }

    body::after {
      background: #38bdf8;
      bottom: -140px;
      left: -120px;
    }

    .login-wrapper {
      position: relative;
      z-index: 2;
      width: 100%;
    }

    .brand-side {
      color: #fff;
      padding: 30px;
    }

    .brand-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .14);
      backdrop-filter: blur(14px);
      font-size: 13px;
      margin-bottom: 20px;
    }

    .brand-title {
      font-size: clamp(34px, 5vw, 54px);
      font-weight: 800;
      line-height: 1.05;
      margin-bottom: 18px;
    }

    .brand-title span {
      color: var(--accent);
    }

    .brand-text {
      color: rgba(255, 255, 255, .78);
      max-width: 460px;
      font-size: 16px;
      line-height: 1.7;
    }

    .feature-list {
      display: grid;
      gap: 12px;
      margin-top: 30px;
      max-width: 420px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 13px 15px;
      border-radius: 16px;
      background: rgba(255, 255, 255, .10);
      color: rgba(255, 255, 255, .9);
      backdrop-filter: blur(14px);
      font-size: 14px;
    }

    .feature-item i {
      color: var(--accent);
      font-size: 18px;
    }

    .login-card {
      border: 1px solid rgba(255, 255, 255, .35);
      border-radius: 28px;
      background: var(--card);
      backdrop-filter: blur(22px);
      box-shadow: 0 35px 90px rgba(0, 0, 0, .35);
      overflow: hidden;
    }

    .login-card::before {
      content: "";
      display: block;
      height: 6px;
      background: linear-gradient(90deg, var(--accent), #ec4899, #6366f1);
    }

    .card-body {
      padding: 42px;
    }

    .logo-box {
      width: 96px;
      height: 96px;
      border-radius: 24px;
      background: linear-gradient(145deg, #ffffff, #f3f4f6);
      box-shadow: 0 14px 35px rgba(91, 33, 182, .20);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
      padding: 12px;
    }

    .logo-box img {
      max-width: 100%;
      max-height: 72px;
      object-fit: contain;
    }

    .login-heading {
      font-weight: 800;
      color: var(--text);
      margin-bottom: 4px;
    }

    .login-subtitle {
      color: var(--muted);
      font-size: 14px;
    }

    .form-label {
      color: #374151;
      margin-bottom: 8px;
    }

    .input-group {
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 8px 22px rgba(17, 24, 39, .06);
    }

    .input-group-text {
      border: 1px solid #e5e7eb;
      border-right: 0;
      border-radius: 14px 0 0 14px;
      background: #fff;
      color: var(--primary);
      padding-left: 16px;
      padding-right: 14px;
    }

    .form-control {
      border: 1px solid #e5e7eb;
      border-left: 0;
      border-radius: 0 14px 14px 0;
      padding: 13px 15px;
      font-size: 14px;
      background: #fff;
    }

    .form-control:focus {
      border-color: #ddd6fe;
      box-shadow: none;
    }

    .input-group:focus-within {
      box-shadow: 0 0 0 .25rem rgba(91, 33, 182, .13);
    }

    #togglePwd {
      border-radius: 0 14px 14px 0;
      border-color: #e5e7eb;
      background: #fff;
    }

    #togglePwd :hover {
      color: #0f172a;
    }

    .btn-login {
      border: 0;
      border-radius: 16px;
      padding: 14px;
      font-weight: 700;
      font-size: 15px;
      background: linear-gradient(135deg, var(--primary), #7c3aed);
      box-shadow: 0 14px 30px rgba(91, 33, 182, .35);
      transition: .25s ease;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      box-shadow: 0 18px 38px rgba(91, 33, 182, .45);
    }

    .alert {
      border: 0;
      border-radius: 14px;
      font-size: 14px;
    }

    .footer-text {
      color: var(--muted);
      font-size: 13px;
    }

    @media (max-width: 991px) {
      .brand-side {
        text-align: center;
        padding: 20px 10px 35px;
      }

      .brand-text,
      .feature-list {
        margin-left: auto;
        margin-right: auto;
      }
    }

    @media (max-width: 575px) {
      body {
        align-items: flex-start;
        padding: 24px 0;
      }

      .card-body {
        padding: 30px 22px;
      }

      .brand-title {
        font-size: 32px;
      }

      .feature-list {
        display: none;
      }
    }
  </style>
</head>

<body>

  <div class="login-wrapper">
    <div class="container">
      <div class="row align-items-center justify-content-center g-4">

        <div class="col-lg-6 d-none d-lg-block">
          <div class="brand-side">

            <div class="brand-badge">
              <i class="bi bi-stars"></i>
              NGWebD Consulting Pvt. Ltd.
            </div>

            <h1 class="brand-title">
              Welcome to <span>NGWebD Portal</span>
            </h1>

            <p class="brand-text">
              A centralized workspace for managing projects, proposals,
              milestones, invoices, client communication and business operations.
            </p>

            <div class="feature-list">

              <div class="feature-item">
                <i class="bi bi-person-workspace"></i>
                Secure access for Administrators and Clients
              </div>

              <div class="feature-item">
                <i class="bi bi-kanban"></i>
                Track projects, milestones and work progress
              </div>

              <div class="feature-item">
                <i class="bi bi-receipt-cutoff"></i>
                View invoices, payments and agreements
              </div>

              <div class="feature-item">
                <i class="bi bi-chat-dots"></i>
                Stay connected with updates and notifications
              </div>

            </div>

          </div>
        </div>

        <div class="col-md-7 col-lg-5 col-xl-4">
          <div class="card login-card">
            <div class="card-body">

              <?php
              $logo = !empty($settings['company_logo'])
                ? $settings['company_logo']
                : 'assets/images/logo/logo.png';
              ?>

              <div class="text-center mb-4">
                <div class="logo-box">
                  <img src="<?= base_url($logo) ?>"
                    alt="<?= esc($settings['company_name'] ?? 'NGWebD ERP') ?>">
                </div>

                <h4 class="login-heading">
                  <?= esc($settings['company_name'] ?? 'NGWebD Portal') ?>
                </h4>

                <p class="login-subtitle mb-0">
                  Sign in to access your dashboard
                </p>
              </div>

              <form action="<?= base_url('login') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                  <label class="form-label fw-semibold small">Email Address</label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email"
                      name="email"
                      class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                      value="<?= old('email') ?>"
                      placeholder="admin@ngwebd.com"
                      required
                      autofocus>
                  </div>

                  <?php if (session('errors.email')): ?>
                    <div class="text-danger small mt-1"><?= session('errors.email') ?></div>
                  <?php endif; ?>
                </div>

                <div class="mb-1">
                  <label class="form-label fw-semibold small">Password</label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input type="password"
                      name="password"
                      id="pwdField"
                      class="form-control"
                      placeholder="••••••••"
                      required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                      <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                  </div>
                </div>

                <div class="d-flex justify-content-end mb-3">
                  <a href="<?= base_url('forgot-password') ?>"
                    class="small text-decoration-none">
                    Forgot Password?
                  </a>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                  <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
              </form>

              <hr class="my-4">

              <p class="text-center footer-text mb-0">
                NGWebD Consulting Pvt. Ltd. &copy; <?= date('Y') ?>
              </p>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    document.getElementById('togglePwd').addEventListener('click', function() {
      const f = document.getElementById('pwdField');
      const i = document.getElementById('eyeIcon');

      if (f.type === 'password') {
        f.type = 'text';
        i.className = 'bi bi-eye-slash';
      } else {
        f.type = 'password';
        i.className = 'bi bi-eye';
      }
    });
  </script>

  <?php
  $logo = !empty($settings['company_logo'])
    ? $settings['company_logo']
    : 'assets/images/logo/logo.png';
  ?>

  <div id="ngLoader">
    <div class="ng-loader-box">
      <div class="ng-loader-logo">
        <img src="<?= base_url($logo) ?>" alt="Logo">
      </div>
      <div class="ng-spinner"></div>
      <div class="ng-loader-text" id="ngLoaderText">Please wait...</div>
    </div>
  </div>

  <div id="ngToastContainer"></div>

  <script src="<?= base_url('assets/js/ng-ui.js') ?>"></script>

  <?php if (session()->getFlashdata('success')): ?>
    <script>
      showToast("<?= esc(session()->getFlashdata('success')) ?>", "success");
    </script>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <script>
      showToast("<?= esc(session()->getFlashdata('error')) ?>", "error");
    </script>
  <?php endif; ?>

  <?php if (session()->getFlashdata('warning')): ?>
    <script>
      showToast("<?= esc(session()->getFlashdata('warning')) ?>", "warning");
    </script>
  <?php endif; ?>

  <?php if (session()->getFlashdata('info')): ?>
    <script>
      showToast("<?= esc(session()->getFlashdata('info')) ?>", "info");
    </script>
  <?php endif; ?>

</body>

</html>