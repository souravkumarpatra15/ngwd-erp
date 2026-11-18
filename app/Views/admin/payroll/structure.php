<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0">Set Up Salary Structure</h4>
    <small class="text-muted">
      <?= esc($employee['user_name'] ?? ('Employee #' . $employee['id'])) ?>
      &middot; <?= esc($employee['employment_type'] ?? '') ?>
    </small>
  </div>
  <a href="<?= base_url('admin/payroll') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Back to Payroll
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form id="salaryForm" method="post" action="<?= base_url('admin/payroll/setup-structure/' . $employee['id']) ?>">
      <?= csrf_field() ?>      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Basic Salary (Monthly) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">&#8377;</span>
            <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" required>
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label">HRA (%)</label>
          <input type="number" step="0.01" min="0" max="100" name="hra_percent" class="form-control" value="40">
        </div>
        <div class="col-md-4">
          <label class="form-label">Other Allowances (&#8377;)</label>
          <div class="input-group">
            <span class="input-group-text">&#8377;</span>
            <input type="number" step="0.01" min="0" name="other_allowances" class="form-control" value="0">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Provident Fund (Employee %)</label>
          <input type="number" step="0.01" min="0" max="100" name="pf_percent" class="form-control" value="12">
        </div>
        <div class="col-md-4">
          <label class="form-label">Professional Tax (&#8377;)</label>
          <div class="input-group">
            <span class="input-group-text">&#8377;</span>
            <input type="number" step="0.01" min="0" name="professional_tax" class="form-control" value="200">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label">TDS (%)</label>
          <input type="number" step="0.01" min="0" max="100" name="tds_percent" class="form-control" value="0">
        </div>
        <div class="col-md-6">
          <label class="form-label">Effective From</label>
          <input type="date" name="effective_from" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Currency</label>
          <select name="currency" class="form-select">
            <option value="INR" selected>INR (&#8377;)</option>
            <option value="USD">USD ($)</option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Salary Structure</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('salaryForm');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    fetch(form.action, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    }).then(r => r.json()).then(res => {
      if (res.status === 'success') {
        alert(res.message || 'Salary structure saved');
        window.location.href = '<?= base_url('admin/payroll') ?>';
      } else {
        alert(res.message || 'Failed to save salary structure');
      }
    }).catch(() => alert('Request failed'));
  });
});
</script>

<?= $this->endSection() ?>
