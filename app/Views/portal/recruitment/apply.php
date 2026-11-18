<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - NGWebD ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/ng-ui.css') ?>">
</head>
<body>
    <div class="container-fluid">
        <?php if ($noProgramsAvailable): ?>
        <div class="alert alert-info">
            <h4>No Active Internship Programmes</h4>
            <p>There are currently no active internship programs accepting applications. Please check back later or contact HR for more information.</p>
        </div>
        <?php elseif ($program): ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= esc($program['title']) ?></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4>Programme Details</h4>
                        <dl class="dl-horizontal">
                            <dt>Description</dt>
                            <dd><?= esc($program['description'] ?? 'No description available') ?></dd>
                            <dt>Duration</dt>
                            <dd><?= esc($program['duration_months'] ?? '6') ?> Months</dd>
                            <dt>Mode</dt>
                            <dd><?= ucfirst($program['mode'] ?? 'remote') ?></dd>
                            <dt>Weekly Commitment</dt>
                            <dd><?= esc($program['weekly_hours'] ?? '20') ?> hours</dd>
                            <dt>Eligibility</dt>
                            <dd><?= esc($program['eligibility_criteria'] ?? 'Freshers Only') ?></dd>
                            <dt>Batch Size</dt>
                            <dd><?= esc($program['batch_size_min'] ?? '3') ?>-<?= esc($program['batch_size_max'] ?? '5') ?> candidates</dd>
                        </dl>
                    </div>
                    <div class="col-md-4">
                        <h4>Application Information</h4>
                        <p><strong>Reference Number:</strong> INT-2026-0001</p>
                        <p><strong>Application Deadline:</strong> <?= $program['application_deadline'] ? date('M d, Y', strtotime($program['application_deadline'])) : 'Not specified' ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($program['status'] ?? 'active') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Internship Application Form</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= base_url('recruitment/apply/submit') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fullName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mobile" class="form-label">Mobile Number</label>
                                        <input type="text" class="form-control" id="mobile" name="mobile" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cityState" class="form-label">City and State</label>
                                        <input type="text" class="form-control" id="cityState" name="city_state" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="educationQualification" class="form-label">Education Qualification</label>
                                        <input type="text" class="form-control" id="educationQualification" name="education_qualification" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="collegeUniversity" class="form-label">College/University</label>
                                        <input type="text" class="form-control" id="collegeUniversity" name="college_university" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="graduationYear" class="form-label">Graduation Year</label>
                                        <input type="number" class="form-control" id="graduationYear" name="graduation_year" min="2020" max="2030" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="preferredRole" class="form-label">Preferred Role</label>
                                        <input type="text" class="form-control" id="preferredRole" name="preferred_role" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="technicalSkills" class="form-label">Technical Skills</label>
                                        <input type="text" class="form-control" id="technicalSkills" name="technical_skills">
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="githubProfile" class="form-label">GitHub Profile (optional)</label>
                                        <input type="text" class="form-control" id="githubProfile" name="github_profile">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="portfolioURL" class="form-label">Portfolio URL (optional)</label>
                                        <input type="text" class="form-control" id="portfolioURL" name="portfolio_url">
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="resume" class="form-label">Resume / CV</label>
                                        <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                        <small class="text-muted">Max size: 5MB. Supported: PDF, DOC, DOCX</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="profilePhotograph" class="form-label">Profile Photograph (optional)</label>
                                        <input type="file" class="form-control" id="profilePhotograph" name="profile_photograph" accept="image/*">
                                        <small class="text-muted">Max size: 2MB. Supported: JPEG, PNG</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="laptopAvailable" class="form-label">Laptop Available</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="laptop_available" id="laptopYes" value="1" checked>
                                            <label class="form-check-label" for="laptopYes">Yes</label>
                                        </div>
                                            <input class="form-check-input" type="radio" name="laptop_available" id="laptopNo" value="0">
                                            <label class="form-check-label" for="laptopNo">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="internetAvailable" class="form-label">Internet Available</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="internet_available" id="internetYes" value="1" checked>
                                            <label class="form-check-label" for="internetYes">Yes</label>
                                        </div>
                                            <input class="form-check-input" type="radio" name="internet_available" id="internetNo" value="0">
                                            <label class="form-check-label" for="internetNo">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="weeklyAvailability" class="form-label">Weekly Availability</label>
                                        <input type="text" class="form-control" id="weeklyAvailability" name="weekly_availability" placeholder="e.g., 15-20 hours/week">
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="motivationStatement" class="form-label">Motivation Statement</label>
                                        <textarea class="form-control" id="motivationStatement" name="motivation_statement" rows="3" placeholder="Why do you want to intern at NGWebD? What do you hope to learn?"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="consent_privacy" id="consentPrivacy" required>
                                        <label class="form-check-label" for="consentPrivacy">
                                            I consent to NGWebD storing my personal data for the purpose of this internship application
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row gutters-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-paper-plane"></i> Submit Application
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            let valid = true;
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields marked with *');
            }
        });
    });
    </script>
</body>
</html>