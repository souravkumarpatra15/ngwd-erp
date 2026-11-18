<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InternshipProgrammesModel;
use App\Models\InternshipPositionsModel;
use App\Models\InternshipApplicationsModel;
use App\Models\ApplicationStatusHistoryModel;
use App\Models\CandidateAssessmentsModel;
use App\Models\InterviewsModel;
use App\Models\InterviewFeedbackModel;
use App\Models\OfferLettersModel;
use App\Models\OfferAcceptancesModel;
use App\Models\OnboardingChecklistsModel;
use App\Models\OnboardingDocumentsModel;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Services\OfferLetterService;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RecruitmentController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $internshipProgrammesModel;
    protected $internshipPositionsModel;
    protected $internshipApplicationsModel;
    protected $applicationStatusHistoryModel;
    protected $candidateAssessmentsModel;
    protected $interviewsModel;
    protected $interviewFeedbackModel;
    protected $offerLettersModel;
    protected $offerAcceptancesModel;
    protected $onboardingChecklistsModel;
    protected $onboardingDocumentsModel;
    protected $employeeModel;
    protected $userModel;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->internshipProgrammesModel = new InternshipProgrammesModel();
        $this->internshipPositionsModel = new InternshipPositionsModel();
        $this->internshipApplicationsModel = new InternshipApplicationsModel();
        $this->applicationStatusHistoryModel = new ApplicationStatusHistoryModel();
        $this->candidateAssessmentsModel = new CandidateAssessmentsModel();
        $this->interviewsModel = new InterviewsModel();
        $this->interviewFeedbackModel = new InterviewFeedbackModel();
        $this->offerLettersModel = new OfferLettersModel();
        $this->offerAcceptancesModel = new OfferAcceptancesModel();
        $this->onboardingChecklistsModel = new OnboardingChecklistsModel();
        $this->onboardingDocumentsModel = new OnboardingDocumentsModel();
        $this->employeeModel = new EmployeeModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access recruitment.');
        }

        $search = $this->request->getGet('q');
        $status = $this->request->getGet('status');
        $programId = $this->request->getGet('program_id');

        $query = $this->internshipApplicationsModel->withProgram();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', $search)->orWhere('applicant_email', $search)->orWhere('applicant_mobile', $search);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $applications = $query->orderBy('applied_at', 'DESC')->paginate(20);
        $pager = $this->internshipApplicationsModel->pager;

        // Get programs for filter
        $programs = $this->internshipProgrammesModel->where('status', 'active')->findAll();

        return view('admin/recruitment/index', [
            'title' => 'Recruitment Management',
            'applications' => $applications,
            'pager' => $pager,
            'programs' => $programs,
            'search' => $search,
            'selected_status' => $status,
            'selected_program' => $programId,
            'canViewRecruitment' => $auth->canViewRecruitment($role),
        ]);
    }

    public function createProgram()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to create internship programmes.');
        }

        return view('admin/recruitment/create_program', [
            'title' => 'Create Internship Programme',
        ]);
    }

    public function storeProgram()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return $this->jsonError('You do not have permission to create internship programmes.');
        }

        $rules = [
            'title' => 'required|string',
            'description' => 'permit_empty|string',
            'duration_months' => 'required|integer|greater_than[0]',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
            'mode' => 'required|in_list[remote,hybrid,office]',
            'weekly_hours' => 'required|integer|greater_than[0]',
            'compensation_category' => 'permit_empty|string',
            'eligibility_criteria' => 'permit_empty|string',
            'batch_size_min' => 'permit_empty|integer|greater_than[0]',
            'batch_size_max' => 'permit_empty|integer|greater_than[1]',
            'application_deadline' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[draft,active,closed]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors());
        }

        $startDate = (string) $this->request->getPost('start_date');
        $endDate = (string) $this->request->getPost('end_date');
        if ($endDate < $startDate) {
            return $this->jsonError('Validation failed', ['end_date' => 'End date must be on or after the start date.']);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'duration_months' => $this->request->getPost('duration_months'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'mode' => $this->request->getPost('mode'),
            'weekly_hours' => $this->request->getPost('weekly_hours'),
            'compensation_category' => $this->request->getPost('compensation_category'),
            'eligibility_criteria' => $this->request->getPost('eligibility_criteria'),
            'batch_size_min' => $this->request->getPost('batch_size_min'),
            'batch_size_max' => $this->request->getPost('batch_size_max'),
            'application_deadline' => $this->request->getPost('application_deadline'),
            'status' => $this->request->getPost('status'),
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Set initial positions based on the standard NGWebD Internship Programme config
        $this->internshipProgrammesModel->insert($data);
        $programId = $this->internshipProgrammesModel->getInsertID();

        // Create initial positions for the standard NGWebD Internship Programme
        $positions = [
            ['title' => 'PHP / Backend Developer Intern', 'description' => 'Backend development using PHP', 'required_skills' => 'PHP, MySQL, CodeIgniter, REST APIs', 'education_requirement' => 'Bachelor\'s in Computer Science or related', 'experience_years' => 0, 'stipend_category' => 'Unpaid educational internship', 'working_hours_per_week' => 20, 'location' => 'Remote'],
            ['title' => 'Frontend Developer Intern', 'description' => 'Frontend development using modern JavaScript frameworks', 'required_skills' => 'JavaScript, React/Vue, HTML5, CSS3', 'education_requirement' => 'Bachelor\'s in Computer Science or related', 'experience_years' => 0, 'stipend_category' => 'Unpaid educational internship', 'working_hours_per_week' => 20, 'location' => 'Remote'],
            ['title' => 'UI/UX Designer Intern', 'description' => 'UI/UX design and prototyping', 'required_skills' => 'Figma, Adobe XD, UI design principles, prototyping', 'education_requirement' => 'Bachelor\'s in Design or related', 'experience_years' => 0, 'stipend_category' => 'Unpaid educational internship', 'working_hours_per_week' => 20, 'location' => 'Remote'],
            ['title' => 'QA / Software Testing Intern', 'description' => 'Software testing and quality assurance', 'required_skills' => 'Test case creation, bug reporting, Selenium/WebDriver', 'education_requirement' => 'Bachelor\'s in Computer Science or related', 'experience_years' => 0, 'stipend_category' => 'Unpaid educational internship', 'working_hours_per_week' => 20, 'location' => 'Remote'],
        ];

        foreach ($positions as $pos) {
            $this->internshipPositionsModel->insert([
                'program_id' => $programId,
                'title' => $pos['title'],
                'description' => $pos['description'],
                'required_skills' => $pos['required_skills'],
                'education_requirement' => $pos['education_requirement'],
                'experience_years' => $pos['experience_years'],
                'stipend_category' => $pos['stipend_category'],
                'working_hours_per_week' => $pos['working_hours_per_week'],
                'location' => $pos['location'],
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $programId,
            'action' => 'create_program',
            'description' => 'Created new internship programme: ' . $data['title'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Internship programme created successfully', ['program_id' => $programId]);
    }

    public function editProgram($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to edit internship programmes.');
        }

        $program = $this->internshipProgrammesModel->find($id);
        if (!$program) {
            return redirect()->to('/admin/recruitment')->with('error', 'Programme not found.');
        }

        $positions = $this->internshipPositionsModel->where('program_id', $id)->findAll();

        return view('admin/recruitment/edit_program', [
            'title' => 'Edit Internship Programme',
            'program' => $program,
            'positions' => $positions,
        ]);
    }

    public function updateProgram($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return $this->jsonError('You do not have permission to update internship programmes.');
        }

        $program = $this->internshipProgrammesModel->find($id);
        if (!$program) {
            return $this->jsonError('Programme not found.');
        }

        $rules = [
            'title' => 'permit_empty|string',
            'description' => 'permit_empty|string',
            'duration_months' => 'permit_empty|integer',
            'start_date' => 'permit_empty|valid_date',
            'end_date' => 'permit_empty|valid_date',
            'mode' => 'permit_empty|in_list[remote,hybrid,office]',
            'weekly_hours' => 'permit_empty|integer',
            'compensation_category' => 'permit_empty|string',
            'eligibility_criteria' => 'permit_empty|string',
            'batch_size_min' => 'permit_empty|integer',
            'batch_size_max' => 'permit_empty|integer',
            'application_deadline' => 'permit_empty|valid_date',
            'status' => 'permit_empty|in_list[draft,active,closed,completed]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title') ?: $program['title'],
            'description' => $this->request->getPost('description') ?: $program['description'],
            'duration_months' => $this->request->getPost('duration_months') ?: $program['duration_months'],
            'start_date' => $this->request->getPost('start_date') ?: $program['start_date'],
            'end_date' => $this->request->getPost('end_date') ?: $program['end_date'],
            'mode' => $this->request->getPost('mode') ?: $program['mode'],
            'weekly_hours' => $this->request->getPost('weekly_hours') ?: $program['weekly_hours'],
            'compensation_category' => $this->request->getPost('compensation_category') ?: $program['compensation_category'],
            'eligibility_criteria' => $this->request->getPost('eligibility_criteria') ?: $program['eligibility_criteria'],
            'batch_size_min' => $this->request->getPost('batch_size_min') ?: $program['batch_size_min'],
            'batch_size_max' => $this->request->getPost('batch_size_max') ?: $program['batch_size_max'],
            'application_deadline' => $this->request->getPost('application_deadline') ?: $program['application_deadline'],
            'status' => $this->request->getPost('status') ?: $program['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->internshipProgrammesModel->update($id, $data);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'update_program',
            'description' => 'Updated internship programme: ' . $data['title'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Internship programme updated successfully');
    }

    public function deleteProgram($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to delete internship programmes.');
        }

        $program = $this->internshipProgrammesModel->find($id);
        if (!$program) {
            return redirect()->to('/admin/recruitment')->with('error', 'Programme not found.');
        }

        // Check if there are applications
        $appCount = $this->internshipApplicationsModel->where('program_id', $id)->countAllResults();
        if ($appCount > 0) {
            return redirect()->to('/admin/recruitment')->with('error', 'Cannot delete programme with existing applications. Close the programme instead.');
        }

        $this->internshipProgrammesModel->delete($id);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'delete_program',
            'description' => 'Deleted internship programme',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Internship programme deleted successfully');
    }

    /** Application portal - public view */
    public function apply($programId = null)
    {
        $program = null;
        $programs = [];

        if ($programId) {
            $program = $this->internshipProgrammesModel->where('id', $programId)->where('status', 'active')->first();
            if (!$program) {
                return redirect()->to('/')->with('error', 'Internship programme not found or not active.');
            }
        } else {
            // Get active programs
            $programs = $this->internshipProgrammesModel->where('status', 'active')->findAll();
            $program = count($programs) > 0 ? $programs[0] : null;
        }

        if (!$program) {
            return view('portal/recruitment/apply', [
                'title' => 'Internship Applications - NGWebD',
                'noProgramsAvailable' => true,
            ]);
        }

        return view('portal/recruitment/apply', [
            'title' => 'Internship Application - ' . $program['title'],
            'program' => $program,
            'noProgramsAvailable' => false,
        ]);
    }

    /** Submit application */
    public function submitApplication()
    {
        // Allow both authenticated and unauthenticated users to apply
        // (unauthenticated will have null user_id)

        $rules = [
            'full_name' => 'required|string|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'mobile' => 'required|string|max_length[20]',
            'city_state' => 'required|string|max_length[150]',
            'education_qualification' => 'required|string|max_length[100]',
            'college_university' => 'required|string|max_length[200]',
            'graduation_year' => 'required|integer|greater_than[2019]|less_than[2031]',
            'preferred_role' => 'required|string|max_length[100]',
            'technical_skills' => 'permit_empty|string',
            'github_profile' => 'permit_empty|string|max_length[255]',
            'portfolio_url' => 'permit_empty|string|max_length[255]',
            'resume' => 'permit_empty|max_size[resume,5120]|ext_in[resume,pdf,doc,docx]',
            'profile_photograph' => 'permit_empty|is_image[profile_photograph]|max_size[profile_photograph,2048]|mime_in[profile_photograph,image/jpeg,image/png]',
            'laptop_available' => 'permit_empty|in_list[0,1]',
            'internet_available' => 'permit_empty|in_list[0,1]',
            'weekly_availability' => 'required|string|max_length[50]',
            'motivation_statement' => 'permit_empty|string',
            'consent_privacy' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors());
        }

        // One active application per email — rejected/withdrawn candidates may reapply
        $existing = $this->internshipApplicationsModel
            ->where('applicant_email', strtolower(trim((string) $this->request->getPost('email'))))
            ->whereNotIn('status', ['rejected', 'withdrawn'])
            ->first();
        if ($existing) {
            return $this->jsonError('An application already exists for this email (Ref: ' . $existing['reference_number'] . ').');
        }

        $data = [
            'program_id' => $this->request->getPost('program_id'),
            'applicant_name' => $this->request->getPost('full_name'),
            'applicant_email' => strtolower(trim($this->request->getPost('email'))),
            'applicant_mobile' => $this->request->getPost('mobile'),
            'applicant_city_state' => $this->request->getPost('city_state'),
            'education_qualification' => $this->request->getPost('education_qualification'),
            'college_university' => $this->request->getPost('college_university'),
            'graduation_year' => $this->request->getPost('graduation_year'),
            'preferred_role' => $this->request->getPost('preferred_role'),
            'technical_skills' => $this->request->getPost('technical_skills'),
            'github_profile' => $this->request->getPost('github_profile'),
            'portfolio_url' => $this->request->getPost('portfolio_url'),
            'resume_file_path' => '',
            'profile_photograph' => '',
            'laptop_available' => $this->request->getPost('laptop_available') ? 1 : 0,
            'internet_available' => $this->request->getPost('internet_available') ? 1 : 1,
            'weekly_availability' => $this->request->getPost('weekly_availability'),
            'motivation_statement' => $this->request->getPost('motivation_statement'),
            'consent_privacy' => $this->request->getPost('consent_privacy') ? 1 : 0,
            'status' => 'applied',
            'applied_at' => date('Y-m-d H:i:s'),
            'reference_number' => 'INT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3))),
        ];

        // Handle resume upload
        $file = $this->request->getFile('resume');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getExtension();
            $name = 'resume_' . date('Ymd His') . '.' . $ext;
            $dir = ROOTPATH . 'internship/resumes';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $file->move($dir, $name);
            $data['resume_file_path'] = 'internship/resumes/' . $name;
        }

        // Handle profile photograph upload
        $file = $this->request->getFile('profile_photograph');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getExtension();
            $name = 'photo_' . date('Ymd His') . '.' . $ext;
            $dir = ROOTPATH . 'internship/photos';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $file->move($dir, $name);
            $data['profile_photograph'] = 'internship/photos/' . $name;
        }

        $this->internshipApplicationsModel->insert($data);

        // Create initial status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $this->internshipApplicationsModel->getInsertID(),
            'previous_status' => null,
            'new_status' => 'applied',
            'changed_by' => session()->get('user_id') ?: 0,
            'changed_by_name' => session()->get('user_name') ?: 'Anonymous',
            'change_reason' => 'Initial application submission',
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id') ?: 0,
            'module' => 'recruitment',
            'module_id' => $this->internshipApplicationsModel->getInsertID(),
            'action' => 'submit_application',
            'description' => 'New internship application submitted',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Application submitted successfully', [
            'reference_number' => $data['reference_number'],
            'application_id' => $this->internshipApplicationsModel->getInsertID(),
        ]);
    }

    /** Shortlist a candidate */
    public function shortlist($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return $this->jsonError('You do not have permission to shortlist candidates.');
        }

        $application = $this->internshipApplicationsModel->find($id);
        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // Update status to shortlisted
        $this->internshipApplicationsModel->update($id, [
            'status' => 'shortlisted',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $id,
            'previous_status' => $application['status'],
            'new_status' => 'shortlisted',
            'changed_by' => session()->get('user_id'),
            'changed_by_name' => session()->get('user_name'),
            'change_reason' => 'Candidate shortlisted by recruiter',
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'shortlist',
            'description' => 'Candidate ' . $application['applicant_name'] . ' shortlisted',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Candidate shortlisted successfully');
    }

    /** Reject a candidate */
    public function reject($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return $this->jsonError('You do not have permission to reject candidates.');
        }

        $application = $this->internshipApplicationsModel->find($id);
        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // Update status to rejected
        $this->internshipApplicationsModel->update($id, [
            'status' => 'rejected',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $id,
            'previous_status' => $application['status'],
            'new_status' => 'rejected',
            'changed_by' => session()->get('user_id'),
            'changed_by_name' => session()->get('user_name'),
            'change_reason' => 'Candidate rejected by recruiter',
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'reject',
            'description' => 'Candidate ' . $application['applicant_name'] . ' rejected',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Candidate rejected successfully');
    }

    /** Schedule interview */
    public function scheduleInterview($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageInterviews($role)) {
            return $this->jsonError('You do not have permission to schedule interviews.');
        }

        $application = $this->internshipApplicationsModel->find($id);
        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // If interview already exists, return it
        $existing = $this->interviewsModel->where('application_id', $id)->where('interview_status', 'scheduled')->first();
        if ($existing) {
            return $this->jsonSuccess('Interview already scheduled', $existing);
        }

        $data = [
            'application_id' => $id,
            'interview_round' => 1,
            'interviewer_id' => null,
            'interviewer_name' => null,
            'interview_date' => null,
            'start_time' => null,
            'end_time' => null,
            'timezone' => 'UTC',
            'interview_type' => 'technical',
            'meeting_link' => null,
            'instructions' => null,
            'internal_notes' => 'Interview for: ' . $application['applicant_name'] . ' (' . $application['preferred_role'] . ')',
            'interview_status' => 'scheduled',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->interviewsModel->insert($data);

        // Create status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $id,
            'previous_status' => $application['status'],
            'new_status' => 'interview_scheduled',
            'changed_by' => session()->get('user_id'),
            'changed_by_name' => session()->get('user_name'),
            'change_reason' => 'Interview scheduled',
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSuccess('Interview scheduled successfully', $data);
    }

    /** Update interview */
    public function updateInterview($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageInterviews($role)) {
            return $this->jsonError('You do not have permission to update interviews.');
        }

        $interview = $this->interviewsModel->find($id);
        if (!$interview) {
            return $this->jsonError('Interview not found.');
        }

        $rules = [
            'interview_date' => 'permit_empty|valid_date',
            'start_time' => 'permit_empty',
            'end_time' => 'permit_empty',
            'timezone' => 'permit_empty|string',
            'interview_type' => 'permit_empty|in_list[technical,hr,culture_add,technical_pair,panel]',
            'meeting_link' => 'permit_empty|string',
            'instructions' => 'permit_empty|string',
            'internal_notes' => 'permit_empty|string',
            'interview_status' => 'permit_empty|in_list[scheduled,completed,cancelled,rescheduled]',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors());
        }

        $data = [
            'interviewer_id' => $this->request->getPost('interviewer_id') ?: $interview['interviewer_id'],
            'interviewer_name' => $this->request->getPost('interviewer_name') ?: $interview['interviewer_name'],
            'interview_date' => $this->request->getPost('interview_date') ?: $interview['interview_date'],
            'start_time' => $this->request->getPost('start_time') ?: $interview['start_time'],
            'end_time' => $this->request->getPost('end_time') ?: $interview['end_time'],
            'timezone' => $this->request->getPost('timezone') ?: $interview['timezone'],
            'interview_type' => $this->request->getPost('interview_type') ?: $interview['interview_type'],
            'meeting_link' => $this->request->getPost('meeting_link') ?: $interview['meeting_link'],
            'instructions' => $this->request->getPost('instructions') ?: $interview['instructions'],
            'internal_notes' => $this->request->getPost('internal_notes') ?: $interview['internal_notes'],
            'interview_status' => $this->request->getPost('interview_status') ?: $interview['interview_status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->interviewsModel->update($id, $data);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'update_interview',
            'description' => 'Updated interview for application ' . $interview['application_id'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Interview updated successfully');
    }

    /** Complete interview and evaluate */
    public function completeInterview($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return $this->jsonError('You do not have permission to complete interviews.');
        }

        $interview = $this->interviewsModel->find($id);
        if (!$interview) {
            return $this->jsonError('Interview not found.');
        }

        $application = $this->internshipApplicationsModel->find($interview['application_id']);
        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // Update interview status
        $this->interviewsModel->update($id, [
            'interview_status' => 'completed',
            'evaluation_score' => $this->request->getPost('evaluation_score'),
            'final_recommendation' => $this->request->getPost('final_recommendation'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create assessment record
        $this->candidateAssessmentsModel->insert([
            'application_id' => $interview['application_id'],
            'technical_knowledge' => $this->request->getPost('technical_knowledge') ?: null,
            'problem_solving' => $this->request->getPost('problem_solving') ?: null,
            'communication' => $this->request->getPost('communication') ?: null,
            'learning_attitude' => $this->request->getPost('learning_attitude') ?: null,
            'logical_thinking' => $this->request->getPost('logical_thinking') ?: null,
            'availability' => $this->request->getPost('availability') ?: null,
            'overall_score' => $this->request->getPost('overall_score') ?: null,
            'strengths' => $this->request->getPost('strengths') ?: null,
            'improvement_areas' => $this->request->getPost('improvement_areas') ?: null,
            'recommendation' => $this->request->getPost('recommendation'),
            'assessed_by' => session()->get('user_id'),
            'assessed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Update application status based on recommendation
        $rec = $this->request->getPost('final_recommendation');
        $newStatus = match($rec) {
            'strongly_recommended' => 'interview_completed',
            'recommended' => 'under_review',
            'hold' => 'under_review',
            'not_recommended' => 'rejected',
            default => $application['status'],
        };

        $this->internshipApplicationsModel->update($interview['application_id'], [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $interview['application_id'],
            'previous_status' => $application['status'],
            'new_status' => $newStatus,
            'changed_by' => session()->get('user_id'),
            'changed_by_name' => session()->get('user_name'),
            'change_reason' => 'Interview completed with recommendation: ' . $rec,
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'complete_interview',
            'description' => 'Interview completed for ' . $application['applicant_name'] . ', recommendation: ' . $rec,
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Interview completed successfully', [
            'new_application_status' => $newStatus,
            'recommendation' => $rec,
        ]);
    }

    /** Generate offer letter */
    public function generateOffer($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canIssueOffers($role)) {
            return $this->jsonError('You do not have permission to generate offer letters.');
        }

        $application = $this->internshipApplicationsModel->find($id);
        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // Check if application is in a state where offer can be generated
        if (!in_array($application['status'], ['selected', 'interview_completed'])) {
            return $this->jsonError('Application not in a state suitable for offer generation.');
        }

        // Check if offer already exists
        $existingOffer = $this->offerLettersModel->where('application_id', $id)->first();
        if ($existingOffer) {
            return $this->jsonSuccess('Offer letter already exists', $existingOffer);
        }

        // Get the program details
        $program = $this->internshipProgrammesModel->find($application['program_id']);

        // Generate offer reference
        $offerRef = 'INT-OFF-' . date('Y') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);

        // Prepare data for template
        $letterData = [
            'offer_reference' => $offerRef,
            'issue_date' => date('Y-m-d'),
            'candidate_name' => $application['applicant_name'],
            'candidate_address' => '', // Will be collected later or from profile
            'position' => $application['preferred_role'] ?? 'Intern',
            'program_name' => $program ? $program['title'] : 'NGWebD Internship Programme',
            'duration' => $program ? ($program['duration_months'] ?? '6') . ' Months' : '6 Months',
            'start_date' => $program ? $program['start_date'] : date('Y-m-d', strtotime('+6 months')),
            'end_date' => $program ? $program['end_date'] : date('Y-m-d', strtotime('+6 months', strtotime($program['start_date']))),
            'work_mode' => $program ? $program['mode'] : 'remote',
            'weekly_commitment' => $program ? ($program['weekly_hours'] ?? '20') . ' hours' : '20 hours',
            'compensation_disclosure' => 'Unpaid educational internship, subject to legal review and applicable law.',
            'learning_objectives' => 'Gain practical experience in ' . ($application['preferred_role'] ?? 'software development') . '. Develop professional skills. Contribute to real projects.',
            'terms_conditions' => 'Please review the internship programme details carefully. By accepting, you agree to the terms and conditions of the NGWebD Internship Programme.',
            'authorized_signatory' => 'Super Admin, NGWebD Consulting Pvt. Ltd.',
            'company_branding' => 'NGWebD Consulting Pvt. Ltd. - Internship Programme',
        ];

        // Generate PDF using OfferLetterService
        $offerLetterService = new OfferLetterService();
        $htmlContent = $offerLetterService->renderOfferLetterTemplate($letterData);
        $pdfFilename = $offerRef . '.pdf';
        $offerLetterService->generatePdf($htmlContent, $pdfFilename);

        // Save offer letter record
        $data = [
            'application_id' => $id,
            'candidate_name' => $application['applicant_name'],
            'candidate_address' => '', // Will be collected later or from profile
            'position' => $application['preferred_role'] ?? 'Intern',
            'program_name' => $program ? $program['title'] : 'NGWebD Internship Programme',
            'duration' => $program ? ($program['duration_months'] ?? '6') . ' Months' : '6 Months',
            'start_date' => $program ? $program['start_date'] : date('Y-m-d', strtotime('+6 months')),
            'end_date' => $program ? $program['end_date'] : date('Y-m-d', strtotime('+6 months', strtotime($program['start_date']))),
            'work_mode' => $program ? $program['mode'] : 'remote',
            'weekly_commitment' => $program ? ($program['weekly_hours'] ?? '20') . ' hours' : '20 hours',
            'compensation_disclosure' => 'Unpaid educational internship, subject to legal review and applicable law.',
            'learning_objectives' => 'Gain practical experience in ' . ($application['preferred_role'] ?? 'software development') . '. Develop professional skills. Contribute to real projects.',
            'terms_conditions' => 'Please review the internship programme details carefully. By accepting, you agree to the terms and conditions of the NGWebD Internship Programme.',
            'authorized_signatory' => 'Super Admin, NGWebD Consulting Pvt. Ltd.',
            'company_branding' => 'NGWebD Consulting Pvt. Ltd. - Internship Programme',
            'offer_reference' => $offerRef,
            'issue_date' => date('Y-m-d'),
            'acceptance_deadline' => date('Y-m-d', strtotime('+14 days')), // 14 days to accept
            'status' => 'issued',
            'pdf_path' => 'internship/letters/' . $pdfFilename,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->offerLettersModel->insert($data);

        // Create status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $id,
            'previous_status' => $application['status'],
            'new_status' => 'offer_sent',
            'changed_by' => session()->get('user_id'),
            'changed_by_name' => session()->get('user_name'),
            'change_reason' => 'Offer letter generated',
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'generate_offer',
            'description' => 'Offer letter generated for ' . $application['applicant_name'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Offer letter generated successfully', [
            'offer_id' => $this->offerLettersModel->getInsertID(),
            'pdf_path' => $data['pdf_path'],
            'offer_reference' => $offerRef,
        ]);
    }

    /** Send offer (change status to issued) */
    public function sendOffer($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canIssueOffers($role)) {
            return $this->jsonError('You do not have permission to send offers.');
        }

        $offer = $this->offerLettersModel->find($id);
        if (!$offer) {
            return $this->jsonError('Offer letter not found.');
        }

        $this->offerLettersModel->update($id, [
            'status' => 'issued',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'send_offer',
            'description' => 'Offer letter sent to ' . $offer['candidate_name'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Offer letter sent successfully');
    }

    /** Accept offer */
    public function acceptOffer($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        // Allow the candidate to accept - but from admin side we just update

        $offer = $this->offerLettersModel->find($id);
        if (!$offer) {
            return $this->jsonError('Offer letter not found.');
        }

        // Update offer status
        $this->offerLettersModel->update($id, [
            'status' => 'accepted',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create acceptance record
        $this->offerAcceptancesModel->insert([
            'offer_letter_id' => $id,
            'candidate_name' => $offer['candidate_name'],
            'candidate_id' => null, // Will be set when employee profile created
            'accepted_at' => date('Y-m-d H:i:s'),
            'acceptance_type' => 'accepted',
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'accept_offer',
            'description' => 'Offer accepted by ' . $offer['candidate_name'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Offer accepted successfully');
    }

    /** Start onboarding */
    public function startOnboarding($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return $this->jsonError('You do not have permission to start onboarding.');
        }

        $offer = $this->offerLettersModel->find($id);
        if (!$offer) {
            return $this->jsonError('Offer letter not found.');
        }

        $application = $this->internshipApplicationsModel->find($offer['application_id']);
        if (!$application) {
            return $this->jsonError('Application not found.');
        }

        // Check if already onboarding
        $existingChecklist = $this->onboardingChecklistsModel->where('employee_id', null)->where('id', null)->limit(1)->first();
        // Actually, we need to create checklists for the new employee

        // Create onboarding checklists
        $checklistItems = [
            1 => 'Offer accepted',
            2 => 'Identity verification',
            3 => 'Contact details confirmed',
            4 => 'Education details verified',
            5 => 'Resume on file',
            6 => 'Emergency contact',
            7 => 'Profile photograph approved',
            8 => 'Signed documents',
            9 => 'Confidentiality agreement',
            10 => 'Required policies acknowledged',
            11 => 'Mentor assigned',
            12 => 'Department assigned',
            13 => 'Role assigned',
            14 => 'Orientation scheduled',
            15 => 'Joining date confirmed',
        ];

        foreach ($checklistItems as $order => $item) {
            $this->onboardingChecklistsModel->insert([
                'employee_id' => null, // Will be set when employee profile is created
                'checklist_item' => $item,
                'order_index' => $order,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Update application status to onboarding
        $this->internshipApplicationsModel->update($offer['application_id'], [
            'status' => 'onboarding',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create status history entry
        $this->applicationStatusHistoryModel->insert([
            'application_id' => $offer['application_id'],
            'previous_status' => $application['status'],
            'new_status' => 'onboarding',
            'changed_by' => session()->get('user_id'),
            'changed_by_name' => session()->get('user_name'),
            'change_reason' => 'Onboarding started after offer acceptance',
            'changed_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'recruitment',
            'module_id' => $id,
            'action' => 'start_onboarding',
            'description' => 'Onboarding started for ' . $offer['candidate_name'],
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Onboarding started successfully', [
            'checklist_count' => count($checklistItems),
            'application_status' => 'onboarding',
        ]);
    }

    /** API: Get applications with filters */
    public function ajaxApplications()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return $this->jsonError('Unauthorized');
        }

        $status = $this->request->getGet('status');
        $programId = $this->request->getGet('program_id');
        $search = $this->request->getGet('q');

        $query = $this->internshipApplicationsModel->withProgram();

        if ($status) {
            $query->where('status', $status);
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', $search)->orWhere('applicant_email', $search)->orWhere('applicant_mobile', $search);
            });
        }

        $applications = $query->orderBy('applied_at', 'DESC')->findAll();

        return $this->jsonSuccess('Applications loaded', $applications);
    }

    /** API: Get program details */
    public function ajaxProgram($id = null)
    {
        if ($id) {
            $program = $this->internshipProgrammesModel->find($id);
            $positions = $this->internshipPositionsModel->where('program_id', $id)->findAll();
            return $this->jsonSuccess('Program details', array_merge((array)$program, ['positions' => $positions]));
        }

        $programs = $this->internshipProgrammesModel->where('status', 'active')->findAll();
        return $this->jsonSuccess('Programs loaded', $programs);
    }

    /** Get applicant with program details */
    public function withProgram()
    {
        $this->db->table('internship_applications')
            ->select('internship_applications.*, internship_programs.title as program_title, internship_programs.mode, internship_programs.duration_months');
        $this->db->join('internship_programs', 'internship_programs.id = internship_applications.program_id', 'left');
        return $this;
    }

    /** Programme detail page */
    // Route: GET admin/recruitment/program/(:num)
    public function programDetail($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewRecruitment($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to view recruitment.');
        }

        $program = $this->internshipProgrammesModel->find($id);
        if (!$program) {
            return redirect()->to('/admin/recruitment')->with('error', 'Programme not found.');
        }

        $positions = $this->internshipPositionsModel->where('program_id', $id)->orderBy('created_at', 'ASC')->findAll();
        $applications = $this->internshipApplicationsModel->withProgram()
            ->where('program_id', $id)
            ->orderBy('applied_at', 'DESC')
            ->findAll();

        $statusCounts = [];
        foreach ($applications as $app) {
            $statusCounts[$app['status']] = ($statusCounts[$app['status']] ?? 0) + 1;
        }

        return view('admin/recruitment/program_detail', [
            'title'        => $program['title'],
            'program'      => $program,
            'positions'    => $positions,
            'applications' => $applications,
            'statusCounts' => $statusCounts,
        ]);
    }
}