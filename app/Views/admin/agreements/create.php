<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-10">
    <form action="<?= base_url('admin/agreements/store') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row g-4">

        <div class="col-md-8">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Agreement Details</h6></div>
            <div class="card-body row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
                <select name="client_id" id="clientSel" class="form-select select2" required>
                  <option value="">Select Client</option>
                  <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Project</label>
                <select name="project_id" id="projectSel" class="form-select">
                  <option value="">Select client first</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Agreement Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Web Development Agreement — Bong Villa">
              </div>
            </div>
          </div>

          <!-- Agreement Sections -->
          <?php
          $sections = [
            ['field'=>'client_information',   'label'=>'Client Information',   'placeholder'=>"Client's business overview, point of contact, address..."],
            ['field'=>'project_information',  'label'=>'Project Information',  'placeholder'=>"Project scope, objectives, technology stack..."],
            ['field'=>'deliverables',         'label'=>'Deliverables',         'placeholder'=>"List of items to be delivered, formats, platforms..."],
            ['field'=>'timeline',             'label'=>'Timeline',             'placeholder'=>"Phase-wise timeline with start and end dates..."],
            ['field'=>'payment_terms',        'label'=>'Payment Terms',        'placeholder'=>"Payment schedule, milestones, late payment policy..."],
            ['field'=>'support_terms',        'label'=>'Support & Maintenance','placeholder'=>"Post-delivery support period, SLA, contact hours..."],
            ['field'=>'cancellation_terms',   'label'=>'Cancellation Terms',   'placeholder'=>"Notice period, refund policy, handover process..."],
            ['field'=>'terms_conditions',     'label'=>'General Terms & Conditions','placeholder'=>"Confidentiality, IP ownership, governing law..."],
          ];
          ?>
          <?php foreach ($sections as $s): ?>
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><?= $s['label'] ?></h6></div>
            <div class="card-body">
              <textarea name="<?= $s['field'] ?>" class="form-control" rows="4" placeholder="<?= $s['placeholder'] ?>"></textarea>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm mb-3 sticky-top" style="top:80px">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Settings</h6></div>
            <div class="card-body row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                  <option value="draft" selected>Save as Draft</option>
                  <option value="sent">Mark as Sent (ready to sign)</option>
                </select>
              </div>
              <div class="col-12 d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Create Agreement</button>
                <a href="<?= base_url('admin/agreements') ?>" class="btn btn-outline-secondary">Cancel</a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>';
if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme:'bootstrap-5', width:'100%' });

$('#clientSel').on('change', function() {
  const cid = $(this).val();
  if (!cid) { $('#projectSel').html('<option value="">Select client first</option>'); return; }
  $.get(`${BASE}admin/ajax/projects/${cid}`, data => {
    let opts = '<option value="">— No Project —</option>';
    data.forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
    $('#projectSel').html(opts);
  });
});
</script>
<?= $this->endSection() ?>
