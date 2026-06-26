<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-status="">All</button>
    <button class="btn btn-sm btn-outline-primary filter-btn" data-status="new">New</button>
    <button class="btn btn-sm btn-outline-warning filter-btn" data-status="follow_up">Follow Up</button>
    <button class="btn btn-sm btn-outline-info filter-btn" data-status="proposal_sent">Proposal Sent</button>
    <button class="btn btn-sm btn-outline-success filter-btn" data-status="converted">Converted</button>
    <button class="btn btn-sm btn-outline-danger filter-btn" data-status="lost">Lost</button>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLeadModal">
    <i class="bi bi-plus-lg me-1"></i>Add Lead
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table id="leadsTable" class="table table-hover mb-0 w-100">
      <thead class="table-light">
        <tr>
          <th>Lead #</th>
          <th>Name</th>
          <th>Mobile</th>
          <th>Source</th>
          <th>Budget</th>
          <th>Status</th>
          <th>Follow Up</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold">Add New Lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= base_url('admin/leads/store') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Company</label><input type="text" name="company_name" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Mobile <span class="text-danger">*</span></label><input type="text" name="mobile" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">WhatsApp</label><input type="text" name="whatsapp" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Source <span class="text-danger">*</span></label>
              <select name="source" class="form-select" required>
                <option value="">Select Source</option>
                <?php foreach (['facebook', 'instagram', 'whatsapp', 'google_ads', 'website', 'phone', 'referral', 'linkedin', 'manual'] as $s): ?>
                  <option value="<?= $s ?>"><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Budget (₹)</label><input type="number" name="budget" class="form-control"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Follow Up Date</label><input type="date" name="follow_up_date" class="form-control"></div>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  let currentStatus = '';
  const leadsTable = $('#leadsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/leads/datatable') ?>',
      data: d => {
        d.status = currentStatus;
      }
    },
    columns: [{
        data: 'lead_number',
        width: '100px'
      },
      {
        data: 'name',
        render: (d, t, r) => `<div><a href="<?= base_url('admin/leads/') ?>${r.id}" class="fw-semibold text-decoration-none">${d}</a><div class="text-muted" style="font-size:11px">${r.company_name||''}</div></div>`
      },
      {
        data: 'mobile',
        render: d => `<a href="tel:${d}" class="text-decoration-none">${d}</a>`
      },
      {
        data: 'source',
        render: d => `<span class="badge bg-info text-dark">${d.replace(/_/g,' ')}</span>`
      },
      {
        data: 'budget',
        render: d => d ? '₹' + parseInt(d).toLocaleString('en-IN') : '—'
      },
      {
        data: 'status',
        render: d => {
          const m = {
            new: 'primary',
            contacted: 'info',
            follow_up: 'warning',
            proposal_sent: 'secondary',
            negotiation: 'purple',
            converted: 'success',
            lost: 'danger'
          };
          return `<span class="badge bg-${m[d]||'secondary'}">${d.replace(/_/g,' ')}</span>`;
        }
      },
      {
        data: 'follow_up_date',
        render: d => d && d !== '0000-00-00' ? new Date(d).toLocaleDateString('en-IN') : '—'
      },
      {
        data: 'created_at',
        render: d => new Date(d).toLocaleDateString('en-IN')
      },
      {
        data: null,
        orderable: false,
        width: '130px',
        render: (d, t, r) => {
          const isConverted = r.status === 'converted';

          return `
      <div class="d-flex gap-1">
        <a href="<?= base_url('admin/leads/') ?>${r.id}"
           class="btn btn-xs btn-outline-primary"
           title="View">
          <i class="bi bi-eye"></i>
        </a>

        <a href="<?= base_url('admin/leads/edit/') ?>${r.id}"
           class="btn btn-xs btn-outline-warning"
           title="Edit">
          <i class="bi bi-pencil"></i>
        </a>

        ${
          isConverted
          ? `
            <button class="btn btn-xs btn-success" disabled title="Already Converted">
              <i class="bi bi-person-check-fill"></i>
            </button>
          `
          : `
            <button class="btn btn-xs btn-outline-success btn-convert"
                    data-id="${r.id}"
                    data-confirm-title="Convert Lead?"
                    data-confirm="Are you sure you want to convert this lead to a client?"
                    data-confirm-yes="Yes, Convert"
                    title="Convert">
              <i class="bi bi-person-check"></i>
            </button>
          `
        }

        <button class="btn btn-xs btn-outline-danger btn-del-lead"
                data-id="${r.id}"
                data-confirm-title="Delete Lead?"
                data-confirm="Are you sure you want to delete this lead? This action cannot be undone."
                data-confirm-yes="Yes, Delete"
                title="Delete">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    `;
        }
      },
    ],
    order: [
      [7, 'desc']
    ],
    pageLength: 25,
    language: {
      processing: '<div class="spinner-border spinner-border-sm text-primary"></div>'
    },
  });
  $('.filter-btn').on('click', function() {
    currentStatus = $(this).data('status');
    $('.filter-btn').removeClass('active');
    $(this).addClass('active');
    leadsTable.ajax.reload();
  });
  let leadActionType = null;
  let leadActionId = null;

  $(document).on('click', '.btn-convert, .btn-del-lead', function(e) {
    e.preventDefault();

    leadActionId = $(this).data('id');

    if ($(this).hasClass('btn-convert')) {
      leadActionType = 'convert';
    }

    if ($(this).hasClass('btn-del-lead')) {
      leadActionType = 'delete';
    }

    $('#ngConfirmTitle').text($(this).data('confirm-title') || 'Are you sure?');
    $('#ngConfirmMessage').text($(this).data('confirm') || 'Please confirm this action.');
    $('#ngConfirmYes').text($(this).data('confirm-yes') || 'Yes');

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('ngConfirmModal'));
    modal.show();
  });

  $('#ngConfirmYes').off('click').on('click', function() {
    if (!leadActionId || !leadActionType) return;

    const modalEl = document.getElementById('ngConfirmModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    if (leadActionType === 'convert') {
      showLoader('Converting lead...');

      $.post(`<?= base_url('admin/leads/convert/') ?>${leadActionId}`, {
        csrf_test_name: CSRF_TOKEN
      }, res => {
        hideLoader();

        if (res.status === 'success') {
          showToast('Lead converted to client successfully!', 'success');
          leadsTable.ajax.reload(null, false);
        } else {
          showToast(res.message || 'Lead convert failed', 'error');
        }
      }, 'json').fail(() => {
        hideLoader();
        showToast('Server error. Please try again.', 'error');
      });
    }

    if (leadActionType === 'delete') {
      showLoader('Deleting lead...');

      $.post(`<?= base_url('admin/leads/delete/') ?>${leadActionId}`, {
        csrf_test_name: CSRF_TOKEN
      }, res => {
        hideLoader();

        if (res.status === 'success') {
          showToast('Lead deleted successfully', 'warning');
          leadsTable.ajax.reload(null, false);
        } else {
          showToast(res.message || 'Lead delete failed', 'error');
        }
      }, 'json').fail(() => {
        hideLoader();
        showToast('Server error. Please try again.', 'error');
      });
    }

    leadActionType = null;
    leadActionId = null;
  });
</script>
<?= $this->endSection() ?>