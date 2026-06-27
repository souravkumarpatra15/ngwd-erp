<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0"><?= esc($client['name']) ?></h4>
    <small class="text-muted"><?= esc($client['client_number'] ?? '') ?></small>
  </div>
  <div>
    <a href="<?= base_url('admin/clients/edit/' . $client['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
    <a href="<?= base_url('admin/clients') ?>" class="btn btn-secondary btn-sm">Back</a>
  </div>
</div>

<div class="row mb-4">

  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="text-muted">Total Billed</h6>
        <h4>₹<?= number_format($total_billed ?? 0, 2) ?></h4>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="text-muted">Total Paid</h6>
        <h4 class="text-success">₹<?= number_format($total_paid ?? 0, 2) ?></h4>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="text-muted">Balance</h6>
        <h4 class="text-danger">₹<?= number_format(($total_billed ?? 0) - ($total_paid ?? 0), 2) ?></h4>
      </div>
    </div>
  </div>

</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white">
    <strong>Client Details</strong>
  </div>
  <div class="card-body">
    <div class="row">

      <div class="col-md-6 mb-2"><strong>Name:</strong> <?= esc($client['name'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>Company:</strong> <?= esc($client['company_name'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>Email:</strong> <?= esc($client['email'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>Phone:</strong> <?= esc($client['phone'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>GST:</strong> <?= esc($client['gst_number'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>Status:</strong>
        <span class="badge bg-<?= ($client['status'] ?? 'active') == 'active' ? 'success' : 'secondary' ?>">
          <?= esc($client['status'] ?? 'active') ?>
        </span>
      </div>
      <div class="col-md-12 mb-2"><strong>Address:</strong> <?= esc($client['address'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>City:</strong> <?= esc($client['city'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>State:</strong> <?= esc($client['state'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>Country:</strong> <?= esc($client['country'] ?? '-') ?></div>
      <div class="col-md-6 mb-2"><strong>Pincode:</strong> <?= esc($client['pincode'] ?? '-') ?></div>

    </div>
  </div>
</div>

<?= view('admin/clients/partials/table_section', [
  'title' => 'Projects',
  'items' => $projects,
  'columns' => ['project_number', 'title', 'status', 'created_at']
]) ?>

<?= view('admin/clients/partials/table_section', [
  'title' => 'Invoices',
  'items' => $invoices,
  'columns' => ['invoice_number', 'total', 'status', 'created_at']
]) ?>

<?= view('admin/clients/partials/table_section', [
  'title' => 'Payments',
  'items' => $payments,
  'columns' => ['payment_number', 'amount', 'status', 'created_at']
]) ?>

<?= view('admin/clients/partials/table_section', [
  'title' => 'Domains',
  'items' => $domains,
  'columns' => ['domain_name', 'registrar', 'expiry_date', 'status']
]) ?>

<?= view('admin/clients/partials/table_section', [
  'title' => 'Hostings',
  'items' => $hostings,
  'columns' => ['hosting_name', 'plan_name', 'expiry_date', 'status']
]) ?>

<?= $this->endSection() ?>