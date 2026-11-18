<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0">Create Internship Programme</h4>
    <small class="text-muted">Standard NGWebD positions are auto-created with the programme</small>
  </div>
  <a href="<?= base_url('admin/recruitment') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Back to Recruitment
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form id="programForm" method="post" action="<?= base_url('admin/recruitment/program/store') ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="active" selected>Active</option>
            <option value="draft">Draft</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label">Duration (months) <span class="text-danger">*</span></label>
          <input type="number" name="duration_months" min="1" class="form-control" value="3" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Weekly Hours <span class="text-danger">*</span></label>
          <input type="number" name="weekly_hours" min="1" class="form-control" value="20" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Mode <span class="text-danger">*</span></label>
          <select name="mode" class="form-select" required>
            <option value="remote" selected>Remote</option>
            <option value="hybrid">Hybrid</option>
            <option value="office">Office</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Compensation Category</label>
          <input type="text" name="compensation_category" class="form-control" value="Unpaid educational internship">
        </div>
        <div class="col-md-4">
          <label class="form-label">Start Date <span class="text-danger">*</span></label>
          <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date <span class="text-danger">*</span></label>
          <input type="date" name="end_date" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Application Deadline <span class="text-danger">*</span></label>
          <input type="date" name="application_deadline" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Batch Size — Min</label>
          <input type="number" name="batch_size_min" min="1" class="form-control" value="3">
        </div>
        <div class="col-md-6">
          <label class="form-label">Batch Size — Max</label>
          <input type="number" name="batch_size_max" min="2" class="form-control" value="5">
        </div>
        <div class="col-12">
          <label class="form-label">Eligibility Criteria</label>
          <textarea name="eligibility_criteria" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Create Programme</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('programForm');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    fetch(form.action, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    }).then(r => r.json()).then(res => {
      if (res.status === 'success') {
        window.location.href = '<?= base_url('admin/recruitment') ?>';
      } else {
        alert(res.message || (res.errors ? Object.values(res.errors).join('\n') : 'Failed to create programme'));
      }
    }).catch(() => alert('Request failed'));
  });
});
</script>

<?= $this->endSection() ?>
