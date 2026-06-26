<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <form action="<?= base_url('admin/leads/store') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Name *</label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Company</label>
          <input type="text" name="company_name" class="form-control">
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Mobile *</label>
          <input type="text" name="mobile" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">WhatsApp</label>
          <input type="text" name="whatsapp" class="form-control">
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Email</label>
          <input type="email" name="email" class="form-control">
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Source *</label>
          <select name="source" class="form-select" required>
            <option value="">Select Source</option>
            <?php foreach (['facebook', 'instagram', 'whatsapp', 'google_ads', 'website', 'phone', 'referral', 'linkedin', 'manual'] as $s): ?>
              <option value="<?= $s ?>"><?= ucwords(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Budget ₹</label>
          <input type="number" name="budget" class="form-control">
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Follow Up Date</label>
          <input type="date" name="follow_up_date" class="form-control">
        </div>

        <div class="col-12">
          <label class="form-label small fw-semibold">Requirement</label>
          <textarea name="requirement" class="form-control" rows="3"></textarea>
        </div>

        <div class="col-12">
          <label class="form-label small fw-semibold">Notes</label>
          <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= base_url('admin/leads') ?>" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary">Save Lead</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>