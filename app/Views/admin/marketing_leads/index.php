<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex flex-wrap gap-2">
    <select id="filterClient" class="form-select form-select-sm select2" style="min-width:220px">
      <option value="">All Clients</option>
      <?php foreach ($clients as $c): ?>
        <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — ' . esc($c['company_name']) : '' ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filterStatus" class="form-select form-select-sm" style="width:150px">
      <option value="">All Status</option>
      <?php foreach (['new','contacted','interested','not_interested','converted','junk'] as $s): ?>
        <option value="<?= $s ?>"><?= ucwords(str_replace('_', ' ', $s)) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filterPlatform" class="form-select form-select-sm" style="width:150px">
      <option value="">All Platforms</option>
      <?php foreach (['facebook','instagram','google_ads','other'] as $p): ?>
        <option value="<?= $p ?>"><?= ucwords(str_replace('_', ' ', $p)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadCsvModal">
      <i class="bi bi-upload me-1"></i>Upload CSV
    </button>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLeadModal">
      <i class="bi bi-plus-lg me-1"></i>Add Lead
    </button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
    <table id="mktLeadsTable" class="table table-hover mb-0 w-100">
      <thead class="table-light">
        <tr>
          <th>Lead</th>
          <th>Client</th>
          <th>Campaign</th>
          <th>Platform</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    </div>
  </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold">Add Marketing Lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="addLeadForm">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" id="addClientSelect" class="form-select select2" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — ' . esc($c['company_name']) : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Project / Campaign Ref</label>
              <select name="project_id" id="addProjectSelect" class="form-select">
                <option value="">— No Project —</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Campaign Name</label><input type="text" name="campaign_name" class="form-control" placeholder="e.g. Diwali Sale FB Ads"></div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Platform</label>
              <select name="platform" class="form-select">
                <?php foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'google_ads' => 'Google Ads', 'other' => 'Other'] as $v => $l): ?>
                  <option value="<?= $v ?>"><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Lead Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">WhatsApp</label><input type="text" name="whatsapp" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">City</label><input type="text" name="city" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Lead Date</label><input type="date" name="lead_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Requirement</label><textarea name="requirement" class="form-control" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Lead</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Lead Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold">Edit Marketing Lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editLeadForm">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="editLeadId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" id="editClientSelect" class="form-select select2" required>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — ' . esc($c['company_name']) : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Project / Campaign Ref</label>
              <select name="project_id" id="editProjectSelect" class="form-select">
                <option value="">— No Project —</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Campaign Name</label><input type="text" name="campaign_name" id="editCampaignName" class="form-control"></div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Platform</label>
              <select name="platform" id="editPlatform" class="form-select">
                <?php foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'google_ads' => 'Google Ads', 'other' => 'Other'] as $v => $l): ?>
                  <option value="<?= $v ?>"><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Lead Name <span class="text-danger">*</span></label><input type="text" name="name" id="editName" class="form-control" required></div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" id="editStatus" class="form-select">
                <?php foreach (['new','contacted','interested','not_interested','converted','junk'] as $s): ?>
                  <option value="<?= $s ?>"><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Phone</label><input type="text" name="phone" id="editPhone" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">WhatsApp</label><input type="text" name="whatsapp" id="editWhatsapp" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" id="editEmail" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">City</label><input type="text" name="city" id="editCity" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Lead Date</label><input type="date" name="lead_date" id="editLeadDate" class="form-control"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Requirement</label><textarea name="requirement" id="editRequirement" class="form-control" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Notes</label><textarea name="notes" id="editNotes" class="form-control" rows="2"></textarea></div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Lead</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Upload CSV Modal -->
