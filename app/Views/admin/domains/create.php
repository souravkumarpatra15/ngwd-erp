<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Add Domain</h6></div>
      <div class="card-body">
        <form action="<?= base_url('admin/domains/store') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" class="form-select select2" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Domain Name <span class="text-danger">*</span></label>
              <input type="text" name="domain_name" class="form-control" required placeholder="example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Registrar</label>
              <input type="text" name="registrar" class="form-control" placeholder="GoDaddy, Namecheap, etc.">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Registration Date</label>
              <input type="date" name="registration_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expiry Date <span class="text-danger">*</span></label>
              <input type="date" name="expiry_date" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Cost (₹)</label>
              <input type="number" name="cost" class="form-control" step="0.01" min="0" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Renewal Cost (₹)</label>
              <input type="number" name="renewal_cost" class="form-control" step="0.01" min="0" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="active" selected>Active</option>
                <option value="expiring_soon">Expiring Soon</option>
                <option value="expired">Expired</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Auto Renew</label>
              <select name="auto_renew" class="form-select">
                <option value="0" selected>No</option>
                <option value="1">Yes</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Notes</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Add Domain</button>
            <a href="<?= base_url('admin/domains') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });</script>
<?= $this->endSection() ?>
