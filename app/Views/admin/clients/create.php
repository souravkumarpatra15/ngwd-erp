<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <small class="text-muted">Create a new client profile</small>
  </div>
  <a href="<?= base_url('admin/clients') ?>" class="btn btn-secondary btn-sm">
    Back
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">

    <form action="<?= base_url('admin/clients/store') ?>" method="post">
      <?= csrf_field() ?>

      <div class="row">

        <div class="col-md-6 mb-3">
          <label class="form-label">Client Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control"
            value="<?= old('name') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Company Name</label>
          <input type="text" name="company_name" class="form-control"
            value="<?= old('company_name') ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Phone <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control"
            value="<?= old('phone') ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control"
            value="<?= old('email') ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">GST Number</label>
          <input type="text" name="gst_number" class="form-control"
            value="<?= old('gst_number') ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="active" <?= old('status') == 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= old('status') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>

        <div class="col-md-12 mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="3"><?= old('address') ?></textarea>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">City</label>
          <input type="text" name="city" class="form-control"
            value="<?= old('city') ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">State</label>
          <input type="text" name="state" class="form-control"
            value="<?= old('state') ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Country</label>
          <input type="text" name="country" class="form-control"
            value="<?= old('country') ?: 'India' ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Pincode</label>
          <input type="text" name="pincode" class="form-control"
            value="<?= old('pincode') ?>">
        </div>

        <div class="col-md-12 mb-3">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" rows="3"><?= old('notes') ?></textarea>
        </div>

      </div>

      <div class="d-flex justify-content-end gap-2">
        <a href="<?= base_url('admin/clients') ?>" class="btn btn-light">
          Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          Save Client
        </button>
      </div>

    </form>

  </div>
</div>

<?= $this->endSection() ?>