<div class="modal fade" id="uploadCsvModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold">Upload Leads CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= base_url('admin/marketing-leads/upload-csv') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
              <select name="client_id" id="csvClientSelect" class="form-select select2" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?><?= $c['company_name'] ? ' — ' . esc($c['company_name']) : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Project / Campaign Ref</label>
              <select name="project_id" id="csvProjectSelect" class="form-select">
                <option value="">— No Project —</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Default Campaign Name</label>
              <input type="text" name="campaign_name" class="form-control" placeholder="Used if CSV has no campaign_name column">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Default Platform</label>
              <select name="platform" class="form-select">
                <?php foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'google_ads' => 'Google Ads', 'other' => 'Other'] as $v => $l): ?>
                  <option value="<?= $v ?>"><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">CSV File <span class="text-danger">*</span></label>
              <input type="file" name="csv_file" class="form-control" accept=".csv" required>
              <div class="form-text">Columns supported: name (required), phone, whatsapp, email, city, campaign_name, requirement, notes, lead_date. Max 5MB.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import Leads</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  let filterClient = '', filterStatus = '', filterPlatform = '';
  const mktTable = $('#mktLeadsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/marketing-leads/datatable') ?>',
      data: d => { d.client_id = filterClient; d.status = filterStatus; d.platform = filterPlatform; }
    },
    columns: [
      { data: 'name', render: (d, t, r) => `<div><div class="fw-semibold">${d}</div><div class="text-muted" style="font-size:11px">${r.city || ''}</div></div>` },
      { data: 'client_name', render: d => d || '—' },
      { data: 'campaign_name', render: (d, t, r) => `${d || '—'}${r.project_name ? '<div class=\"text-muted\" style=\"font-size:11px\">' + r.project_name + '</div>' : ''}` },
      { data: 'platform', render: d => `<span class="badge bg-info text-dark">${(d||'').replace(/_/g,' ')}</span>` },
      { data: null, render: (d, t, r) => {
          let out = '';
          if (r.phone) out += `<a href="tel:${r.phone}" class="text-decoration-none d-block"><i class="bi bi-telephone me-1"></i>${r.phone}</a>`;
          if (r.email) out += `<div class="text-muted" style="font-size:11px">${r.email}</div>`;
          return out || '—';
        }
      },
      { data: 'status', render: d => {
          const m = { new: 'primary', contacted: 'info', interested: 'success', not_interested: 'warning', converted: 'success', junk: 'danger' };
          return `<span class="badge bg-${m[d]||'secondary'}">${(d||'').replace(/_/g,' ')}</span>`;
        }
      },
      { data: 'lead_date', render: d => d && d !== '0000-00-00' ? new Date(d).toLocaleDateString('en-IN') : '—' },
      { data: null, orderable: false, width: '110px', render: (d, t, r) => `
          <div class="d-flex gap-1">
            <button class="btn btn-xs btn-outline-warning btn-edit-mkt" data-id="${r.id}" title="Edit"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-xs btn-outline-danger btn-del-mkt" data-id="${r.id}"
                    data-confirm-title="Delete Lead?" data-confirm="Are you sure you want to delete this lead? This action cannot be undone."
                    data-confirm-yes="Yes, Delete" title="Delete"><i class="bi bi-trash"></i></button>
          </div>`
      },
    ],
    order: [[6, 'desc']],
    pageLength: 25,
    language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div>' },
  });

  $('#filterClient, #filterStatus, #filterPlatform').on('change', function() {
    filterClient = $('#filterClient').val();
    filterStatus = $('#filterStatus').val();
    filterPlatform = $('#filterPlatform').val();
    mktTable.ajax.reload();
  });

  function loadProjects(clientId, targetSelect, selectedId) {
    if (!clientId) { $(targetSelect).html('<option value="">— No Project —</option>'); return; }
    $.get(`<?= base_url('admin/ajax/projects/') ?>${clientId}`, data => {
      let opts = '<option value="">— No Project —</option>';
      data.forEach(p => opts += `<option value="${p.id}" ${selectedId == p.id ? 'selected' : ''}>${p.name}</option>`);
      $(targetSelect).html(opts);
    });
  }

  $('#addClientSelect').on('change', function() { loadProjects($(this).val(), '#addProjectSelect'); });
  $('#editClientSelect').on('change', function() { loadProjects($(this).val(), '#editProjectSelect'); });
  $('#csvClientSelect').on('change', function() { loadProjects($(this).val(), '#csvProjectSelect'); });

  $('#addLeadForm').on('submit', function(e) {
    e.preventDefault();
    $(this).find('input[name="csrf_test_name"]').val(getCsrfToken());
    showLoader('Saving lead...');
    $.post('<?= base_url('admin/marketing-leads/store') ?>', $(this).serialize(), res => {
      hideLoader();
      if (res.status === 'success') {
        showToast(res.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('addLeadModal')).hide();
        this.reset();
        $('#addProjectSelect').html('<option value="">— No Project —</option>');
        mktTable.ajax.reload(null, false);
      } else {
        showToast(res.message || 'Failed to save lead', 'error');
      }
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });

  $(document).on('click', '.btn-edit-mkt', function() {
    const id = $(this).data('id');
    showLoader('Loading lead...');
    $.get(`<?= base_url('admin/marketing-leads/edit/') ?>${id}`, res => {
      hideLoader();
      if (res.status !== 'success') { showToast(res.message || 'Lead not found', 'error'); return; }
      const l = res.data;
      $('#editLeadId').val(l.id);
      $('#editClientSelect').val(l.client_id).trigger('change.select2');
      loadProjects(l.client_id, '#editProjectSelect', l.project_id);
      $('#editCampaignName').val(l.campaign_name);
      $('#editPlatform').val(l.platform);
      $('#editName').val(l.name);
      $('#editStatus').val(l.status);
      $('#editPhone').val(l.phone);
      $('#editWhatsapp').val(l.whatsapp);
      $('#editEmail').val(l.email);
      $('#editCity').val(l.city);
      $('#editLeadDate').val(l.lead_date);
      $('#editRequirement').val(l.requirement);
      $('#editNotes').val(l.notes);
      bootstrap.Modal.getOrCreateInstance(document.getElementById('editLeadModal')).show();
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });

  $('#editLeadForm').on('submit', function(e) {
    e.preventDefault();
    $(this).find('input[name="csrf_test_name"]').val(getCsrfToken());
    const id = $('#editLeadId').val();
    showLoader('Updating lead...');
    $.post(`<?= base_url('admin/marketing-leads/update/') ?>${id}`, $(this).serialize(), res => {
      hideLoader();
      if (res.status === 'success') {
        showToast(res.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('editLeadModal')).hide();
        mktTable.ajax.reload(null, false);
      } else {
        showToast(res.message || 'Failed to update lead', 'error');
      }
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
  });

  let delId = null;
  $(document).on('click', '.btn-del-mkt', function() {
    delId = $(this).data('id');
    $('#ngConfirmTitle').text($(this).data('confirm-title') || 'Are you sure?');
    $('#ngConfirmMessage').text($(this).data('confirm') || 'Please confirm this action.');
    $('#ngConfirmYes').text($(this).data('confirm-yes') || 'Yes');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal')).show();
  });
  $('#ngConfirmYes').off('click.mkt').on('click.mkt', function() {
    if (!delId) return;
    const modal = bootstrap.Modal.getInstance(document.getElementById('ngConfirmModal'));
    modal.hide();
    showLoader('Deleting lead...');
    $.post(`<?= base_url('admin/marketing-leads/delete/') ?>${delId}`, { csrf_test_name: getCsrfToken() }, res => {
      hideLoader();
      if (res.status === 'success') { showToast(res.message, 'success'); mktTable.ajax.reload(null, false); }
      else showToast(res.message || 'Delete failed', 'error');
    }, 'json').fail(() => { hideLoader(); showToast('Server error. Please try again.', 'error'); });
    delId = null;
  });

  $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
</script>
<?= $this->endSection() ?>
