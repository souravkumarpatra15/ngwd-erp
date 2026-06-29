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
<style>
.portal-sidebar { width:240px;min-height:100vh;background:#0d6efd; }
.portal-link { display:flex;align-items:center;gap:10px;padding:10px 20px;color:rgba(255,255,255,.75);text-decoration:none;font-size:14px;transition:.15s; }
.portal-link:hover,.portal-link.active { background:rgba(255,255,255,.15);color:#fff; }
.portal-link i { width:18px; }
</style>
</head>
<body class="bg-light">
<div class="d-flex">
  <nav class="portal-sidebar d-flex flex-column flex-shrink-0">
    <div class="p-4 border-bottom border-white border-opacity-25">
      <div class="text-white fw-bold fs-6"><?= esc($settings['company_name'] ?? 'NGWebD') ?></div>
      <div class="text-white-50 small">Client Portal</div>
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

  <div class="flex-grow-1 d-flex flex-column" style="min-width:0">
    <nav class="navbar bg-white border-bottom px-4 py-2">
      <span class="navbar-brand mb-0 h6"><?= $title ?? '' ?></span>
      <div class="ms-auto d-flex align-items-center gap-3">
        <span class="small text-muted"><i class="bi bi-person-circle me-1"></i><?= esc($current_user['name'] ?? '') ?></span>
        <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
      </div>
    </nav>

    <div class="p-4 flex-grow-1">
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
<?= $this->renderSection('scripts') ?>
</body>
</html>
