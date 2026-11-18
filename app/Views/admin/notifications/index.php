<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="mb-0">Notifications</h4>
  <div class="d-flex gap-2">
    <span class="badge bg-danger align-self-center"><?= $unread_count ?> unread</span>
    <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-all me-1"></i>Mark all read</button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="list-group list-group-flush">
      <?php if (empty($notifications)): ?>
        <div class="p-4 text-center text-muted">
          <i class="bi bi-bell-slash display-6 d-block mb-2"></i>No notifications yet.
        </div>
      <?php else: ?>
        <?php foreach ($notifications as $n): ?>
        <div class="list-group-item d-flex justify-content-between align-items-start gap-3 <?= $n['is_read'] ? '' : 'fw-semibold' ?>">
          <div>
            <div class="small <?= $n['is_read'] ? 'text-muted' : '' ?>">
              <?= esc($n['title']) ?>
              <span class="badge bg-secondary-subtle text-secondary ms-1"><?= esc($n['type']) ?></span>
            </div>
            <div class="small text-muted"><?= esc($n['message']) ?></div>
            <div class="small text-muted mt-1">
              <i class="bi bi-clock"></i> <?= date('d M Y, h:i A', strtotime($n['created_at'])) ?>
            </div>
          </div>
          <?php if (!$n['is_read']): ?>
          <button class="btn btn-xs btn-outline-primary btn-mark-read" data-id="<?= $n['id'] ?>">Mark read</button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  document.querySelectorAll('.btn-mark-read').forEach(function (btn) {
    btn.addEventListener('click', function () {
      fetch('<?= base_url('admin/notifications/read') ?>/' + btn.dataset.id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
        body: new URLSearchParams({ 'csrf_test_name': csrf })
      }).then(function (r) { return r.json(); }).then(function (res) {
        if (res.status === 'success') location.reload();
      });
    });
  });

  const markAll = document.getElementById('markAllReadBtn');
  if (markAll) {
    markAll.addEventListener('click', function () {
      fetch('<?= base_url('admin/notifications/read-all') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
        body: new URLSearchParams({ 'csrf_test_name': csrf })
      }).then(function (r) { return r.json(); }).then(function (res) {
        if (res.status === 'success') location.reload();
      });
    });
  }
});
</script>

<?= $this->endSection() ?>
