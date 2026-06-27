<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <small class="text-muted">Manage all clients</small>
    </div>

    <a href="<?= base_url('admin/clients/create') ?>" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Add Client
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table id="clientsTable" class="table table-bordered table-striped align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client No</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th width="130">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    let clientsTable;
    let clientActionId = null;
    let clientActionType = null;

    document.addEventListener('DOMContentLoaded', function() {
        clientsTable = $('#clientsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "<?= base_url('admin/clients/datatable') ?>",
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'client_number'
                },
                {
                    data: 'name'
                },
                {
                    data: 'company_name',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'email',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'phone'
                },
                {
                    data: 'status',
                    render: function(data) {
                        let status = data || 'active';
                        let cls = status === 'active' ? 'success' : 'secondary';
                        return `<span class="badge bg-${cls}">${status}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(id) {
                        return `
                        <div class="d-flex justify-content-center gap-1">
                            <a href="<?= base_url('admin/clients') ?>/${id}"
                               class="btn btn-sm btn-info"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="<?= base_url('admin/clients/edit') ?>/${id}"
                               class="btn btn-sm btn-warning"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <button type="button"
                                    onclick="deleteClient(${id})"
                                    class="btn btn-sm btn-danger"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    }
                }
            ]
        });

        window.deleteClient = function(id) {
            clientActionId = id;
            clientActionType = 'delete';

            $('#ngConfirmTitle').text('Delete Client?');
            $('#ngConfirmMessage').text('Are you sure you want to delete this client?');
            $('#ngConfirmYes').text('Yes, Delete');

            const modalEl = document.getElementById('ngConfirmModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        let CSRF_TOKEN = "<?= csrf_hash() ?>";

        $('#ngConfirmYes').off('click').on('click', function() {
            if (!clientActionId || !clientActionType) return;

            const modalEl = document.getElementById('ngConfirmModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            if (clientActionType === 'delete') {
                showLoader('Deleting client...');

                $.post(`<?= base_url('admin/clients/delete/') ?>${clientActionId}`, {
                    csrf_test_name: CSRF_TOKEN
                }, res => {
                    hideLoader();
                    if (res.csrf) {
                        CSRF_TOKEN = res.csrf;
                    }

                    if (res.status === 'success') {
                        showToast('Client deleted successfully', res.status);
                        clientsTable.ajax.reload(null, false);
                    } else {
                        showToast(res.message || 'Client delete failed', 'error');
                    }
                }, 'json').fail(() => {
                    hideLoader();
                    showToast('Server error. Please try again.', 'error');
                });
            }

            clientActionId = null;
            clientActionType = null;
        });
    });
</script>

<?= $this->endSection() ?>