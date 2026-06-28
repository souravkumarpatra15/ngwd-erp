<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Edit Hosting Record</h6>
        <span class="badge bg-<?= ['active'=>'success','expiring_soon'=>'warning','expired'=>'danger'][$hosting['status']] ?? 'secondary' ?>">
          <?= ucwords(str_replace('_',' ',$hosting['status'])) ?>
        </span>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/hostings/update/'.$hosting['id']) ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" class="form-select select2" required>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $hosting['client_id']==$c['id']?'selected':'' ?>>
                  <?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Provider <span class="text-danger">*</span></label>
              <input type="text" name="provider" class="form-control" value="<?= esc($hosting['provider']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Package / Plan</label>
              <input type="text" name="package" class="form-control" value="<?= esc($hosting['package'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Server IP</label>
              <input type="text" name="server_ip" class="form-control" value="<?= esc($hosting['server_ip'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Control Panel URL</label>
              <input type="text" name="control_panel_url" class="form-control" value="<?= esc($hosting['control_panel_url'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">cPanel Username</label>
              <input type="text" name="username" class="form-control" value="<?= esc($hosting['username'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Purchase Date</label>
              <input type="date" name="purchase_date" class="form-control" value="<?= $hosting['purchase_date'] ?? '' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expiry Date <span class="text-danger">*</span></label>
              <input type="date" name="expiry_date" class="form-control" value="<?= $hosting['expiry_date'] ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Cost (₹)</label>
              <input type="number" name="cost" class="form-control" step="0.01" value="<?= $hosting['cost'] ?? 0 ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Renewal Cost (₹)</label>
              <input type="number" name="renewal_cost" class="form-control" step="0.01" value="<?= $hosting['renewal_cost'] ?? 0 ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" class="form-select">
                <?php foreach (['active','expiring_soon','expired'] as $s): ?>
                <option value="<?= $s ?>" <?= $hosting['status']==$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Server Details / Notes</label>
              <textarea name="server_details" class="form-control" rows="3"><?= esc($hosting['server_details'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
            <a href="<?= base_url('admin/hostings') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme:'bootstrap-5', width:'100%' });</script>
<?= $this->endSection() ?>
