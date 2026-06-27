<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-danger"><?= $open ?></div>
      <div class="text-muted small">Open</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-primary"><?= $in_progress ?></div>
      <div class="text-muted small">In Progress</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-success"><?= count(array_filter($tickets, fn($t) => $t['status'] === 'closed')) ?></div>
      <div class="text-muted small">Closed</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fs-4 fw-bold text-secondary"><?= count($tickets) ?></div>
      <div class="text-muted small">Total</div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-status="open">Open</button>
    <button class="btn btn-sm btn-outline-primary filter-btn" data-status="in_progress">In Progress</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-status="closed">Closed</button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="ticketsTable" class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Ticket #</th><th>Subject</th><th>Client</th><th>Priority</th><th>Status</th><th>Opened</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
        <?php
          $sc = ['open'=>'danger','in_progress'=>'primary','closed'=>'success','on_hold'=>'warning'][$t['status']] ?? 'secondary';
          $pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'][$t['priority']] ?? 'secondary';
        ?>
        <tr data-status="<?= $t['status'] ?>">
          <td><a href="<?= base_url('admin/tickets/'.$t['id']) ?>" class="fw-semibold text-decoration-none small">#<?= esc($t['ticket_number']) ?></a></td>
          <td>
            <a href="<?= base_url('admin/tickets/'.$t['id']) ?>" class="text-decoration-none">
              <div class="fw-semibold small"><?= esc($t['subject']) ?></div>
            </a>
          </td>
          <td class="small text-muted"><?= esc($t['client_name'] ?? '—') ?></td>
          <td><span class="badge bg-<?= $pc ?>"><?= ucfirst($t['priority']) ?></span></td>
          <td>
            <select class="form-select form-select-sm ticket-status-sel" data-id="<?= $t['id'] ?>" style="width:120px">
              <?php foreach (['open','in_progress','on_hold','closed'] as $s): ?>
              <option value="<?= $s ?>" <?= $t['status']==$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="small text-muted"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
          <td>
            <a href="<?= base_url('admin/tickets/'.$t['id']) ?>" class="btn btn-xs btn-outline-primary" title="View &amp; Reply"><i class="bi bi-chat-text"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>'; const CSRF = CSRF_TOKEN;
const dt = $('#ticketsTable').DataTable({ pageLength: 25, order: [[5,'desc']] });

$('.filter-btn').on('click', function() {
  const s = $(this).data('status');
  $('.filter-btn').removeClass('active'); $(this).addClass('active');
  dt.column(4).search(s ? $(this).text().trim() : '', false, false).draw();
});

$('.ticket-status-sel').on('change', function() {
  showLoader('Updating...');
  $.post(`${BASE}admin/tickets/status/${$(this).data('id')}`, {status: $(this).val(), csrf_test_name: CSRF}, res => {
    hideLoader(); showToast(res.message, res.status);
  }, 'json');
});
</script>
<?= $this->endSection() ?>
