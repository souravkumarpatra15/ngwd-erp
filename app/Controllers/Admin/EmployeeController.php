<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\EmployeeDepartmentModel;
use App\Models\EmployeeDesignationModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class EmployeeController extends BaseController
{
    protected $helpers = ['url', 'form', 'html', 'csrf'];
    protected $employeeModel;
    protected $departmentModel;
    protected $designationModel;
    protected $userModel;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new EmployeeDepartmentModel();
        $this->designationModel = new EmployeeDesignationModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access employee management.');
        }

        $search = $this->request->getGet('q');
        $departmentId = $this->request->getGet('department_id');
        $employmentStatus = $this->request->getGet('employment_status');
        $employmentType = $this->request->getGet('employment_type');

        $query = $this->employeeModel->withDetails();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', $search)->orWhere('users.name', $search)->orWhere('users.email', $search);
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employmentStatus) {
            $query->where('employment_status', $employmentStatus);
        }

        if ($employmentType) {
            $query->where('employment_type', $employmentType);
        }

        $employees = $query->orderBy('joining_date', 'DESC')->paginate(20);
        $pager = $this->employeeModel->pager;

        $departments = $this->departmentModel->activeForClient(session()->get('client_id'))->findAll();

        return view('admin/employees/index', [
            'title' => 'Employee Management',
            'employees' => $employees,
            'pager' => $pager,
            'departments' => $departments,
            'search' => $search,
            'selected_department' => $departmentId,
            'selected_status' => $employmentStatus,
            'selected_type' => $employmentType,
            'canManageEmployees' => $auth->canManageEmployees($role),
        ]);
    }

    public function create()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to create employees.');
        }

        $departments = $this->departmentModel->activeForClient(session()->get('client_id'))->findAll();
        $designations = $this->designationModel->activeForClient(session()->get('client_id'))->findAll();
        $users = $this->userModel->where('id !=', 0)->where('is_active', 1)->where('role !=', 'client')->findAll();

        return view('admin/employees/create', [
            'title' => 'Create Employee',
            'departments' => $departments,
            'designations' => $designations,
            'users' => $users,
        ]);
    }

    public function store()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return $this->jsonError('You do not have permission to create employees.');
        }

        $rules = [
            'user_id'       => 'required|integer',
            'employee_id'   => 'required|string|is_unique[employees.employee_id]',
            'department_id' => 'permit_empty|integer',
            'designation_id'=> 'permit_empty|integer',
            'employment_type' => 'required|in_list[permanent,full_time,part_time,contract,probation,internship]',
            'employment_status' => 'required|in_list[draft,active,on_leave,probation,notice_period,resigned,terminated,inactive]',
            'joining_date'  => 'required|valid_date',
            'profile_photo' => 'permit_empty|is_image[profile_photo]|max_size[profile_photo,2048]|mime_in[profile_photo,image/jpeg,image/png]',
            'emergency_contact_name' => 'permit_empty|string',
            'emergency_contact_phone' => 'permit_empty|string',
            'address_line1' => 'permit_empty|string',
            'city' => 'permit_empty|string',
            'state' => 'permit_empty|string',
            'pincode' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors());
        }

        $data = [
            'user_id'       => $this->request->getPost('user_id'),
            'employee_id'   => $this->request->getPost('employee_id'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'designation_id'=> $this->request->getPost('designation_id') ?: null,
            'employment_type' => $this->request->getPost('employment_type'),
            'employment_status' => $this->request->getPost('employment_status'),
            'joining_date'  => $this->request->getPost('joining_date'),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        // Handle profile photo upload
        $file = $this->request->getFile('profile_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getExtension();
            $name = 'emp_' . date('Ymd His') . '.' . $ext;
            $file->move('employee_photos', $name);
            $data['profile_photo'] = 'employee_photos/' . $name;
        }

        $this->employeeModel->insert($data);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'hrms',
            'module_id' => $this->employeeModel->getInsertID(),
            'action' => 'create',
            'description' => 'Created new employee record',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Employee created successfully', ['employee_id' => $this->employeeModel->getInsertID()]);
    }

    public function edit($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to edit employees.');
        }

        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            return redirect()->to('/admin/employees')->with('error', 'Employee not found.');
        }

        $departments = $this->departmentModel->activeForClient(session()->get('client_id'))->findAll();
        $designations = $this->designationModel->activeForClient(session()->get('client_id'))->findAll();
        $users = $this->userModel->where('id !=', 0)->where('is_active', 1)->where('role !=', 'client')->findAll();

        return view('admin/employees/edit', [
            'title' => 'Edit Employee',
            'employee' => $employee,
            'departments' => $departments,
            'designations' => $designations,
            'users' => $users,
        ]);
    }

    public function update($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return $this->jsonError('You do not have permission to update employees.');
        }

        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            return $this->jsonError('Employee not found.');
        }

        $rules = [
            'user_id'       => 'permit_empty|integer',
            'employee_id'   => 'permit_empty|string|is_unique[employees.employee_id,id,' . $id . ']',
            'department_id' => 'permit_empty|integer',
            'designation_id'=> 'permit_empty|integer',
            'employment_type' => 'permit_empty|in_list[permanent,full_time,part_time,contract,probation,internship]',
            'employment_status' => 'permit_empty|in_list[draft,active,on_leave,probation,notice_period,resigned,terminated,inactive]',
            'joining_date'  => 'permit_empty|valid_date',
            'profile_photo' => 'permit_empty|is_image[profile_photo]|max_size[profile_photo,2048]|mime_in[profile_photo,image/jpeg,image/png]',
            'emergency_contact_name' => 'permit_empty|string',
            'emergency_contact_phone' => 'permit_empty|string',
            'address_line1' => 'permit_empty|string',
            'city' => 'permit_empty|string',
            'state' => 'permit_empty|string',
            'pincode' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonError('Validation failed', $this->validator->getErrors());
        }

        $data = [
            'user_id'       => $this->request->getPost('user_id') ?: $employee['user_id'],
            'employee_id'   => $this->request->getPost('employee_id') ?: $employee['employee_id'],
            'department_id' => $this->request->getPost('department_id') ?: $employee['department_id'],
            'designation_id'=> $this->request->getPost('designation_id') ?: $employee['designation_id'],
            'employment_type' => $this->request->getPost('employment_type') ?: $employee['employment_type'],
            'employment_status' => $this->request->getPost('employment_status') ?: $employee['employment_status'],
            'joining_date'  => $this->request->getPost('joining_date') ?: $employee['joining_date'],
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        // Handle profile photo upload
        $file = $this->request->getFile('profile_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getExtension();
            $name = 'emp_' . date('Ymd His') . '.' . $ext;
            $file->move('employee_photos', $name);
            $data['profile_photo'] = 'employee_photos/' . $name;
        }

        $this->employeeModel->update($id, $data);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'hrms',
            'module_id' => $id,
            'action' => 'update',
            'description' => 'Updated employee record',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Employee updated successfully');
    }

    public function delete($id = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to delete employees.');
        }

        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            return redirect()->to('/admin/employees')->with('error', 'Employee not found.');
        }

        // Soft delete - just update status to 'terminated' instead of actual delete
        $this->employeeModel->update($id, [
            'employment_status' => 'terminated',
            'termination_date' => date('Y-m-d'),
            'last_working_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'hrms',
            'module_id' => $id,
            'action' => 'terminate',
            'description' => 'Terminated employee record',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Employee terminated successfully');
    }

    public function show($id = null)
    {
        $employee = $this->employeeModel->withDetails()->find($id);
        if (!$employee) {
            return $this->jsonError('Employee not found.');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $employee,
        ]);
    }

    /** API: Get departments for a client */
    public function ajaxDepartments()
    {
        $departments = $this->departmentModel->activeForClient(session()->get('client_id'))->findAll();
        return $this->jsonSuccess('Departments loaded', $departments);
    }

    /** API: Get designations for a client */
    public function ajaxDesignations()
    {
        $designations = $this->designationModel->activeForClient(session()->get('client_id'))->findAll();
        return $this->jsonSuccess('Designations loaded', $designations);
    }

    /** API: Get employees for a department */
    public function ajaxEmployeesByDepartment($departmentId = null)
    {
        $employees = $this->employeeModel->where('employees.department_id', (int) $departmentId)->active()->withDetails()->findAll();
        return $this->jsonSuccess('Employees loaded', $employees);
    }

    /** API: Get employee statistics */
    public function ajaxStats()
    {
        $total = $this->employeeModel->countAll();
        $active = $this->employeeModel->active()->countAllResults();
        $interns = $this->employeeModel->interns()->countAllResults();
        $permanent = $this->employeeModel->permanent()->countAllResults();

        return $this->jsonSuccess('Statistics', [
            'total' => $total,
            'active' => $active,
            'interns' => $interns,
            'permanent' => $permanent,
        ]);
    }
}