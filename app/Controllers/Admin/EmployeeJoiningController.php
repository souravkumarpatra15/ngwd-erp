<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\EmployeeDepartmentModel;
use App\Models\EmployeeDesignationModel;
use App\Models\OfferLettersModel;
use App\Models\OnboardingChecklistsModel;
use App\Models\ApplicationStatusHistoryModel;
use App\Models\ActivityModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\CLIRequest;
use Config\Services;
use Dompdf\Dompdf;
use Dompdf\Options;

class EmployeeJoiningController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $employeeModel;
    protected $userModel;
    protected $auth;
    protected $offerLettersModel;
    protected $onboardingChecklistsModel;
    protected $applicationStatusHistoryModel;
    protected $activityModel;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->employeeModel = new EmployeeModel();
        $this->userModel = new UserModel();
        $this->auth = new PmsAuthorizationService();
        $this->offerLettersModel = new OfferLettersModel();
        $this->onboardingChecklistsModel = new OnboardingChecklistsModel();
        $this->applicationStatusHistoryModel = new ApplicationStatusHistoryModel();
        $this->activityModel = new ActivityModel();
    }

    /**
     * Show joining confirmation form for a specific application/offer
     */
    public function index($offerId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access joining confirmation.');
        }

        $offer = null;
        $application = null;
        $program = null;

        if ($offerId) {
            $offer = $this->offerLettersModel->find($offerId);
            if (!$offer) {
                return redirect()->to('/admin/recruitment')->with('error', 'Offer letter not found.');
            }
            $application = \Config\Database::connect()
                ->table('internship_applications')
                ->where('id', $offer['application_id'])
                ->get()->getRowArray();
            if (!$application) {
                return redirect()->to('/admin/recruitment')->with('error', 'Application not found.');
            }
            $program = \Config\Database::connect()
                ->table('internship_programs')
                ->where('id', $application['program_id'])
                ->get()->getRowArray();
        }

        return view('admin/joining/index', [
            'title' => 'Joining Confirmation',
            'offer' => $offer,
            'application' => $application,
            'program' => $program,
        ]);
    }

    /**
     * Process the joining confirmation
     * 
     * This is the core workflow:
     * 1. Validate the offer and application state
     * 2. Create employee record linked to user
     * 3. Generate unique IDs and usernames
     * 4. Assign role and permissions
     * 5. Create account activation link
     * 6. Generate joining letter PDF
     * 7. Generate ID card PDF
     * 8. Update status history
     */
    public function processJoin($offerId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return $this->jsonError('You do not have permission to process joining.');
        }

        $offer = $this->offerLettersModel->find($offerId);
        if (!$offer) {
            return $this->jsonError('Offer letter not found.');
        }

        $application = \Config\Database::connect()
            ->table('internship_applications')
            ->where('id', $offer['application_id'])
            ->get()->getRowArray();

        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Check if already processed
            if ($employee = $this->employeeModel->where('user_id', null)->where('email', $application['applicant_email'])->first()) {
                return $this->jsonError('Employee record already exists for this email.');
            }

            // 1. Check if user already exists and is active
            $existingUser = $this->userModel->where('email', $application['applicant_email'])->first();

            if ($existingUser) {
                // Use existing user, activate it
                $userId = $existingUser['id'];
                $existingUser['is_active'] = 1;
                $this->userModel->update($userId, ['is_active' => 1]);
            } else {
                // 2. Create new user account
                // Generate unique username from name
                $nameParts = explode(' ', $application['applicant_name']);
                $baseUsername = strtolower($nameParts[0] . '.' . $nameParts[count($nameParts)-1] ?? $nameParts[0]);
                
                // Make username unique
                $username = $baseUsername;
                $count = $this->userModel->where('username', $username)->countAllResults();
                if ($count > 0) {
                    $username = $baseUsername . rand(100, 9999);
                }

                // Generate secure random password (never email plaintext)
                $temporaryPassword = random_bytes(16); // 16 bytes = 128 bits of entropy
                $passwordHash = password_hash($temporaryPassword, PASSWORD_BCRYPT);

                // Create user with intern/employee role
                $userId = $this->userModel->insert([
                    'name' => $application['applicant_name'],
                    'email' => $application['applicant_email'],
                    'password' => $passwordHash,
                    'role' => $application['preferred_role'] === 'intern' ? 'intern' : 'permanent_employee',
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // 3. Generate unique employee ID
            $employeeId = 'EMP-' . date('Y') . '-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT);

            // 4. Create or update employee record
            // Check if employee record already exists
            $existingEmployee = $this->employeeModel->where('user_id', $userId)->first();

            if ($existingEmployee) {
                // Update existing employee record
                $employeeId = $existingEmployee['employee_id'];
                $this->employeeModel->update($existingEmployee['id'], [
                    'employee_id' => $employeeId,
                    'department_id' => $offer['department_id'] ?? null,
                    'designation_id' => $offer['designation_id'] ?? null,
                    'employment_type' => $application['preferred_role'] === 'intern' ? 'internship' : 'permanent',
                    'employment_status' => 'active',
                    'joining_date' => date('Y-m-d'),
                    'last_working_date' => date('Y-m-d'),
                    'profile_photo' => $offer['profile_photograph'] ?? '',
                    'emergency_contact_name' => $application['emergency_contact_name'] ?? '',
                    'emergency_contact_phone' => $application['emergency_contact_phone'] ?? '',
                    'address_line1' => $application['address_line1'] ?? '',
                    'city' => $application['city'] ?? '',
                    'state' => $application['state'] ?? '',
                    'pincode' => $application['pincode'] ?? '',
                    'notes' => 'Joined via internship program: ' . ($program['title'] ?? 'NGWebD Internship Programme'),
                    'created_by' => session()->get('user_id'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                // Create new employee record
                $employeeId = $this->employeeModel->insert([
                    'user_id' => $userId,
                    'employee_id' => $employeeId,
                    'department_id' => $offer['department_id'] ?? null,
                    'designation_id' => $offer['designation_id'] ?? null,
                    'employment_type' => $application['preferred_role'] === 'intern' ? 'internship' : 'permanent',
                    'employment_status' => 'active',
                    'joining_date' => date('Y-m-d'),
                    'last_working_date' => date('Y-m-d'),
                    'profile_photo' => $offer['profile_photograph'] ?? '',
                    'emergency_contact_name' => $application['emergency_contact_name'] ?? '',
                    'emergency_contact_phone' => $application['emergency_contact_phone'] ?? '',
                    'address_line1' => $application['address_line1'] ?? '',
                    'city' => $application['city'] ?? '',
                    'state' => $application['state'] ?? '',
                    'pincode' => $application['pincode'] ?? '',
                    'notes' => 'Joined via internship program: ' . ($program['title'] ?? 'NGWebD Internship Programme'),
                    'created_by' => session()->get('user_id'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // 5. Assign role and permissions via PmsAuthorizationService
            // The role is already set during user creation above
            // Additional permission assignments can be done here if needed

            // 6. Create account activation link
            $activationToken = random_bytes(32);
            $activationUrl = base_url('auth/activate-account/' . base64_encode($activationToken . '_' . $userId));

            // Store activation token (could add to users table or a separate table)
            // For now, we'll include it in the joining letter and ID card

            // 7. Update offer status to 'joined'
            $this->offerLettersModel->update($offerId, [
                'status' => 'joined',
                'joined_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // 8. Update application status to 'joined'
            \Config\Database::connect()
                ->table('internship_applications')
                ->where('id', $application['id'])
                ->update(['status' => 'joined', 'updated_at' => date('Y-m-d H:i:s')]);

            // 9. Create status history entry
            $this->applicationStatusHistoryModel->insert([
                'application_id' => $application['id'],
                'previous_status' => $application['status'],
                'new_status' => 'joined',
                'changed_by' => session()->get('user_id'),
                'changed_by_name' => session()->get('user_name'),
                'change_reason' => 'Employee joined confirmed by administrator',
                'changed_at' => date('Y-m-d H:i:s'),
            ]);

            // 10. Log activity
            $this->activityModel->insert([
                'user_id' => session()->get('user_id'),
                'module' => 'hrms',
                'module_id' => $employeeId,
                'action' => 'join_confirmed',
                'description' => 'Employee ' . $application['applicant_name'] . ' joining confirmed',
                'ip_address' => $this->request->getIPAddress(),
            ]);

            // Commit transaction
            $db->transComplete();

            // Return success with activation details
            return $this->jsonSuccess('Joining process completed successfully', [
                'user_id' => $userId,
                'employee_id' => $employeeId,
                'activation_url' => $activationUrl,
                'activation_token' => base64_encode($activationToken . '_' . $userId),
                'temporary_password' => 'RESET_REQUIRED', // Never include actual password
                'joining_confirmed' => true,
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->jsonError('Joining process failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate joining confirmation letter PDF
     */
    public function generateJoiningLetterPdf($offerId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return $this->response->setStatusCode(403);
        }

        $offer = $this->offerLettersModel->find($offerId);
        if (!$offer) {
            return $this->response->setStatusCode(404);
        }

        $application = \Config\Database::connect()
            ->table('internship_applications')
            ->where('id', $offer['application_id'])
            ->get()->getRowArray();

        $program = \Config\Database::connect()
            ->table('internship_programs')
            ->where('id', $application['program_id'])
            ->get()->getRowArray();

        // Render HTML template
        $html = $this->renderJoiningLetterTemplate($offer, $application, $program);

        // Generate PDF
        $options = new Options();
        $options->set('isHtml5Enabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // Store PDF (ensure the directory exists — portable across environments)
        $pdfFilename = 'joining_letter_'. $offer['offer_reference'] . '.pdf';
        $pdfDir = ROOTPATH . 'internship/joining-letters';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0775, true);
        }
        file_put_contents($pdfDir . DIRECTORY_SEPARATOR . $pdfFilename, $pdfOutput);

        // Return PDF as download
        $this->response->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $pdfFilename . '"');
        $this->response->setBody($pdfOutput);
        $this->response->send();
        exit;
    }

    /**
     * Generate portrait ID card PDF
     */
    public function generateIdCardPdf($userId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return $this->response->setStatusCode(403);
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setStatusCode(404);
        }

        $employee = $this->employeeModel->where('user_id', $userId)->first();
        if (!$employee) {
            return $this->response->setStatusCode(404);
        }

        // Get program info from application
        $application = \Config\Database::connect()
            ->table('internship_applications')
            ->where('applicant_email', $user['email'])
            ->get()->getRowArray();

        $program = null;
        if ($application) {
            $program = \Config\Database::connect()
                ->table('internship_programs')
                ->where('id', $application['program_id'])
                ->get()->getRowArray();
        }

        // Render HTML template
        $html = $this->renderIdCardTemplate($user, $employee, $program);

        // Generate PDF
        $options = new Options();
        $options->set('isHtml5Enabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // Store PDF (ensure the directory exists — portable across environments)
        $internNumber = $employee['employee_id'] ?? 'INT-' . date('Y') . '-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT);
        $pdfFilename = 'id_card_'. $internNumber . '.pdf';
        $pdfDir = ROOTPATH . 'internship/id-cards';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0775, true);
        }
        file_put_contents($pdfDir . DIRECTORY_SEPARATOR . $pdfFilename, $pdfOutput);

        // Return PDF as download
        $this->response->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $pdfFilename . '"');
        $this->response->setBody($pdfOutput);
        $this->response->send();
        exit;
    }

    /**
     * Render joining confirmation letter template
     */
    protected function renderJoiningLetterTemplate($offer, $application, $program)
    {
        $compensationDisclosure = $program['compensation_category'] ?? 'Unpaid educational internship, subject to legal review and applicable law.';

        ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joining Confirmation - NGWebD Internship</title>
    <style>
        body { font-family: "Noto Sans", Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #2D3748; padding-bottom: 20px; }
        .header h1 { font-size: 28px; color: #2D3748; margin-bottom: 10px; }
        .logo { width: 150px; margin: 0 auto 20px; }
        .section { margin: 30px 0; }
        .section h2 { color: #4A5568; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #CCC; padding: 8px 12px; font-size: 14px; }
        .table th { background-color: #F7FAFD; width: 40%; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #718096; border-top: 1px solid #E2E8F0; padding-top: 20px; }
        .qr-code { text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo" alt="NGWebD Logo"></div>
            <h1>Joining Confirmation Letter</h1>
            <p>NGWebD Digital Solutions</p>
        </div>

        <div class="qr-code">
            <p>QR Code: <?= $offer['offer_reference'] ?? 'N/A' ?></p>
        </div>

        <div class="section">
            <h2>Employee Information</h2>
            <table>
                <tr><th>Full Name</th><td><?= $application['applicant_name'] ?></td></tr>
                <tr><th>Employee ID</th><td><?= $offer['offer_reference'] ?? 'N/A' ?></td></tr>
                <tr><th>Position</th><td><?= $application['preferred_role'] ?></td></tr>
                <tr><th>Department</th><td><?= $program['title'] ?? 'N/A' ?></td></tr>
                <tr><th>Designation</th><td><?= $offer['position'] ?? 'N/A' ?></td></tr>
                <tr><th>Joining Date</th><td><?= date('F j, Y') ?></td></tr>
                <tr><th>Programme</th><td><?= $program['title'] ?? 'N/A' ?></td></tr>
                <tr><th>Duration</th><td><?= $program['duration_months'] ?> Months</td></tr>
                <tr><th>Mode</th><td><?= ucfirst($program['mode'] ?? 'remote') ?></td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Internship Programme Details</h2>
            <table>
                <tr><th>Programme Name</th><td><?= $program['title'] ?></td></tr>
                <tr><th>Duration</th><td><?= $program['duration_months'] ?> Months</td></tr>
                <tr><th>Start Date</th><td><?= $program['start_date'] ? date('F j, Y', strtotime($program['start_date'])) : 'N/A' ?></td></tr>
                <tr><th>End Date</th><td><?= $program['end_date'] ? date('F j, Y', strtotime($program['end_date'])) : 'N/A' ?></td></tr>
                <tr><th>Work Mode</th><td><?= ucfirst($program['mode'] ?? 'remote') ?></td></tr>
                <tr><th>Weekly Commitment</th><td><?= $program['weekly_hours'] ?> hours</td></tr>
                <tr><th>Compensation</th><td><?= $compensationDisclosure ?></td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Terms and Conditions</h2>
            <p>By joining NGWebD Digital Solutions, you agree to the terms and conditions of the internship programme.</p>
            <p>Please report to your mentor on your joining date.</p>
        </div>

        <div class="footer">
            <p>This joining confirmation letter is issued by NGWebD Digital Solutions<br>
            Date: <?= date('F j, Y') ?><br>
            For any queries, please contact HR department.</p>
        </div>
</body>
</html>
<?php
        return ob_get_clean();
    }

    /**
     * Render portrait ID card template
     */
    protected function renderIdCardTemplate($user, $employee, $program)
    {
        $internNumber = $employee['employee_id'] ?? 'INT-' . date('Y') . '-' . str_pad((string) $user['id'], 4, '0', STR_PAD_LEFT);

        ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Card - NGWebD</title>
    <style>
        body { font-family: "Noto Sans", Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .card { max-width: 600px; margin: 0 auto; border: 2px solid #2D3748; border-radius: 8px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .portrait { text-align: center; margin: 20px 0; }
        .portrait img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #2D3748; }
        .id-number { font-size: 24px; font-weight: bold; color: #2D3748; text-align: center; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th, .table td { border: 1px solid #CCC; padding: 8px 12px; font-size: 14px; }
        .table th { background-color: #F7FAFD; width: 40%; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #718096; border-top: 1px solid #E2E8F0; padding-top: 15px; }
        .qr-section { margin: 20px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>NGWebD Employee ID Card</h1>
        </div>
        
        <div class="portrait">
            <!-- Portrait would be inserted here - using placeholder for now -->
            <div style="width: 150px; height: 150px; background-color: #E2E8F0; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #718096; font-size: 12px;">
                <?= substr($user['name'], 0, 1) . '.' ?>
            </div>
            <p><small>Portrait to be updated</small></p>
        </div>

        <div class="id-number">
            <?= $internNumber ?>
        </div>

        <table>
            <tr><th>Full Name</th><td><?= $user['name'] ?></td></tr>
            <tr><th>Employee ID</th><td><?= $internNumber ?></td></tr>
            <tr><th>Position</th><td><?= $employee['designation_id'] ? 'Designation' : 'Intern' ?></td></tr>
            <tr><th>Department</th><td><?= $employee['department_id'] ? 'Department' : 'N/A' ?></td></tr>
            <tr><th>Joining Date</th><td><?= $employee['joining_date'] ? date('F j, Y', strtotime($employee['joining_date'])) : 'N/A' ?></td></tr>
            <tr><th>Email</th><td><?= $user['email'] ?></td></tr>
        </table>

        <div class="qr-section">
            <p>QR Code: <?= $internNumber ?></p>
            <p>Scanning this code will display employee profile</p>
        </div>

        <div class="footer">
            <p>NGWebD Digital Solutions<br>
            This is a professional employee identification card.</p>
        </div>
    </div>
</body>
</html>
<?php
        return ob_get_clean();
    }
}