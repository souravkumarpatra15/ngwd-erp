<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - NGWebD ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css"
          integrity="sha512-...", crossorigin="anonymous">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-check-circle me-1"></i> Certificate Verification
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if ($notFound): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i> Certificate not found.
                                <p>The certificate number <?= $certificateNumber ?> could not be found in our system.</p>
                            </div>
                        <?php elseif ($certificate && $certificate['valid']): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i> Certificate is VALID
                            </div>
                            
                            <h6>Verification Details</h6>
                            <dl class="row">
                                <dt class="col-sm-3">Intern Name</dt>
                                <dd class="col-sm-9"><?= $internName ?></dd>
                                
                                <dt class="col-sm-3">Internship Programme</dt>
                                <dd class="col-sm-9"><?= $internRole ?></dd>
                                
                                <dt class="col-sm-3">Duration</dt>
                                <dd class="col-sm-9"><?= $durationMonths ?></dd>
                                
                                <dt class="col-sm-3">Final Evaluation</dt>
                                <dd class="col-sm-9"><?= $finalScore ?></dd>
                                
                                <dt class="col-sm-3">Certificate Number</dt>
                                <dd class="col-sm-9"><?= $certificateNumber ?></dd>
                                
                                <dt class="col-sm-3">Issue Date</dt>
                                <dd class="col-sm-9"><?= $issuedDate ?></dd>
                                
                                <dt class="col-sm-3">Organization</dt>
                                <dd class="col-sm-9"><?= $organization ?></dd>
                            </dl>
                            
                            <p class="mt-3">
                                <small>This certificate was issued by NGWebD Consulting Pvt. Ltd.</small>
                                <br>
                                <small>For any queries, please contact the HR department.</small>
                            </p>
                            
                        <?php elseif ($certificate && !$certificate['valid']): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i> Certificate is NOT valid (revoked or expired).
                            </div>
                            <p>This certificate has been revoked or expired and is no longer valid for verification.</p>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"
            integrity="sha512-...", crossorigin="anonymous"></script>
</body>
</html>