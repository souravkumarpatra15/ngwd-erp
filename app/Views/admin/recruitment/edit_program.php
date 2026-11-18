<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0">Edit Internship Programme</h4>
    <small class="text-muted"><?= esc($program['title']) ?></small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/recruitment/program/' . $program['id']) ?>" class="btn btn-sm btn-outline-info">
      <i class="bi bi-eye me-1"></i>View Detail
    </a>
    <a href="<?= base_url('admin/recruitment') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form id="programForm" method="post" action="<?= base_url('admin/recruitment/program/update/' . $program['id']) ?>">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" value="<?= esc($program['title']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <?php foreach (['active', 'draft', 'closed', 'completed'] as $st): ?>
            <option value="<?= $st ?>" <?= $program['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= esc($program['description']) ?></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label">Duration (months) <span class="text-danger">*</span></label>
          <input type="number" name="duration_months" min="1" class="form-control" value="<?= (int) $program['duration_months'] ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Weekly Hours <span class="text-danger">*</span></label>
          <input type="number" name="weekly_hours" min="1" class="form-control" value="<?= (int) $program['weekly_hours'] ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Mode <span class="text-danger">*</span></label>
          <select name="mode" class="form-select" required>
            <?php foreach (['remote', 'hybrid', 'office'] as $m): ?>
            <option value="<?= $m ?>" <?= $program['mode'] === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Compensation Category</label>
          <input type="text" name="compensation_category" class="form-control" value="<?= esc($program['compensation_category']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Start Date <span class="text-danger">*</span></label>
          <input type="date" name="start_date" class="form-control" value="<?= esc($program['start_date']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">End Date <span class="text-danger">*</span></label>
          <input type="date" name="end_date" class="form-control" value="<?= esc($program['end_date']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Application Deadline <span class="text-danger">*</span></label>
          <input type="date" name="application_deadline" class="form-control" value="<?= esc($program['application_deadline']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Batch Size — Min</label>
          <input type="number" name="batch_size_min" min="1" class="form-control" value="<?= (int) $program['batch_size_min'] ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Batch Size — Max</label>
          <input type="number" name="batch_size_max" min="2" class="form-control" value="<?= (int) $program['batch_size_max'] ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Eligibility Criteria</label>
          <textarea name="eligibility_criteria" class="form-control" rows="2"><?= esc($program['eligibility_criteria']) ?></textarea>
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Programme</button>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white"><strong>Positions (<?= count($positions) ?>)</strong></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Title</th><th>Required Skills</th><th>Hours/Week</th><th>Location</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (empty($positions)): ?>
          <tr><td colspan="5" class="text-center text-muted py-3">No positions for this programme.</td></tr>
          <?php else: foreach ($positions as $p): ?>
          <tr>
            <td class="fw-semibold"><?= esc($p['title']) ?></td>
            <td class="small text-muted"><?= esc($p['required_skills']) ?></td>
            <td class="small"><?= (int) $p['working_hours_per_week'] ?></td>
            <td class="small text-muted"><?= esc($p['location']) ?></td>
            <td><span class="badge bg-<?= $p['status'] === 'open' ? 'success' : 'secondary' ?>"><?= ucfirst($p['status']) ?></span></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
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
        window.location.href = '<?= base_url('admin/recruitment/program/' . $program['id']) ?>';
      } else {
        alert(res.message || (res.errors ? Object.values(res.errors).join('\n') : 'Failed to update programme'));
      }
    }).catch(() => alert('Request failed'));
  });
});
</script>

<?= $this->endSection() ?>
