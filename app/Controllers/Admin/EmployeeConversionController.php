<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\EmployeeDepartmentModel;
use App\Models\EmployeeDesignationModel;
use App\Models\OfferLettersModel;
use App\Models\ApplicationStatusHistoryModel;
use App\Models\ActivityModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\CLIRequest;
use Config\Services;

class EmployeeConversionController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $employeeModel;
    protected $userModel;
    protected $auth;
    protected $offerLettersModel;
    protected $applicationStatusHistoryModel;
    protected $activityModel;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->employeeModel = new EmployeeModel();
        $this->userModel = new UserModel();
        $this->auth = new PmsAuthorizationService();
        $this->offerLettersModel = new OfferLettersModel();
        $this->applicationStatusHistoryModel = new ApplicationStatusHistoryModel();
        $this->activityModel = new ActivityModel();
    }

    /**
     * Show intern profile for conversion
     */
    public function index($userId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access employee conversion.');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('/admin/employees')->with('error', 'User not found.');
        }

        $employee = $this->employeeModel->where('user_id', $userId)->first();
        if (!$employee) {
            return redirect()->to('/admin/employees')->with('error', 'Employee record not found.');
        }

        // Check employment type
        $isIntern = $employee['employment_type'] === 'internship';

        return view('admin/conversion/index', [
            'title' => 'Intern to Permanent Conversion',
            'user' => $user,
            'employee' => $employee,
            'isIntern' => $isIntern,
            'canConvert' => $auth->canViewOnboarding($role) && $isIntern,
        ]);
    }

    /**
     * Process the intern to permanent employee conversion
     */
    public function convert($userId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();

        // Check HR approval first
        if (!$auth->canViewOnboarding($role)) {
            return $this->jsonError('You do not have permission to convert employees.');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->jsonError('User not found.');
        }

        $employee = $this->employeeModel->where('user_id', $userId)->first();
        if (!$employee) {
            return $this->jsonError('Employee record not found.');
        }

        // Check if already permanent
        if ($employee['employment_type'] !== 'internship') {
            return $this->jsonError('This employee is not an intern.');
        }

        // Start database transaction
        $this->db->transStart();

        try {
            // 1. Update user role from 'intern' to 'permanent_employee'
            $this->userModel->update($userId, ['role' => 'permanent_employee']);

            // 2. Update employment type from 'internship' to 'permanent'
            $this->employeeModel->update($employee['id'], [
                'employment_type' => 'permanent',
                'employment_status' => 'active',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // 3. Keep the same employee ID (don't generate new one)
            // Keep the same department_id if set
            // Keep the same designation_id if set

            // 4. Record conversion in application status history if applicable
            // Check if there's an associated application
            $application = \Config\Database::connect()
                ->table('internship_applications')
                ->where('applicant_email', $user['email'])
                ->get()->getRowArray();

            if ($application) {
                // Update application status to reflect conversion
                \Config\Database::connect()
                    ->table('internship_applications')
                    ->where('id', $application['id'])
                    ->update(['status' => 'joined']);

                // Record conversion history
                $this->applicationStatusHistoryModel->insert([
                    'application_id' => $application['id'],
                    'previous_status' => $application['status'],
                    'new_status' => 'converted_to_permanent',
                    'changed_by' => session()->get('user_id'),
                    'changed_by_name' => session()->get('user_name'),
                    'change_reason' => 'Intern converted to permanent employee',
                    'changed_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // 5. Log the conversion activity
            $this->activityModel->insert([
                'user_id' => session()->get('user_id'),
                'module' => 'hrms',
                'module_id' => $employee['id'],
                'action' => 'convert_to_permanent',
                'description' => 'Intern ' . $user['name'] . ' converted to permanent employee',
                'ip_address' => $this->request->getIPAddress(),
            ]);

            // Commit transaction
            $this->db->transComplete();

            return $this->jsonSuccess('Employee converted to permanent successfully', [
                'user_id' => $userId,
                'employee_id' => $employee['employee_id'],
                'new_role' => 'permanent_employee',
                'employment_type' => 'permanent',
                'conversion_date' => date('Y-m-d'),
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->jsonError('Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Show conversion form with recommendations
     */
    public function form($userId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewOnboarding($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access conversion form.');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('/admin/employees')->with('error', 'User not found.');
        }

        $employee = $this->employeeModel->where('user_id', $userId)->first();

        return view('admin/conversion/form', [
            'title' => 'Intern to Permanent Conversion Form',
            'user' => $user,
            'employee' => $employee ?? [],
        ]);
    }
}