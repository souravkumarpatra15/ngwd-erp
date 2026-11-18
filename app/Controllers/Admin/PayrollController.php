<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\PayrollPeriodsModel; // Will create later
use App\Models\PayrollComponentsModel; // Will create later
use App\Models\EmployeeSalaryComponentsModel;
use App\Models\PayrollRecordsModel;
use App\Models\PayslipsModel;
use App\Models\PayrollApprovalsModel;
use App\Models\SalaryPaymentsModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\CLIRequest;
use Config\Services;

class PayrollController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $employeeModel;
    protected $userModel;
    protected $auth;
    protected $payrollPeriodsModel;
    protected $payrollComponentsModel;
    protected $employeeSalaryComponentsModel;
    protected $payrollRecordsModel;
    protected $payslipsModel;
    protected $payrollApprovalsModel;
    protected $salaryPaymentsModel;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->employeeModel = new EmployeeModel();
        $this->userModel = new UserModel();
        $this->auth = new PmsAuthorizationService();
        // Note: Additional models would be created in subsequent migrations
        // For now, using direct database queries where needed
    }

    /**
     * Payroll index - show payroll management dashboard
     */
    public function index()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewPayroll($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access payroll.');
        }

        // Get active payroll periods
        // Note: payroll_periods model would be created in migration
        // For now, using direct approach

        // Get employees with salary structure details
        $employees = $this->employeeModel->getWithDetails(['employment_status' => 'active']);

        return view('admin/payroll/index', [
            'title' => 'Payroll Management',
            'employees' => $employees,
            'canViewPayroll' => $auth->canViewPayroll($role),
        ]);
    }

    /**
     * Set up salary structure for an employee
     */
    public function setupStructure($employeeId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return $this->jsonError('You do not have permission to set up salary structures.');
        }

        $employee = $this->employeeModel->find($employeeId);
        if (!$employee) {
            return $this->jsonError('Employee not found.');
        }

        return view('admin/payroll/structure', [
            'title' => 'Set Up Salary Structure',
            'employee' => $employee,
        ]);
    }

    /**
     * Save salary structure for an employee
     */
    public function saveStructure($employeeId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return $this->jsonError('You do not have permission to set up salary structures.');
        }

        $employee = $this->employeeModel->find($employeeId);
        if (!$employee) {
            return $this->jsonError('Employee not found.');
        }

        $basic = (float) $this->request->getPost('basic_salary');
        if ($basic <= 0) {
            return $this->jsonError('Basic salary must be greater than zero.');
        }

        try {
            $this->db->transBegin();

            // Seed standard payroll components if missing
            $components = [
                'Basic'             => ['type' => 'earning',  'rate' => $basic],
                'HRA'               => ['type' => 'earning',  'rate' => round($basic * min(100, max(0, (float) $this->request->getPost('hra_percent'))) / 100, 2)],
                'Other Allowances'  => ['type' => 'earning',  'rate' => (float) $this->request->getPost('other_allowances')],
                'Provident Fund'    => ['type' => 'deduction','rate' => round($basic * min(100, max(0, (float) $this->request->getPost('pf_percent'))) / 100, 2)],
                'Professional Tax'  => ['type' => 'deduction','rate' => (float) $this->request->getPost('professional_tax')],
                'TDS'               => ['type' => 'deduction','rate' => round($basic * min(100, max(0, (float) $this->request->getPost('tds_percent'))) / 100, 2)],
            ];

            $effectiveFrom = $this->request->getPost('effective_from') ?: date('Y-m-d');

            $componentIds = [];
            foreach (array_keys($components) as $name) {
                $row = $this->db->table('payroll_components')->where('component_name', $name)->get()->getRowArray();
                if (!$row) {
                    $this->db->table('payroll_components')->insert([
                        'component_name' => $name,
                        'component_type' => $components[$name]['type'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $componentIds[$name] = (int) $this->db->insertID();
                } else {
                    $componentIds[$name] = (int) $row['id'];
                }
            }

            // Replace the employee's current structure
            $this->db->table('employee_salary_components')->where('employee_id', (int) $employeeId)->delete();

            foreach ($components as $name => $meta) {
                $this->db->table('employee_salary_components')->insert([
                    'employee_id'    => (int) $employeeId,
                    'component_id'   => $componentIds[$name],
                    'value'          => $meta['rate'],
                    'effective_from' => $effectiveFrom,
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->transCommit();
            return $this->jsonSuccess('Salary structure saved successfully');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                return $this->jsonError('Payroll tables are missing. Import all_pending_migrations.sql first.');
            }
            return $this->jsonError('Failed to save salary structure: ' . $e->getMessage());
        }
    }

    /**
     * Process monthly payroll
     */
    public function processPayroll($periodId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewPayroll($role)) {
            return $this->jsonError('You do not have permission to process payroll.');
        }

        // Would calculate payroll for the given period
        // For now, return success
        return $this->jsonSuccess('Payroll processing initiated', ['period_id' => $periodId]);
    }

    /**
     * Generate payslip for an employee
     */
    public function generatePayslip($employeeId = null, $periodId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewPayroll($role)) {
            return $this->jsonError('You do not have permission to generate payslips.');
        }

        // Would generate payslip PDF
        // For now, return success
        return $this->jsonSuccess('Payslip generation initiated', ['employee_id' => $employeeId, 'period_id' => $periodId]);
    }

    /**
     * Get payroll approval queue
     */
    public function approvalQueue()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewPayroll($role)) {
            return $this->jsonError('You do not have permission to view payroll approvals.');
        }

        // Would return approval queue
        return $this->jsonSuccess('Approval queue retrieved');
    }

    /**
     * Approve a payroll record
     */
    public function approvePayroll($recordId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canViewPayroll($role)) {
            return $this->jsonError('You do not have permission to approve payroll.');
        }

        // Would approve the payroll record
        return $this->jsonSuccess('Payroll approved');
    }
}