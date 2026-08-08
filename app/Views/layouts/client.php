<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? 'Client Portal' ?> — NGWebD</title>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/ng-ui.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  /* Client portal design tokens — deliberately distinct from the admin panel's Bootstrap-blue default */
  --ink: #14151f;
  --ink-soft: #5b5f76;
  --signal: #5b5bf5;
  --signal-dark: #4747d1;
  --signal-rgb: 91, 91, 245;
  --paper: #f6f6fb;
  --line: #e7e7f2;

  --bs-primary: var(--signal);
  --bs-primary-rgb: var(--signal-rgb);
  --bs-link-color: var(--signal);
  --bs-link-hover-color: var(--signal-dark);
  --bs-card-border-radius: 14px;
  --bs-border-radius: .6rem;
  --bs-box-shadow-sm: 0 1px 2px rgba(20,21,31,.04), 0 8px 20px -12px rgba(20,21,31,.10);
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--paper) !important;
  color: var(--ink);
}
h1, h2, h3, h4, h5, h6, .navbar-brand, .fw-bold, strong { font-family: 'Sora', 'Inter', sans-serif; }

.btn-primary { background: var(--signal); border-color: var(--signal); }
.btn-primary:hover, .btn-primary:focus { background: var(--signal-dark); border-color: var(--signal-dark); }
.btn-outline-primary { color: var(--signal); border-color: var(--signal); }
.btn-outline-primary:hover { background: var(--signal); border-color: var(--signal); }
.text-primary { color: var(--signal) !important; }
.bg-primary { background-color: var(--signal) !important; }
.bg-primary-subtle { background-color: rgba(var(--signal-rgb), .12) !important; }
.border-primary-subtle { border-color: rgba(var(--signal-rgb), .3) !important; }

.card { border: 1px solid var(--line); }
.badge { border-radius: 50rem; font-weight: 600; letter-spacing: .2px; padding: .4em .75em; }
.progress { background: var(--line); border-radius: 50rem; }
.progress-bar { border-radius: 50rem; }

