<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-10">
    <form action="<?= base_url('admin/proposals/update/'.$proposal['id']) ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row g-4">
        <div class="col-md-8">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-semibold">Edit Proposal</h6>
              <span class="badge bg-secondary"><?= esc($proposal['proposal_number']) ?></span>
            </div>
            <div class="card-body row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Client</label>
                <select name="client_id" class="form-select select2">
                  <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $proposal['client_id']==$c['id']?'selected':'' ?>><?= esc($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Title *</label>
                <input type="text" name="title" class="form-control" value="<?= esc($proposal['title']) ?>" required>
              </div>
              <div class="col-12"><label class="form-label small fw-semibold">Introduction</label><textarea name="introduction" class="form-control" rows="3"><?= esc($proposal['introduction'] ?? '') ?></textarea></div>
              <div class="col-12"><label class="form-label small fw-semibold">Project Overview</label><textarea name="project_overview" class="form-control" rows="3"><?= esc($proposal['project_overview'] ?? '') ?></textarea></div>
              <div class="col-12"><label class="form-label small fw-semibold">Scope of Work</label><textarea name="scope_of_work" class="form-control" rows="4"><?= esc($proposal['scope_of_work'] ?? '') ?></textarea></div>
              <div class="col-12"><label class="form-label small fw-semibold">Deliverables</label><textarea name="deliverables" class="form-control" rows="3"><?= esc($proposal['deliverables'] ?? '') ?></textarea></div>
              <div class="col-12"><label class="form-label small fw-semibold">Timeline</label><textarea name="timeline" class="form-control" rows="2"><?= esc($proposal['timeline'] ?? '') ?></textarea></div>
              <div class="col-12"><label class="form-label small fw-semibold">Pricing Details</label><textarea name="pricing" class="form-control" rows="3"><?= esc($proposal['pricing'] ?? '') ?></textarea></div>
              <div class="col-12"><label class="form-label small fw-semibold">Terms & Conditions</label><textarea name="terms" class="form-control" rows="4"><?= esc($proposal['terms'] ?? '') ?></textarea></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Settings</h6></div>
            <div class="card-body row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Total Amount (₹) *</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" name="total_amount" class="form-control" step="0.01" value="<?= $proposal['total_amount'] ?>" required>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Valid Until</label>
                <input type="date" name="valid_until" class="form-control" value="<?= $proposal['valid_until'] ?? '' ?>">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                  <?php foreach (['draft','sent','accepted','rejected'] as $s): ?>
                  <option value="<?= $s ?>" <?= $proposal['status']==$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12"><label class="form-label small fw-semibold">Notes (internal)</label><textarea name="notes" class="form-control" rows="2"><?= esc($proposal['notes'] ?? '') ?></textarea></div>
            </div>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
            <a href="<?= base_url('admin/proposals/'.$proposal['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });</script>
<?= $this->endSection() ?>
