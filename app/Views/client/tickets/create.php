<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-md-7">
    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h5 class="mb-0 fw-bold">Raise a Support Ticket</h5></div>
      <div class="card-body">
        <form action="<?= base_url('portal/tickets/store') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control" value="<?= old('subject') ?>" required placeholder="Brief summary of your issue">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Priority</label>
              <select name="priority" class="form-select">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Related Project</label>
              <select name="project_id" class="form-select">
                <option value="">None / General</option>
                <?php foreach ($projects ?? [] as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="5" required placeholder="Please describe your issue in detail..."><?= old('description') ?></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Ticket</button>
            <a href="<?= base_url('portal/tickets') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