.portal-sidebar { width:240px;min-width:240px;min-height:100vh;background:var(--ink); }
.portal-brand { padding: 22px 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,.08); }
.portal-brand img { height: 28px; width: auto; border-radius: 4px; }
.portal-brand .name { color: #fff; font-family: 'Sora', sans-serif; font-weight: 600; font-size: 14px; line-height: 1.2; }
.portal-brand .sub { color: rgba(255,255,255,.45); font-size: 11px; letter-spacing: .3px; text-transform: uppercase; }
.portal-link { display:flex;align-items:center;gap:10px;padding:10px 20px;margin:1px 10px;border-radius:8px;color:rgba(255,255,255,.6);text-decoration:none;font-size:14px;font-weight:500;transition:.15s;border-left:3px solid transparent; }
.portal-link:hover { background:rgba(255,255,255,.06);color:#fff; }
.portal-link.active { background:rgba(var(--signal-rgb),.18);color:#fff;border-left-color:var(--signal); }
.portal-link i { width:18px;font-size:15px; }
#portalSidebarOpen { display:none; }

.portal-topbar { backdrop-filter: blur(6px); }

@media (max-width: 768px) {
  .portal-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1040;
    transform: translateX(-100%);
    transition: .3s ease;
    overflow-y: auto;
  }
  .portal-sidebar.mobile-show { transform: translateX(0); }
  #portalSidebarOpen { display: inline-flex; }
  .portal-topbar .navbar-brand { font-size: 15px; }
  .portal-backdrop {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1039;
  }
  .portal-backdrop.show { display: block; }
}
</style>
</head>
<body class="bg-light">
<div class="d-flex">
  <nav class="portal-sidebar d-flex flex-column flex-shrink-0" id="portalSidebar">
    <div class="portal-brand">
      <?php $portalLogo = !empty($settings['company_logo']) ? base_url($settings['company_logo']) : base_url('assets/images/logo/logo.png'); ?>
      <img src="<?= $portalLogo ?>" alt="<?= esc($settings['company_name'] ?? '') ?>" onerror="this.style.display='none'">
      <div>
        <div class="name"><?= esc($settings['company_name'] ?? 'NGWebD') ?></div>
        <div class="sub">Client Portal</div>
      </div>
    </div>
    <div class="flex-grow-1 py-2">
      <a href="<?= base_url('portal/dashboard') ?>" class="portal-link <?= isActive('portal/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="<?= base_url('portal/projects') ?>" class="portal-link <?= isActive('portal/projects') ?>"><i class="bi bi-folder2-open"></i> Projects</a>
      <a href="<?= base_url('portal/invoices') ?>" class="portal-link <?= isActive('portal/invoices') ?>"><i class="bi bi-receipt"></i> Invoices</a>
      <a href="<?= base_url('portal/payments') ?>" class="portal-link <?= isActive('portal/payments') ?>"><i class="bi bi-cash-stack"></i> Payments</a>
      <a href="<?= base_url('portal/proposals') ?>" class="portal-link <?= isActive('portal/proposals') ?>"><i class="bi bi-file-earmark-text"></i> Proposals</a>
      <a href="<?= base_url('portal/agreements') ?>" class="portal-link <?= isActive('portal/agreements') ?>"><i class="bi bi-file-earmark-check"></i> Agreements</a>
      <a href="<?= base_url('portal/documents') ?>" class="portal-link <?= isActive('portal/documents') ?>"><i class="bi bi-folder"></i> Documents</a>
      <a href="<?= base_url('portal/tickets') ?>" class="portal-link <?= isActive('portal/tickets') ?>"><i class="bi bi-headset"></i> Support</a>
    </div>
    <div class="p-3 border-top border-white border-opacity-25">
      <a href="<?= base_url('logout') ?>" class="portal-link" style="color:rgba(255,255,255,.6)"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </nav>
  <div class="portal-backdrop" id="portalBackdrop"></div>

  <div class="flex-grow-1 d-flex flex-column" style="min-width:0">
    <nav class="navbar bg-white border-bottom px-3 px-md-4 py-2 portal-topbar">
      <button class="btn btn-sm btn-outline-secondary me-2" id="portalSidebarOpen" type="button"><i class="bi bi-list fs-5"></i></button>
      <span class="navbar-brand mb-0 h6 text-truncate"><?= $title ?? '' ?></span>
      <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
        <span class="small text-muted d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i><?= esc($current_user['name'] ?? '') ?></span>
        <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline">Logout</span></a>
      </div>
    </nav>

    <div class="p-3 p-md-4 flex-grow-1">
      <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
        <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      <?= $this->renderSection('content') ?>
    </div>

    <footer class="border-top bg-white px-4 py-2 text-center text-muted small">
      &copy; <?= date('Y') ?> <?= esc($settings['company_name'] ?? 'NGWebD Consulting') ?>. All rights reserved.
    </footer>
  </div>
</div>
<?php
  $logo = !empty($settings['company_logo'])
    ? $settings['company_logo']
    : 'assets/images/logo/logo.png';
  ?>

  <div id="ngLoader">

    <div class="ng-loader-box">

      <div class="ng-loader-logo">
        <img src="<?= base_url($logo) ?>">
      </div>

      <div class="ng-loader-text" id="ngLoaderText">
        Loading...
      </div>

      <div class="ng-progress">
        <span></span>
      </div>

      <div class="ng-loader-dots">
        <span></span>
        <span></span>
        <span></span>
      </div>

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
  <div class="modal fade" id="ngConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content ng-confirm-modal">
        <div class="ng-confirm-icon"><i class="bi bi-question-circle-fill"></i></div>
        <div class="modal-body text-center px-4 pb-4">
          <h5 class="fw-bold mb-2" id="ngConfirmTitle">Are you sure?</h5>
          <p class="text-muted mb-4" id="ngConfirmMessage">This action cannot be undone.</p>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary px-4" id="ngConfirmYes">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    const sidebar = document.getElementById('portalSidebar');
    const backdrop = document.getElementById('portalBackdrop');
    function openSidebar() { sidebar?.classList.add('mobile-show'); backdrop?.classList.add('show'); }
    function closeSidebar() { sidebar?.classList.remove('mobile-show'); backdrop?.classList.remove('show'); }
    document.getElementById('portalSidebarOpen')?.addEventListener('click', () => {
      sidebar?.classList.contains('mobile-show') ? closeSidebar() : openSidebar();
    });
    backdrop?.addEventListener('click', closeSidebar);
    document.querySelectorAll('.portal-link').forEach(a => a.addEventListener('click', closeSidebar));
  })();
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
