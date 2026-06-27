<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold"><?= esc($agreement['title']) ?></h5>
          <span class="badge bg-info">Pending Signature</span>
        </div>
        <div class="text-muted small"><?= esc($agreement['agreement_number']) ?></div>
      </div>
      <div class="card-body p-4 p-md-5">
        <?php if ($agreement['content'] ?? null): ?>
        <div class="border rounded p-4 bg-light mb-4" style="max-height:400px;overflow-y:auto;font-size:14px;line-height:1.7">
          <?= nl2br(esc($agreement['content'])) ?>
        </div>
        <?php endif; ?>

        <?php if ($agreement['terms'] ?? null): ?>
        <div class="border rounded p-4 bg-light mb-4" style="max-height:300px;overflow-y:auto;font-size:13px;line-height:1.7">
          <h6 class="fw-bold mb-2">Terms &amp; Conditions</h6>
          <?= nl2br(esc($agreement['terms'])) ?>
        </div>
        <?php endif; ?>

        <div class="alert alert-info d-flex align-items-start gap-2">
          <i class="bi bi-info-circle fs-5 flex-shrink-0 mt-1"></i>
          <div>
            <strong>Before signing:</strong> Please read the entire agreement carefully. By clicking "Sign Agreement" you are digitally signing this document. Your IP address and timestamp will be recorded.
          </div>
        </div>

        <form action="<?= base_url('portal/agreements/sign/'.$agreement['id']) ?>" method="POST">
          <?= csrf_field() ?>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agreeCheck" required>
            <label class="form-check-label" for="agreeCheck">
              I have read, understood, and agree to all terms and conditions stated in this agreement.
            </label>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" name="action" value="sign" class="btn btn-success btn-lg">
              <i class="bi bi-pen me-2"></i>Sign Agreement
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-outline-danger">
              <i class="bi bi-x-circle me-1"></i>Decline
            </button>
          </div>
        </form>
      </div>
    </div>
    <div class="text-center">
      <a href="<?= base_url('portal/agreements') ?>" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Agreements</a>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
