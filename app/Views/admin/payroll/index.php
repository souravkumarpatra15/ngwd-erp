<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0">Payroll Management</h4>
    <small class="text-muted">Active employees and their salary structure status</small>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Employee</th>
            <th>Email</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Employment Type</th>
            <th>Joining Date</th>
            <th>Salary Structure</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employees)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No active employees found.</td></tr>
          <?php else: foreach ($employees as $e): ?>
          <tr>
            <td class="fw-semibold"><?= esc($e['user_name'] ?? ('Employee #' . $e['id'])) ?></td>
            <td class="small text-muted"><?= esc($e['user_email'] ?? '—') ?></td>
            <td class="small text-muted"><?= esc($e['department_name'] ?? '—') ?></td>
            <td class="small text-muted"><?= esc($e['designation_title'] ?? '—') ?></td>
            <td><span class="badge bg-info-subtle text-info"><?= esc(ucfirst((string)($e['employment_type'] ?? '—'))) ?></span></td>
            <td class="small text-muted"><?= $e['joining_date'] ? date('d M Y', strtotime($e['joining_date'])) : '—' ?></td>
            <td>
              <span class="badge bg-secondary-subtle text-secondary">Not configured</span>
            </td>
            <td>
              <a href="<?= base_url('admin/payroll/setup-structure/' . $e['id']) ?>" class="btn btn-xs btn-outline-primary">
                <i class="bi bi-gear me-1"></i>Set Salary
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
