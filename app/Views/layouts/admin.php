<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Dashboard' ?> — NGWebD ERP</title>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/ng-ui.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/admin-modern.css') ?>">
  <meta name="base-url" data-base-url="<?= base_url('/') ?>">
</head>

<body>

  <div class="d-flex" id="wrapper">

    <!-- ── Sidebar ── -->
    <nav id="sidebar" class="bg-dark text-white d-flex flex-column" style="min-width:240px;max-width:240px;min-height:100vh;transition:all .3s">
      <?php
      $companyLogo = !empty($settings['company_logo'])
        ? base_url($settings['company_logo'])
        : base_url('assets/images/logo/logo.png');
      ?>

      <div class="p-3 border-bottom border-secondary d-flex align-items-center gap-2 sidebar-brand">
        <div class="sidebar-logo">
          <img src="<?= $companyLogo ?>" alt="Logo">
        </div>

        <div class="sidebar-brand-text">
          <div class="fw-bold" style="font-size:13px;line-height:1">
            <?= esc($settings['company_name'] ?? 'NGWebD ERP') ?>
          </div>
          <div class="text-secondary" style="font-size:10px">Admin Panel</div>
        </div>
      </div>

      <div class="overflow-auto flex-grow-1 py-2">
        <div class="px-3 py-1" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Main</div>
        <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-link <?= isActive('admin/dashboard') ?>">
          <i class="bi bi-speedometer2"></i> Dashboard</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">CRM</div>
        <a href="<?= base_url('admin/leads') ?>" class="sidebar-link <?= isActive('admin/leads') ?>">
          <i class="bi bi-person-plus"></i> Leads</a>
        <a href="<?= base_url('admin/clients') ?>" class="sidebar-link <?= isActive('admin/clients') ?>">
          <i class="bi bi-people"></i> Clients</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Projects</div>
        <a href="<?= base_url('admin/projects') ?>" class="sidebar-link <?= isActive('admin/projects') ?>">
          <i class="bi bi-folder2-open"></i> Projects</a>
        <a href="<?= base_url('admin/milestones') ?>" class="sidebar-link <?= isActive('admin/milestones') ?>">
          <i class="bi bi-flag"></i> Milestones</a>
        <a href="<?= base_url('admin/tasks') ?>" class="sidebar-link <?= isActive('admin/tasks') ?>">
          <i class="bi bi-check2-square"></i> Tasks</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Sales</div>
        <a href="<?= base_url('admin/proposals') ?>" class="sidebar-link <?= isActive('admin/proposals') ?>">
          <i class="bi bi-file-earmark-text"></i> Proposals</a>
        <a href="<?= base_url('admin/agreements') ?>" class="sidebar-link <?= isActive('admin/agreements') ?>">
          <i class="bi bi-file-earmark-check"></i> Agreements</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Billing</div>
        <a href="<?= base_url('admin/invoices') ?>" class="sidebar-link <?= isActive('admin/invoices') ?>">
          <i class="bi bi-receipt"></i> Invoices</a>
        <a href="<?= base_url('admin/payments') ?>" class="sidebar-link <?= isActive('admin/payments') ?>">
          <i class="bi bi-cash-stack"></i> Payments</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Infrastructure</div>
        <a href="<?= base_url('admin/domains') ?>" class="sidebar-link <?= isActive('admin/domains') ?>">
          <i class="bi bi-globe"></i> Domains</a>
        <a href="<?= base_url('admin/hostings') ?>" class="sidebar-link <?= isActive('admin/hostings') ?>">
          <i class="bi bi-server"></i> Hosting</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Associates</div>
        <a href="<?= base_url('admin/users') ?>" class="sidebar-link <?= isActive('admin/users') ?>">
          <i class="bi bi-people"></i> Users</a>

        <div class="px-3 py-1 mt-2" style="font-size:10px;letter-spacing:1px;color:#6c757d;text-transform:uppercase">Other</div>
        <a href="<?= base_url('admin/documents') ?>" class="sidebar-link <?= isActive('admin/documents') ?>">
          <i class="bi bi-folder"></i> Documents</a>
        <a href="<?= base_url('admin/tickets') ?>" class="sidebar-link <?= isActive('admin/tickets') ?>">
          <i class="bi bi-headset"></i> Support</a>
        <a href="<?= base_url('admin/reports') ?>" class="sidebar-link <?= isActive('admin/reports') ?>">
          <i class="bi bi-bar-chart-line"></i> Reports</a>
        <a href="<?= base_url('admin/settings') ?>" class="sidebar-link <?= isActive('admin/settings') ?>">
          <i class="bi bi-gear"></i> Settings</a>
      </div>
    </nav>

    <!-- ── Main Content ── -->
    <div class="flex-grow-1 d-flex flex-column" style="min-width:0">

      <!-- Topbar -->
      <nav class="navbar navbar-expand bg-white border-bottom px-3 py-2 sticky-top" style="z-index:100">
        <button class="btn btn-sm btn-outline-secondary me-2" id="sidebarOpen"><i class="bi bi-list fs-5"></i></button>

        <div class="me-auto">
          <input type="text" id="globalSearch" class="form-control form-control-sm" placeholder="Everything Search here..." style="width:280px; height:40px;font-family:'Bootstrap Icons',sans-serif">
        </div>

        <ul class="navbar-nav ms-3 gap-1 align-items-center">
          <!-- Quick Add -->
          <li class="nav-item dropdown">
            <a class="btn btn-primary btn-sm" data-bs-toggle="dropdown" href="#" title="Quick Add">
              <i class="bi bi-plus-lg"></i></a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="<?= base_url('admin/leads/create') ?>"><i class="bi bi-person-plus me-2 text-primary"></i>Add Lead</a></li>
              <li><a class="dropdown-item" href="<?= base_url('admin/clients/create') ?>"><i class="bi bi-people me-2 text-success"></i>Add Client</a></li>
              <li><a class="dropdown-item" href="<?= base_url('admin/projects/create') ?>"><i class="bi bi-folder-plus me-2 text-warning"></i>Add Project</a></li>
              <li><a class="dropdown-item" href="<?= base_url('admin/invoices/create') ?>"><i class="bi bi-receipt me-2 text-info"></i>Create Invoice</a></li>
              <li><a class="dropdown-item" href="<?= base_url('admin/payments/create') ?>"><i class="bi bi-cash me-2 text-danger"></i>Record Payment</a></li>
            </ul>
          </li>

          <!-- Notifications -->
          <li class="nav-item dropdown">
            <a class="btn btn-sm btn-outline-secondary position-relative" data-bs-toggle="dropdown" href="#">
              <i class="bi bi-bell fs-6"></i>
              <?php if (($unread_notifications ?? 0) > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px"><?= $unread_notifications ?></span>
              <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width:320px">
              <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <strong class="small">Notifications</strong>
                <a href="#" class="text-muted small" id="markAllRead">Mark all read</a>
              </div>
              <div id="notifList" style="max-height:280px;overflow-y:auto">
                <p class="text-center text-muted py-3 small">No new notifications</p>
              </div>
              <div class="p-2 border-top text-center">
                <a href="<?= base_url('admin/notifications') ?>" class="text-primary small">View All</a>
              </div>
            </div>
          </li>

          <!-- User -->
          <li class="nav-item dropdown">
            <a class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" href="#">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:13px">
                <?= strtoupper(substr($current_user['name'] ?? 'A', 0, 1)) ?>
              </div>
              <span class="d-none d-md-inline small fw-semibold"><?= esc($current_user['name'] ?? '') ?></span>
              <i class="bi bi-chevron-down small text-muted"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="<?= base_url('admin/profile') ?>"><i class="bi bi-people me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="<?= base_url('admin/settings') ?>"><i class="bi bi-gear me-2"></i>Settings</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </nav>

      <!-- Page Header -->
      <div class="bg-white border-bottom px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0 fw-semibold"><?= $title ?? '' ?></h5>
            <?php if (isset($breadcrumb)): ?>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small"><?= $breadcrumb ?></ol>
              </nav>
            <?php endif; ?>
          </div>
          <?php if (isset($page_actions)): ?>
            <div><?= $page_actions ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Main Content -->
      <div class="p-4 flex-grow-1">
        <?= $this->renderSection('content') ?>
      </div>

      <footer class="border-top bg-white px-4 py-2 text-center text-muted small">
        &copy; <?= date('Y') ?> NGWebD Consulting Pvt. Ltd. &mdash; ERP v1.0
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
        <div class="ng-confirm-icon">
          <i class="bi bi-trash3-fill"></i>
        </div>

        <div class="modal-body text-center px-4 pb-4">
          <h5 class="fw-bold mb-2" id="ngConfirmTitle">Are you sure?</h5>
          <p class="text-muted mb-4" id="ngConfirmMessage">
            This action cannot be undone.
          </p>

          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
              Cancel
            </button>
            <button type="button" class="btn btn-danger px-4" id="ngConfirmYes">
              Yes, Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="<?= base_url('assets/js/custom.js') ?>"></script>
  <script>
    // Sidebar toggle
    document.getElementById('sidebarOpen')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('d-none'));
    document.getElementById('sidebarToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('d-none'));
    // Mark all notifications read
    document.getElementById('markAllRead')?.addEventListener('click', e => {
      e.preventDefault();
      $.post('<?= base_url('admin/notifications/read-all') ?>', {
        csrf_test_name: $('meta[name=csrf-token]').attr('content')
      }, () => {
        document.querySelector('.badge.bg-danger')?.remove();
        showToast('All notifications marked as read', 'info');
      });
    });
  </script>
  <?= $this->renderSection('scripts') ?>
</body>

</html>