<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-xl-10">
    <form action="<?= base_url('admin/agreements/update/'.$agreement['id']) ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row g-4">

        <div class="col-md-8">
          <!-- Header card -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-semibold">Edit Agreement</h6>
              <span class="badge bg-secondary"><?= esc($agreement['agreement_number']) ?></span>
            </div>
            <div class="card-body row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
                <select name="client_id" id="clientSel" class="form-select select2" required>
                  <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $agreement['client_id']==$c['id']?'selected':'' ?>>
                    <?= esc($c['name']) ?><?= $c['company_name'] ? ' — '.$c['company_name'] : '' ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Project</label>
                <select name="project_id" id="projectSel" class="form-select">
                  <option value="">— No Project —</option>
                  <?php foreach ($projects as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= ($agreement['project_id']==$p['id'])?'selected':'' ?>><?= esc($p['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Agreement Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= esc($agreement['title']) ?>" required>
              </div>
            </div>
          </div>

          <!-- Agreement sections -->
          <?php
          $sections = [
            'client_information'  => 'Client Information',
            'project_information' => 'Project Information',
            'deliverables'        => 'Deliverables',
            'timeline'            => 'Timeline',
            'payment_terms'       => 'Payment Terms',
            'support_terms'       => 'Support & Maintenance',
            'cancellation_terms'  => 'Cancellation Terms',
            'terms_conditions'    => 'General Terms & Conditions',
          ];
          ?>
          <?php foreach ($sections as $field => $label): ?>
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><?= $label ?></h6></div>
            <div class="card-body">
              <textarea name="<?= $field ?>" class="form-control" rows="4"><?= esc($agreement[$field] ?? '') ?></textarea>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm sticky-top" style="top:80px">
            <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">Settings</h6></div>
            <div class="card-body row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select">
                  <?php foreach (['draft'=>'Draft','sent'=>'Sent','signed'=>'Signed','rejected'=>'Rejected'] as $val => $lbl): ?>
                  <option value="<?= $val ?>" <?= $agreement['status']==$val?'selected':'' ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 d-grid gap-2 mt-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
                <a href="<?= base_url('admin/agreements/'.$agreement['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
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
const currentProjectId = '<?= $agreement['project_id'] ?>';

if (typeof $.fn.select2 !== 'undefined') $('.select2').select2({ theme:'bootstrap-5', width:'100%' });

// Reload projects when client changes
$('#clientSel').on('change', function() {
  const cid = $(this).val();
  if (!cid) { $('#projectSel').html('<option value="">— No Project —</option>'); return; }
  $.get(`${BASE}admin/ajax/projects/${cid}`, data => {
    let opts = '<option value="">— No Project —</option>';
    data.forEach(p => opts += `<option value="${p.id}" ${p.id==currentProjectId?'selected':''}>${p.name}</option>`);
    $('#projectSel').html(opts);
  });
});
</script>
<?= $this->endSection() ?>
