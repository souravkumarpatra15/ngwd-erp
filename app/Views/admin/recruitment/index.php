<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - NGWebD ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin-modern.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin-app-theme.css') ?>">
</head>
<body>
    <div class="container-fluid">
        <div class="page-header">
            <h1><?= esc($title) ?></h1>
            <a href="<?= base_url('admin/recruitment/createProgram') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Internship Program
            </a>
        </div>

        <?php if (empty($programs)): ?>
        <div class="alert alert-info">
            <h4>No Active Internship Programs</h4>
            <p>There are currently no active internship programs. Create a new program to start receiving applications.</p>
            <a href="<?= base_url('admin/recruitment/createProgram') ?>" class="btn btn-secondary mt-2">Create First Program</a>
        </div>
        <?php else: ?>
        <div class="row gutters-sm">
            <?php foreach ($programs as $program): ?>
            <div class="col-xl-4 col-md-6">
                <div class="info-card text white small">
                    <div class="info-card-icon bg-primary">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="info-card-data">
                        <div class="info-card-number h2"><?= esc($program['title'] ?? 'N/A') ?></div>
                        <div class="info-card-text">Internship Programme</div>
                        <div class="small"><?= $program['start_date'] ? date('M Y', strtotime($program['start_date'])) : '—' ?> - <?= $program['end_date'] ? date('M Y', strtotime($program['end_date'])) : '—' ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Application Pipeline</h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Filter by Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('admin/recruitment') ?>">All Applications</a></li>
                                <?php 
                                $statuses = ['applied', 'under_review', 'shortlisted', 'interview_scheduled', 'interview_completed', 'offer_sent', 'offer_accepted', 'onboarding', 'joined', 'rejected', 'withdrawn', 'on_hold'];
                                foreach ($statuses as $s): 
                                    $label = ucfirst(str_replace('_', ' ', $s));
                                ?>
                                <li><a class="dropdown-item" href="<?= base_url('admin/recruitment?status=' . $s) ?>"><?= $label ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div table-responsive>
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Applicant</th>
                                        <th>Program</th>
                                        <th>Preferred Role</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = (($pager->getCurrentPage() ?? 1) - 1) * ($pager->getPerPage() ?? 20);
                                    foreach ($applications as $app): $i++; 
                                        $statusLabel = ucfirst(str_replace('_', ' ', $app['status']));
                                        $programTitle = $app['program_title'] ?? 'N/A';
                                    ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <a href="javascript:void(0)" class="application-link" data-app-id="<?= $app['id'] ?>">
                                                <?= esc($app['applicant_name'] ?? '—') ?>
                                            </a>
                                        </td>
                                        <td><?= esc($programTitle) ?></td>
                                        <td><?= esc($app['preferred_role'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                ((in_array($app['status'], ['shortlisted', 'interview_completed', 'offer_accepted', 'onboarding', 'joined'])) ? 'success' : 
                                                (($app['status'] === 'rejected' || $app['status'] === 'withdrawn') ? 'danger' : 
                                                (($app['status'] === 'applied' || $app['status'] === 'under_review') ? 'info' : 'warning')))
                                            ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </td>
                                        <td><?= $app['applied_at'] ? date('M d, Y', strtotime($app['applied_at'])) : '—' ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="viewApplication(<?= $app['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php $auth ??= new \App\Services\PmsAuthorizationService(); ?>
                                                <?php if ($auth->canViewRecruitment(session()->get('user_role'))): ?>
                                                <button type="button" class="btn btn-outline-secondary" onclick="editApplication(<?= $app['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer small text-muted">
                            <?php $firstItem = (($pager->getCurrentPage() ?? 1) - 1) * ($pager->getPerPage() ?? 20) + 1; $lastItem = $firstItem + count($applications) - 1; ?>
                            Showing <?= $firstItem ?>-<?= max($lastItem, 0) ?> of <?= $pager->getTotal() ?> entries
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function viewApplication(appId) {
        $.ajax({
            url: '<?= base_url("admin/recruitment/application/show/") ?>' + appId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    const app = response.data;
                    alert('Application Details:\n\nReference: ' + app.reference_number + '\nName: ' + (app.applicant_name || 'N/A') + '\nEmail: ' + (app.applicant_email || 'N/A') + '\nMobile: ' + (app.applicant_mobile || 'N/A') + '\nProgram: ' + (app.program_title || 'N/A') + '\nRole: ' + (app.preferred_role || 'N/A') + '\nStatus: ' + app.status + '\nApplied: ' + (app.applied_at ? dateFormat(app.applied_at, 'mediumDate') : 'N/A'));
                }
            }
        });
    }

    function editApplication(appId) {
        window.location.href = '<?= base_url("admin/recruitment/application/edit/") ?>' + appId;
    }
    </script>
</body>
</html>