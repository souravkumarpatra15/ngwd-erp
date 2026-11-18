<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h4 class="mb-0">Issue Internship Certificate</h4>
    <small class="text-muted">Certificates are issued to candidates who accepted their offer or joined</small>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Reference</th>
            <th>Candidate</th>
            <th>Preferred Role</th>
            <th>Status</th>
            <th>Certificate</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($applications)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No candidates are currently eligible for certification.</td></tr>
          <?php else: foreach ($applications as $a): ?>
          <tr>
            <td class="fw-semibold small"><?= esc($a['reference_number']) ?></td>
            <td><?= esc($a['applicant_name']) ?></td>
            <td class="small text-muted"><?= esc($a['preferred_role'] ?? '—') ?></td>
            <td><span class="badge bg-info-subtle text-info"><?= esc(ucfirst(str_replace('_', ' ', (string)$a['status']))) ?></span></td>
            <td class="small">
              <?php if (!empty($a['certificate'])): ?>
                <?php $c = $a['certificate']; ?>
                <code><?= esc($c['certificate_number']) ?></code>
                <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : ($c['status'] === 'revoked' ? 'danger' : 'warning') ?>">
                  <?= ucfirst($c['status']) ?>
                </span>
                <?php if ($c['status'] !== 'revoked'): ?>
                <a href="<?= base_url('certificate/verify/' . $c['certificate_number']) ?>" target="_blank" class="ms-1 small">Verify</a>
                <a href="<?= base_url('admin/certificate/print/' . $c['certificate_number']) ?>" target="_blank" class="ms-1 small">Print</a>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted">Not issued</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (empty($a['certificate']) && $canManage): ?>
              <button class="btn btn-xs btn-primary btn-issue"
                      data-id="<?= $a['id'] ?>"
                      data-name="<?= esc($a['applicant_name']) ?>">Issue</button>
              <?php elseif (!empty($a['certificate']) && $a['certificate']['status'] !== 'revoked' && $canManage): ?>
              <button class="btn btn-xs btn-outline-danger btn-revoke"
                      data-number="<?= esc($a['certificate']['certificate_number']) ?>">Revoke</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const post = (url, extra) => fetch(url, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
    body: new URLSearchParams(Object.assign({ 'csrf_test_name': csrf }, extra))
  }).then(r => r.json());

  document.querySelectorAll('.btn-issue').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const mentor = confirm('Mentor approval recorded for ' + btn.dataset.name + '? (OK = yes, Cancel = skip)');
      const hr = confirm('HR approval — issue as ACTIVE certificate for ' + btn.dataset.name + '?\n(Cancel = save as pending)');
      post('<?= base_url('admin/certificate/generate') ?>', {
        'internship_application_id': btn.dataset.id,
        'mentor_approval': mentor ? '1' : '',
        'hr_approval': hr ? '1' : ''
      }).then(function (res) {
        if (res.status === 'success') {
          alert('Certificate issued: ' + res.data.certificate_number + '\nVerify at: ' + res.data.verification_url);
          location.reload();
        } else {
          alert(res.message || 'Failed to issue certificate');
        }
      });
    });
  });

  document.querySelectorAll('.btn-revoke').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const reason = prompt('Reason for revoking certificate ' + btn.dataset.number + ':');
      if (reason === null) return;
      post('<?= base_url('admin/certificate/revoke') ?>/' + btn.dataset.number, { 'reason': reason })
        .then(function (res) {
          if (res.status === 'success') { alert('Certificate revoked'); location.reload(); }
          else { alert(res.message || 'Failed to revoke'); }
        });
    });
  });
});
</script>

<?= $this->endSection() ?>
