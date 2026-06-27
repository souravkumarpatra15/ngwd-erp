<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-10">
    <form action="<?= base_url('admin/proposals/store') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row g-4">

        <div class="col-md-8">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Proposal Details</h6></div>
            <div class="card-body row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select select2" required>
                  <option value="">Select Client</option>
                  <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= old('client_id')==$c['id']?'selected':'' ?>><?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required value="<?= old('title') ?>" placeholder="e.g. Website Development Proposal">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Introduction</label>
                <textarea name="introduction" class="form-control" rows="3" placeholder="Brief introduction..."><?= old('introduction') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Project Overview</label>
                <textarea name="project_overview" class="form-control" rows="3" placeholder="Project background and goals..."><?= old('project_overview') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Scope of Work</label>
                <textarea name="scope_of_work" class="form-control" rows="4" placeholder="Detailed scope and deliverables..."><?= old('scope_of_work') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Deliverables</label>
                <textarea name="deliverables" class="form-control" rows="3" placeholder="List what will be delivered..."><?= old('deliverables') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Timeline</label>
                <textarea name="timeline" class="form-control" rows="2" placeholder="Phase-wise timeline..."><?= old('timeline') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Pricing Details</label>
                <textarea name="pricing" class="form-control" rows="3" placeholder="Breakdown of pricing..."><?= old('pricing') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Terms & Conditions</label>
                <textarea name="terms" class="form-control" rows="4"><?= old('terms', $default_terms ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Settings</h6></div>
            <div class="card-body row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Total Amount (₹) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" name="total_amount" class="form-control" step="0.01" min="0" value="<?= old('total_amount') ?>" required placeholder="0.00">
                </div>
                <div class="form-text">Enter the all-inclusive total for the client.</div>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Valid Until</label>
                <input type="date" name="valid_until" class="form-control" value="<?= old('valid_until', date('Y-m-d', strtotime('+30 days'))) ?>">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                  <option value="draft" selected>Save as Draft</option>
                  <option value="sent">Mark as Sent</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Notes (internal)</label>
                <textarea name="notes" class="form-control" rows="2"><?= old('notes') ?></textarea>
              </div>
            </div>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Create Proposal</button>
            <a href="<?= base_url('admin/proposals') ?>" class="btn btn-outline-secondary">Cancel</a>
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
