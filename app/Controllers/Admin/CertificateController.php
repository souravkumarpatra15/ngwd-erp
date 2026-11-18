<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CertificateModel;
use App\Models\EmployeeModel;
use App\Models\InternshipApplicationsModel;
use App\Models\InternshipProgrammesModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CertificateController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $certificateModel;
    protected $employeeModel;
    protected $applicationsModel;
    protected $programmesModel;
    protected $auth;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->certificateModel  = new CertificateModel();
        $this->employeeModel     = new EmployeeModel();
        $this->applicationsModel = new InternshipApplicationsModel();
        $this->programmesModel   = new InternshipProgrammesModel();
        $this->auth              = new PmsAuthorizationService();
    }

    /**
     * Certificate issuance page — lists internship applications
     * that have accepted their offer / joined, ready to be certified.
     */
    public function generate($employeeId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageCertificates($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to generate certificates.');
        }

        $applications = $this->applicationsModel
            ->whereIn('status', ['offer_accepted', 'onboarding', 'joined'])
            ->orderBy('applied_at', 'DESC')
            ->findAll();

        // Attach existing certificate (if any) to each application
        foreach ($applications as &$app) {
            $app['certificate'] = $this->certificateModel->findByApplicationId((int) $app['id']);
        }

        return view('admin/certificate/issue_form', [
            'title'       => 'Issue Internship Certificate',
            'applications' => $applications,
            'canManage'   => $auth->canManageCertificates($role),
        ]);
    }

    /**
     * Process certificate generation (AJAX)
     */
    public function processGenerate()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageCertificates($role)) {
            return $this->jsonError('You do not have permission to generate certificates.');
        }

        $applicationId = (int) ($this->request->getPost('internship_application_id') ?? 0);
        $application = $this->applicationsModel->find($applicationId);

        if (!$application) {
            return $this->jsonError('Internship application not found.');
        }

        if (!in_array($application['status'], ['offer_accepted', 'onboarding', 'joined'], true)) {
            return $this->jsonError('Certificates can only be issued after the candidate accepts their offer or joins.');
        }

        // Prevent duplicate certificates for the same application
        $existing = $this->certificateModel->findByApplicationId($applicationId);
        if ($existing && (int) $existing['revoked'] === 0) {
            return $this->jsonError('A certificate has already been issued for this application (' . $existing['certificate_number'] . '). Use the revocation flow if needed.');
        }

        $program = $this->programmesModel->find($application['program_id']);

        $certNumber = $this->certificateModel->generateCertificateNumber();
        $hrApproval = $this->request->getPost('hr_approval') ? 1 : 0;

        $certId = $this->certificateModel->insert([
            'internship_application_id' => $applicationId,
            'certificate_number'        => $certNumber,
            'student_name'              => $application['applicant_name'],
            'internship_program'        => $application['preferred_role'] ?? ($program['title'] ?? 'Internship Programme'),
            'duration_months'           => $program['duration_months'] ?? null,
            'final_evaluation'          => $this->request->getPost('final_evaluation') ?: null,
            'mentor_approval'           => $this->request->getPost('mentor_approval') ? 1 : 0,
            'hr_approval'               => $hrApproval,
            'issued_at'                 => $hrApproval ? date('Y-m-d H:i:s') : null,
            'revoked'                   => 0,
            'verification_hash'         => $this->certificateModel->generateVerificationHash($certNumber),
        ]);

        if (!$certId) {
            return $this->jsonError('Failed to save the certificate.');
        }

        return $this->jsonSuccess('Certificate generated', [
            'certificate_id'      => (int) $certId,
            'certificate_number'  => $certNumber,
            'verification_url'    => base_url('certificate/verify/' . $certNumber),
            'intern_name'         => $application['applicant_name'],
            'status'              => $hrApproval ? 'active' : 'pending',
        ]);
    }

    /**
     * Printable certificate (A4 landscape) with QR verification code.
     * Optional ?format=pdf streams a Dompdf PDF instead of the print page.
     */
    public function printCertificate($certificateNumber = null)
    {
        $role = session()->get('user_role');
        if (!$this->auth->canManageCertificates($role) && !$this->auth->canViewCertificates($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to view certificates.');
        }

        if (!$certificateNumber) {
            return redirect()->to('/admin/certificates')->with('error', 'Certificate number is required.');
        }

        $certificate = $this->certificateModel->findByCertificateNumber($certificateNumber);
        if (!$certificate) {
            return redirect()->to('/admin/certificates')->with('error', 'Certificate not found.');
        }

        $application = $this->applicationsModel->find((int) $certificate['internship_application_id']);
        $program     = $application ? $this->programmesModel->find((int) $application['program_id']) : null;

        // Department from the joined employee record (if the intern converted)
        $department = 'N/A';
        $employee   = $this->employeeModel
            ->where('user_id', (int) ($application['user_id'] ?? 0))
            ->first();
        if ($employee && !empty($employee['department_id'])) {
            $dept = \Config\Database::connect()
                ->table('employee_departments')
                ->where('id', (int) $employee['department_id'])
                ->get()->getRowArray();
            if ($dept) {
                $department = $dept['department_name'] ?? 'N/A';
            }
        }

        $startDate = $program['start_date'] ?? null;
        $endDate   = $program['end_date'] ?? null;

        $data = [
            'internName'        => $certificate['student_name'] ?? 'N/A',
            'internDesignation' => $certificate['internship_program'] ?: ($program['title'] ?? 'Intern'),
            'department'        => $department,
            'startDate'         => $startDate ? date('d M Y', strtotime($startDate)) : 'N/A',
            'endDate'           => $endDate ? date('d M Y', strtotime($endDate)) : 'N/A',
            'completionDate'    => !empty($certificate['issued_at']) ? date('d M Y', strtotime($certificate['issued_at'])) : 'Pending HR approval',
            'certNumber'        => $certificate['certificate_number'],
            'finalScore'        => $certificate['final_evaluation'] ? $certificate['final_evaluation'] . ' / 10' : 'N/A',
            'verifyUrl'         => base_url('certificate/verify/' . $certificate['certificate_number']),
            'qrDataUri'         => $this->buildQrDataUri(base_url('certificate/verify/' . $certificate['certificate_number'])),
        ];

        $html = view('admin/certificate/generate', $data);

        // PDF stream on demand
        if (strtolower((string) $this->request->getGet('format')) === 'pdf') {
            $options = new \Dompdf\Options();
            $options->set('isHtml5Enabled', true);
            $options->set('defaultFont', 'Georgia');
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->loadHtml($html);
            $dompdf->render();
            return $this->response
                ->setContentType('application/pdf')
                ->setBody($dompdf->output());
        }

        return $this->response->setBody($html);
    }

    /**
     * Build a base64 PNG data URI for a QR code encoding $payload.
     * Uses the qrserver.com API (no composer dependency); returns null on
     * any failure so the view falls back to a plain-text verify URL.
     */
    protected function buildQrDataUri(string $payload): ?string
    {
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=4&data=' . rawurlencode($payload);

        try {
            $client = \Config\Services::curlrequest(['timeout' => 8]);
            $res    = $client->get($url);
            $png    = $res->getBody();
            if ($res->getStatusCode() === 200 && $png !== '' && str_starts_with($png, "\x89PNG")) {
                return 'data:image/png;base64,' . base64_encode($png);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'QR generation failed for {number}: {msg}', [
                'number' => $payload, 'msg' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Revoke/cancel a certificate (AJAX)
     */
    public function revoke($certificateNumber = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageCertificates($role)) {
            return $this->jsonError('You do not have permission to revoke certificates.');
        }

        if (!$certificateNumber) {
            return $this->jsonError('Certificate number is required.');
        }

        $certificate = $this->certificateModel->findByCertificateNumber($certificateNumber);
        if (!$certificate) {
            return $this->jsonError('Certificate not found.');
        }

        if ((int) $certificate['revoked'] === 1) {
            return $this->jsonError('Certificate is already revoked.');
        }

        $this->certificateModel->update($certificate['id'], [
            'revoked'           => 1,
            'revoked_at'        => date('Y-m-d H:i:s'),
            'revocation_reason' => $this->request->getPost('reason') ?? 'Revoked by administrator',
        ]);

        // Log the revocation
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id'     => session()->get('user_id'),
            'module'      => 'certificates',
            'module_id'   => $certificate['id'],
            'action'      => 'revoked',
            'description' => 'Certificate ' . $certificateNumber . ' revoked by administrator',
            'ip_address'  => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Certificate revoked', [
            'certificate_number' => $certificateNumber,
            'status'             => 'revoked',
        ]);
    }

    /**
     * Public verification page — exposes only safe fields
     */
    public function verify($certificateNumber = null)
    {
        if (!$certificateNumber) {
            return redirect()->to('/');
        }

        $certificate = $this->certificateModel->findByCertificateNumber($certificateNumber);

        if (!$certificate) {
            return view('admin/certificate/verify', [
                'title'             => 'Certificate Verification',
                'certificateNumber' => $certificateNumber,
                'notFound'          => true,
            ]);
        }

        $issued = (int) ($certificate['hr_approval'] ?? 0) === 1;

        $data = [
            'title'             => 'Certificate Verification',
            'certificateNumber' => $certificateNumber,
            'notFound'          => false,
            'certificate'       => [
                'valid' => !$issued && (int) $certificate['revoked'] === 0 ? false : $this->certificateModel->isVerifiable($certificate),
            ],
            'valid'             => $this->certificateModel->isVerifiable($certificate),
            'internName'        => $certificate['student_name'],
            'internRole'        => $certificate['internship_program'],
            'durationMonths'    => $certificate['duration_months'] ? (int) $certificate['duration_months'] . ' months' : 'N/A',
            'finalScore'        => $certificate['final_evaluation'] ? $certificate['final_evaluation'] . ' / 10' : 'N/A',
            'issuedDate'        => $certificate['issued_at'] ? date('F j, Y', strtotime($certificate['issued_at'])) : 'N/A',
            'organization'      => 'NGWebD Consulting Pvt. Ltd.',
        ];

        return view('admin/certificate/verify', $data);
    }

    /**
     * Public AJAX verification API — exposes only safe fields
     */
    public function verifyApi($certificateNumber = null)
    {
        if (!$certificateNumber) {
            return $this->jsonError('Certificate number required.');
        }

        $certificate = $this->certificateModel->findByCertificateNumber($certificateNumber);

        if (!$certificate) {
            return $this->jsonError('Certificate not found.');
        }

        $isActive = $this->certificateModel->isVerifiable($certificate);

        return $this->jsonSuccess('Certificate verification', [
            'valid'             => $isActive,
            'certificateNumber' => $certificateNumber,
            'internName'        => $certificate['student_name'],
            'internRole'        => $certificate['internship_program'],
            'durationMonths'    => $certificate['duration_months'] ? (int) $certificate['duration_months'] . ' months' : 'N/A',
            'issuedDate'        => $certificate['issued_at'] ? date('F j, Y', strtotime($certificate['issued_at'])) : 'N/A',
            'organization'      => 'NGWebD Consulting Pvt. Ltd.',
        ]);
    }
}
