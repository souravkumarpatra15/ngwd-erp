<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Edit Domain</h6>
        <span class="badge bg-<?= ['active'=>'success','expiring_soon'=>'warning','expired'=>'danger'][$domain['status']] ?? 'secondary' ?>">
          <?= ucwords(str_replace('_',' ',$domain['status'])) ?>
        </span>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/domains/update/'.$domain['id']) ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" class="form-select select2" required>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $domain['client_id']==$c['id']?'selected':'' ?>>
                  <?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Domain Name <span class="text-danger">*</span></label>
              <input type="text" name="domain_name" class="form-control" value="<?= esc($domain['domain_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Registrar</label>
              <input type="text" name="registrar" class="form-control" value="<?= esc($domain['registrar'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Registration Date</label>
              <input type="date" name="registration_date" class="form-control" value="<?= $domain['registration_date'] ?? '' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expiry Date <span class="text-danger">*</span></label>
              <input type="date" name="expiry_date" class="form-control" value="<?= $domain['expiry_date'] ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Cost (₹)</label>
              <input type="number" name="cost" class="form-control" step="0.01" value="<?= $domain['cost'] ?? 0 ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Renewal Cost (₹)</label>
              <input type="number" name="renewal_cost" class="form-control" step="0.01" value="<?= $domain['renewal_cost'] ?? 0 ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" class="form-select">
                <?php foreach (['active','expiring_soon','expired'] as $s): ?>
                <option value="<?= $s ?>" <?= $domain['status']==$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Auto Renew</label>
              <select name="auto_renew" class="form-select">
                <option value="0" <?= !$domain['auto_renew']?'selected':'' ?>>No</option>
                <option value="1" <?= $domain['auto_renew']?'selected':'' ?>>Yes</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Notes</label>
              <textarea name="notes" class="form-control" rows="2"><?= esc($domain['notes'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
            <a href="<?= base_url('admin/domains') ?>" class="btn btn-outline-secondary">Cancel</a>
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
