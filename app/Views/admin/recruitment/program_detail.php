<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0"><?= esc($program['title']) ?></h4>
    <small class="text-muted">
      <?= ucfirst($program['mode']) ?> &middot; <?= (int) $program['duration_months'] ?> months &middot;
      <?= (int) $program['weekly_hours'] ?> hrs/week &middot;
      <span class="badge bg-<?= $program['status'] === 'active' ? 'success' : ($program['status'] === 'closed' ? 'danger' : 'secondary') ?>">
        <?= ucfirst($program['status']) ?>
      </span>
    </small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('admin/recruitment/program/edit/' . $program['id']) ?>" class="btn btn-sm btn-outline-warning">
      <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <a href="<?= base_url('admin/recruitment') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
      <div class="display-6 fw-bold"><?= count($applications) ?></div>
      <small class="text-muted">Total Applications</small>
    </div></div>
  </div>
  <?php foreach (['shortlisted', 'interview_scheduled', 'offer_sent', 'onboarding'] as $st): ?>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100"><div class="card-body text-center">
      <div class="display-6 fw-bold"><?= (int) ($statusCounts[$st] ?? 0) ?></div>
      <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $st)) ?></small>
    </div></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white"><strong>Programme Details</strong></div>
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-5">Start Date</dt><dd class="col-7"><?= date('d M Y', strtotime($program['start_date'])) ?></dd>
          <dt class="col-5">End Date</dt><dd class="col-7"><?= date('d M Y', strtotime($program['end_date'])) ?></dd>
          <dt class="col-5">Deadline</dt><dd class="col-7"><?= date('d M Y', strtotime($program['application_deadline'])) ?></dd>
          <dt class="col-5">Batch Size</dt><dd class="col-7"><?= (int) $program['batch_size_min'] ?> – <?= (int) $program['batch_size_max'] ?></dd>
          <dt class="col-5">Compensation</dt><dd class="col-7"><?= esc($program['compensation_category'] ?? '—') ?></dd>
          <dt class="col-5">Eligibility</dt><dd class="col-7"><?= esc($program['eligibility_criteria'] ?? '—') ?></dd>
          <dt class="col-5">Apply URL</dt>
          <dd class="col-7 text-break">
            <?php if (!empty($program['public_application_url'])): ?>
            <a href="<?= base_url($program['public_application_url']) ?>"><?= base_url($program['public_application_url']) ?></a>
            <?php else: ?>—<?php endif; ?>
          </dd>
        </dl>
        <?php if (!empty($program['description'])): ?>
        <hr>
        <p class="small mb-0"><?= nl2br(esc($program['description'])) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white"><strong>Positions (<?= count($positions) ?>)</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Title</th><th>Skills</th><th>Location</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (empty($positions)): ?>
              <tr><td colspan="4" class="text-center text-muted py-3">No positions.</td></tr>
              <?php else: foreach ($positions as $p): ?>
              <tr>
                <td class="fw-semibold"><?= esc($p['title']) ?></td>
                <td class="small text-muted"><?= esc($p['required_skills']) ?></td>
                <td class="small text-muted"><?= esc($p['location']) ?></td>
                <td><span class="badge bg-<?= $p['status'] === 'open' ? 'success' : 'secondary' ?>"><?= ucfirst($p['status']) ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white"><strong>Recent Applications</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Applicant</th><th>Position</th><th>Applied</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (empty($applications)): ?>
              <tr><td colspan="4" class="text-center text-muted py-3">No applications yet.</td></tr>
              <?php else: foreach (array_slice($applications, 0, 10) as $a): ?>
              <tr>
                <td class="fw-semibold"><?= esc($a['applicant_name']) ?></td>
                <td class="small text-muted"><?= esc($a['program_title'] ?? '—') ?></td>
                <td class="small text-muted"><?= $a['applied_at'] ? date('d M Y', strtotime($a['applied_at'])) : '—' ?></td>
                <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', (string)$a['status'])) ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
