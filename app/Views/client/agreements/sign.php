<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-check me-2 text-primary"></i><?= esc($agreement['title']) ?></h5>
        <div class="text-muted small">Agreement #<?= esc($agreement['agreement_number']) ?></div>
      </div>
      <div class="card-body">
        <?php if ($agreement['client_information']): ?><div class="mb-3"><h6 class="text-primary">Client Information</h6><?= nl2br(esc($agreement['client_information'])) ?></div><?php endif; ?>
        <?php if ($agreement['deliverables']): ?><div class="mb-3"><h6 class="text-primary">Deliverables</h6><?= nl2br(esc($agreement['deliverables'])) ?></div><?php endif; ?>
        <?php if ($agreement['payment_terms']): ?><div class="mb-3"><h6 class="text-primary">Payment Terms</h6><?= nl2br(esc($agreement['payment_terms'])) ?></div><?php endif; ?>
        <?php if ($agreement['terms_conditions']): ?><div class="mb-3"><h6 class="text-primary">Terms & Conditions</h6><div style="font-size:13px"><?= nl2br(esc($agreement['terms_conditions'])) ?></div></div><?php endif; ?>

        <div class="alert alert-warning mt-3">
          <i class="bi bi-shield-exclamation me-2"></i><strong>Digital Signature</strong><br>
          By clicking "I Agree & Sign", you acknowledge that you have read and agree to all terms in this agreement. Your IP address and timestamp will be recorded as your digital signature.
        </div>

        <form action="<?= base_url('portal/agreements/sign/'.$agreement['id']) ?>" method="POST" class="mt-3">
          <?= csrf_field() ?>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agreeCheck" required>
            <label class="form-check-label" for="agreeCheck">I have read and agree to all terms and conditions in this agreement.</label>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" name="action" value="sign" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>I Agree & Sign</button>
            <button type="submit" name="action" value="reject" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to reject this agreement?')"><i class="bi bi-x-circle me-2"></i>Reject</button>
            <a href="<?= base_url('portal/agreements') ?>" class="btn btn-outline-secondary">Back</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
