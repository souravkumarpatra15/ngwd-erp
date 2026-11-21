<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$name = $employee['user_name'] ?? 'Employee';
$initial = strtoupper(mb_substr($name, 0, 1));
$statusColor = match ($employee['employment_status'] ?? '') {
    'active' => 'success',
    'probation', 'notice_period' => 'warning text-dark',
    'on_leave' => 'info text-dark',
    'resigned', 'terminated', 'inactive' => 'danger',
    default => 'secondary',
};
$typeColor = match ($employee['employment_type'] ?? '') {
    'permanent' => 'success',
    'internship' => 'info text-dark',
    'contract' => 'purple',
    default => 'warning text-dark',
};
$fmt = static fn($d) => !empty($d) && strtotime((string) $d) ? date('d M Y', strtotime((string) $d)) : '—';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <a href="<?= base_url('admin/employees') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i> Back to Employees
  </a>
  <div class="d-flex flex-wrap gap-2">
    <?php if (!empty($canManage)): ?>
      <a href="<?= base_url('admin/employees/edit/' . (int) $employee['id']) ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-pencil-square me-1"></i> Edit
      </a>
    <?php endif; ?>
    <?php if (!empty($employee['user_id'])): ?>
      <a href="<?= base_url('admin/employee/id-card/' . (int) $employee['user_id']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-person-vcard me-1"></i> ID Card PDF
      </a>
    <?php endif; ?>
    <?php if (($employee['employment_type'] ?? '') === 'internship' && !empty($employee['user_id']) && !empty($canManage)): ?>
      <a href="<?= base_url('admin/employee/conversion/' . (int) $employee['user_id']) ?>" class="btn btn-sm btn-outline-success">
        <i class="bi bi-arrow-repeat me-1"></i> Convert to Permanent
      </a>
    <?php endif; ?>
    <?php if (!empty($canManage)): ?>
      <a href="<?= base_url('admin/attendance/summary/' . (int) $employee['id'] . '/' . date('Y-m')) ?>" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-calendar3 me-1"></i> Attendance Summary
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body d-flex flex-wrap align-items-center gap-3">
    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
         style="width:64px;height:64px;font-size:24px"><?= esc($initial) ?></div>
    <div class="flex-grow-1 min-w-0">
      <h5 class="mb-1 fw-semibold text-truncate"><?= esc($name) ?></h5>
      <div class="text-muted small">
        <span class="me-2"><i class="bi bi-hash"></i> <?= esc($employee['employee_id'] ?? '—') ?></span>
        <span class="me-2"><i class="bi bi-diagram-3"></i> <?= esc($employee['department_name'] ?? 'No department') ?></span>
        <span><i class="bi bi-briefcase"></i> <?= esc($employee['designation_title'] ?? 'No designation') ?></span>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <span class="badge bg-<?= $typeColor ?>"><?= esc(ucfirst(str_replace('_', ' ', (string) ($employee['employment_type'] ?? '—')))) ?></span>
      <span class="badge bg-<?= $statusColor ?>"><?= esc(ucfirst(str_replace('_', ' ', (string) ($employee['employment_status'] ?? '—')))) ?></span>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
      <div class="fs-4 fw-bold text-success"><?= (int) $attendanceSummary['present'] ?></div>
      <div class="text-muted small">Present (30d)</div>
    </div></div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
      <div class="fs-4 fw-bold text-danger"><?= (int) $attendanceSummary['absent'] ?></div>
      <div class="text-muted small">Absent (30d)</div>
    </div></div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
      <div class="fs-4 fw-bold text-warning"><?= (int) $attendanceSummary['half_day'] ?></div>
      <div class="text-muted small">Half days (30d)</div>
    </div></div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm h-100"><div class="card-body text-center py-3">
      <div class="fs-4 fw-bold text-primary"><?= number_format((float) $attendanceSummary['hours'], 1) ?>h</div>
      <div class="text-muted small">Hours logged (30d)</div>
    </div></div>
  </div>
</div>

