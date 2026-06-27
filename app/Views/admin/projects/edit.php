<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-10">
    <form action="<?= base_url('admin/projects/update/'.$project['id']) ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row g-4">

        <div class="col-md-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-semibold">Edit Project</h6>
              <span class="badge bg-secondary"><?= esc($project['project_number']) ?></span>
            </div>
            <div class="card-body row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Project Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= esc($project['name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select select2" required>
                  <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $project['client_id'] == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Project Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                  <?php foreach (['website','ecommerce','mobile_app','software','crm','erp','seo','digital_marketing','hosting','domain'] as $t): ?>
                  <option value="<?= $t ?>" <?= $project['type'] == $t ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= esc($project['description'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Notes</label>
                <textarea name="notes" class="form-control" rows="2"><?= esc($project['notes'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Timeline & Budget</h6></div>
            <div class="card-body row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $project['start_date'] ?? '' ?>">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Delivery Date</label>
                <input type="date" name="delivery_date" class="form-control" value="<?= $project['delivery_date'] ?? '' ?>">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Budget (₹)</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" name="budget" class="form-control" value="<?= $project['budget'] ?? '' ?>" step="0.01">
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Advance Paid (₹)</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" name="advance_paid" class="form-control" value="<?= $project['advance_paid'] ?? '0' ?>" step="0.01">
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                  <?php foreach (['pending','development','testing','revision','completed','on_hold','cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $project['status'] == $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
            <a href="<?= base_url('admin/projects/'.$project['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
</script>
<?= $this->endSection() ?>