<div class="row g-3">
  <!-- Employment & contact -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-briefcase me-2 text-primary"></i>Employment</h6></div>
      <div class="card-body pt-0">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted fw-normal">Employee ID</dt><dd class="col-7"><?= esc($employee['employee_id'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Department</dt><dd class="col-7"><?= esc($employee['department_name'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Designation</dt><dd class="col-7"><?= esc($employee['designation_title'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Employment type</dt><dd class="col-7"><?= esc(ucfirst(str_replace('_', ' ', (string) ($employee['employment_type'] ?? '—')))) ?></dd>
          <dt class="col-5 text-muted fw-normal">Status</dt><dd class="col-7"><?= esc(ucfirst(str_replace('_', ' ', (string) ($employee['employment_status'] ?? '—')))) ?></dd>
          <dt class="col-5 text-muted fw-normal">Joining date</dt><dd class="col-7"><?= $fmt($employee['joining_date'] ?? null) ?></dd>
          <dt class="col-5 text-muted fw-normal">Probation ends</dt><dd class="col-7"><?= $fmt($employee['probation_end_date'] ?? null) ?></dd>
          <?php if (!empty($employee['last_working_date'])): ?>
          <dt class="col-5 text-muted fw-normal">Last working day</dt><dd class="col-7"><?= $fmt($employee['last_working_date']) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-person-lines-fill me-2 text-info"></i>Contact</h6></div>
      <div class="card-body pt-0">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted fw-normal">Email</dt><dd class="col-7 text-break"><?= esc($employee['user_email'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Phone</dt><dd class="col-7"><?= esc($employee['emergency_contact_phone'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Emergency contact</dt><dd class="col-7"><?= esc(trim(($employee['emergency_contact_name'] ?? '') . ' ' . ($employee['emergency_contact_relation'] ? '(' . $employee['emergency_contact_relation'] . ')' : ''))) ?: '—' ?></dd>
          <dt class="col-5 text-muted fw-normal">Address</dt>
          <dd class="col-7"><?= esc(trim(implode(', ', array_filter([$employee['address_line1'] ?? '', $employee['address_line2'] ?? '', $employee['city'] ?? '', $employee['state'] ?? '', $employee['pincode'] ?? ''])))) ?: '—' ?></dd>
          <dt class="col-5 text-muted fw-normal">Blood group</dt><dd class="col-7"><?= esc($employee['blood_group'] ?? '—') ?></dd>
        </dl>
      </div>
    </div>

    <?php if (!empty($canViewSensitive)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-shield-lock me-2 text-warning"></i>Confidential</h6>
        <span class="badge bg-warning text-dark">Restricted</span>
      </div>
      <div class="card-body pt-0">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted fw-normal">PAN</dt><dd class="col-7"><?= esc($employee['pan_number'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Aadhaar</dt><dd class="col-7"><?= esc($employee['aadhar_number'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Bank</dt><dd class="col-7"><?= esc($employee['bank_name'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">Account</dt><dd class="col-7"><?= esc($employee['bank_account_number'] ?? '—') ?></dd>
          <dt class="col-5 text-muted fw-normal">IFSC</dt><dd class="col-7"><?= esc($employee['bank_ifsc'] ?? '—') ?></dd>
        </dl>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Attendance / onboarding / activity -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-check me-2 text-success"></i>Recent Attendance</h6>
        <span class="badge bg-light text-muted border">Last 30 days</span>
      </div>
      <?php if (empty($attendance)): ?>
        <div class="card-body text-center text-muted small py-4">No attendance records in the last 30 days.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check in</th><th>Check out</th><th class="text-end">Hours</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($attendance, 0, 12) as $a): ?>
              <tr>
                <td class="small"><?= $fmt($a['date']) ?></td>
                <td><span class="badge bg-<?= match ($a['status']) { 'present' => 'success', 'absent' => 'danger', 'half_day' => 'warning text-dark', 'leave' => 'secondary', 'work_from_home' => 'info text-dark', default => 'secondary' } ?>"><?= esc(ucfirst(str_replace('_', ' ', (string) $a['status']))) ?></span></td>
                <td class="small"><?= !empty($a['check_in']) ? date('h:i A', strtotime($a['check_in'])) : '—' ?></td>
                <td class="small"><?= !empty($a['check_out']) ? date('h:i A', strtotime($a['check_out'])) : '—' ?></td>
                <td class="small text-end"><?= number_format((float) ($a['hours_worked'] ?? 0), 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-list-check me-2 text-primary"></i>Onboarding Checklist</h6>
        <span class="badge bg-light text-muted border"><?= count(array_filter($onboarding, static fn($o) => !empty($o['is_completed']))) ?>/<?= count($onboarding) ?> done</span>
      </div>
      <div class="card-body">
        <?php if (empty($onboarding)): ?>
          <div class="text-muted small text-center py-3">No onboarding items assigned.</div>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($onboarding as $o): ?>
            <li class="d-flex align-items-start gap-2 py-1 small">
              <i class="bi <?= !empty($o['is_completed']) ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?>"></i>
              <span class="<?= !empty($o['is_completed']) ? 'text-muted text-decoration-line-through' : '' ?>"><?= esc($o['checklist_item'] ?? '') ?></span>
              <?php if (!empty($o['completed_at'])): ?><span class="ms-auto text-muted" style="font-size:11px"><?= date('d M', strtotime($o['completed_at'])) ?></span><?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-activity me-2 text-danger"></i>Recent Activity</h6></div>
      <div class="card-body">
        <?php if (empty($activities)): ?>
          <div class="text-muted small text-center py-3">No recorded activity yet.</div>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($activities as $act): ?>
            <li class="d-flex gap-2 py-2 border-bottom small">
              <i class="bi bi-dot fs-5 text-primary lh-1"></i>
              <div class="min-w-0">
                <div class="fw-semibold"><?= esc(ucfirst(str_replace('_', ' ', (string) ($act['action'] ?? '')))) ?></div>
                <div class="text-muted text-truncate"><?= esc($act['description'] ?? '') ?></div>
                <div class="text-muted" style="font-size:11px"><?= esc(ucfirst((string) ($act['module'] ?? ''))) ?> · <?= !empty($act['created_at']) ? date('d M Y, h:i A', strtotime($act['created_at'])) : '' ?></div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
