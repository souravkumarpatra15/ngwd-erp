<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\UserModel;
use App\Models\EmployeeModel;
use App\Services\PmsAuthorizationService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AttendanceController extends BaseController
{
    protected $attendanceModel;
    protected $userModel;
    protected $employeeModel;
    protected $auth;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->attendanceModel = new AttendanceModel();
        $this->userModel = new UserModel();
        $this->employeeModel = new EmployeeModel();
        $this->auth = new PmsAuthorizationService();
    }

    public function index($employeeId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();

        // Resolve which employee's attendance is being viewed
        $employeeRow = $this->employeeModel->where('user_id', session()->get('user_id'))->first();
        $myEmployeeId = $employeeRow['id'] ?? null;
        $isSelfView = $employeeId === null;

        if ($employeeId !== null) {
            // HR/managers may view others; employees only their own
            if (!in_array($role, ['intern', 'permanent_employee'], true) && !$auth->canManageEmployees($role)) {
                return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access attendance.');
            }
        } elseif ($myEmployeeId === null && !in_array($role, ['intern', 'permanent_employee'], true)) {
            return redirect()->to('/admin/dashboard')->with('error', 'No employee record is linked to your account.');
        }

        $employeeId = $employeeId ?? $myEmployeeId;
        $employee = $employeeId ? $this->employeeModel->find((int) $employeeId) : null;

        $attendanceModel = new AttendanceModel();
        $todayStatus = $employeeId ? $attendanceModel->today((int) $employeeId) : null;

        return view('admin/attendance/workspace', [
            'title' => 'Attendance',
            'employee' => $employee,
            'attendanceModel' => $attendanceModel,
            'todayStatus' => $todayStatus,
            'employeeId' => $employeeId,
            'isSelfView' => $isSelfView,
            'canManage' => $auth->canManageEmployees($role),
            'userRole' => $role,
        ]);
    }

    /**
     * Check in (AJAX). Employees check themselves in; HR/managers may act for others via employee_id.
     */
    public function checkIn()
    {
        $employeeId = $this->resolveEmployeeId();
        if ($employeeId === null) {
            return $this->jsonError('No employee record is linked to your account.');
        }

        $existing = $this->attendanceModel->today($employeeId);
        if ($existing) {
            return $this->jsonError('You have already checked in today. Use the check-out button to end your session.');
        }

        $workFromHome = $this->request->getPost('work_from_home') ? true : false;
        $id = $this->attendanceModel->checkIn($employeeId, $workFromHome, (string) ($this->request->getPost('work_from_home_notes') ?? ''));

        if (!$id) {
            return $this->jsonError('Could not record check-in.');
        }

        $row = $this->attendanceModel->find($id);
        return $this->jsonSuccess('Check-in recorded', [
            'employee_id' => $employeeId,
            'check_in'    => $row['check_in'],
            'date'        => $row['date'],
            'status'      => $row['status'],
        ]);
    }

    /**
     * Check out (AJAX)
     */
    public function checkOut()
    {
        $employeeId = $this->resolveEmployeeId();
        if ($employeeId === null) {
            return $this->jsonError('No employee record is linked to your account.');
        }

        $result = $this->attendanceModel->checkOut($employeeId);
        if (!$result) {
            return $this->jsonError('No open attendance record for today, or already checked out.');
        }

        return $this->jsonSuccess('Check-out recorded', [
            'employee_id'   => $employeeId,
            'check_in'      => $result['check_in'],
            'check_out'     => $result['check_out'],
            'hours_worked'  => $result['hours_worked'],
            'status'        => $result['status'],
        ]);
    }

    /** Today's status for the workspace JS */
    public function getTodayStatus($employeeId = null)
    {
        $employeeId = (int) ($employeeId ?? $this->resolveEmployeeId());
        if (!$employeeId) return $this->jsonError('No employee record found.');

        $row = $this->attendanceModel->today($employeeId);
        $summary = $this->attendanceModel->getMonthlySummary($employeeId);

        return $this->jsonSuccess('Status', array_merge([
            'status'          => $row['status'] ?? null,
            'check_in'        => $row['check_in'] ?? null,
            'check_out'       => $row['check_out'] ?? null,
            'hours_worked'    => $row['hours_worked'] ?? null,
            'work_from_home'  => $row['work_from_home'] ?? 0,
        ], $summary));
    }

    /**
     * Monthly attendance summary
     */
    public function summary($employeeId = null, $month = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to view attendance summary.');
        }

        $employee = $employeeId ? $this->employeeModel->find((int) $employeeId) : null;
        $summary = $this->attendanceModel->getMonthlySummary($employeeId ? (int) $employeeId : null, $month);
        $statusCounts = $this->attendanceModel->getStatusCounts($employeeId ? (int) $employeeId : null, $month);

        return view('admin/attendance/summary', [
            'title' => 'Attendance Summary',
            'employee' => $employee,
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'month' => $month ?? date('Y-m'),
            'userRole' => $role,
        ]);
    }

    /**
     * Attendance calendar
     */
    public function calendar($employeeId = null, $month = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to view attendance calendar.');
        }

        $employee = $employeeId ? $this->employeeModel->find((int) $employeeId) : null;
        $month = $month ?? date('Y-m');

        $attendances = $employeeId
            ? $this->attendanceModel->thisMonth((int) $employeeId, $month)
            : [];

        return view('admin/attendance/calendar', [
            'title' => 'Attendance Calendar',
            'employee' => $employee,
            'month' => $month,
            'attendances' => $attendances,
            'userRole' => $role,
        ]);
    }

    /**
     * Leave/correction request (AJAX)
     */
    public function requestCorrection()
    {
        $employeeId = $this->resolveEmployeeId();
        if ($employeeId === null) {
            return $this->jsonError('No employee record is linked to your account.');
        }

        $type = $this->request->getPost('type') ?? $this->request->getGet('type') ?? '';
        $details = $this->request->getPost('details') ?? $this->request->getGet('details') ?? '';

        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id'     => session()->get('user_id'),
            'module'      => 'attendance',
            'module_id'   => $employeeId,
            'action'      => 'correction_request',
            'description' => 'Attendance correction requested: type=' . $type . ', details=' . $details,
            'ip_address'  => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Correction request submitted', [
            'employee_id' => $employeeId,
            'type'        => $type,
            'details'     => $details,
        ]);
    }

    /**
     * Admin: apply or reject an attendance correction (AJAX)
     * POST fields: status (present/absent/half_day/leave/work_from_home), day_type, hours_worked
     */
    public function approveCorrection($recordId = null)
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return $this->jsonError('You do not have permission to approve attendance corrections.');
        }

        $record = $this->attendanceModel->find($recordId);
        if (!$record) {
            return $this->jsonError('Attendance record not found.');
        }

        $newStatus = $this->request->getPost('status');
        $validStatuses = ['present', 'absent', 'half_day', 'leave', 'work_from_home'];
        if ($newStatus && !in_array($newStatus, $validStatuses, true)) {
            return $this->jsonError('Invalid status value.');
        }

        $data = ['updated_at' => date('Y-m-d H:i:s')];
        if ($newStatus) $data['status'] = $newStatus;
        if ($this->request->getPost('day_type')) $data['day_type'] = $this->request->getPost('day_type');
        if ($this->request->getPost('hours_worked') !== null) $data['hours_worked'] = (float) $this->request->getPost('hours_worked');
        if ($this->request->getPost('check_in')) $data['check_in'] = $this->request->getPost('check_in');
        if ($this->request->getPost('check_out')) $data['check_out'] = $this->request->getPost('check_out');

        $this->attendanceModel->update($record['id'], $data);

        $activity = new \App\Models\ActivityModel();
        $activity->insert([
            'user_id'     => session()->get('user_id'),
            'module'      => 'attendance',
            'module_id'   => $record['id'],
            'action'      => 'correction_approved',
            'description' => 'Attendance correction applied to record #' . $record['id'] . ' for employee ID ' . $record['employee_id'],
            'ip_address'  => $this->request->getIPAddress(),
        ]);

        return $this->jsonSuccess('Correction applied', [
            'record_id'   => $record['id'],
            'employee_id' => $record['employee_id'],
        ]);
    }

    /**
     * AJAX: employee list for HR/manager filters
     */
    public function ajaxEmployees()
    {
        $role = session()->get('user_role');
        $auth = new \App\Services\PmsAuthorizationService();
        if (!$auth->canManageEmployees($role)) {
            return $this->jsonError('Access denied.');
        }

        $employees = $this->employeeModel->getWithDetails(['employment_status' => 'active']);
        $result = [];
        foreach ($employees as $e) {
            $result[] = [
                'id'              => $e['id'],
                'employee_id'     => $e['employee_id'],
                'name'            => $e['user_name'] ?? '',
                'department_id'   => $e['department_id'],
                'designation_id'  => $e['designation_id'],
                'employment_type' => $e['employment_type'],
            ];
        }

        return $this->jsonSuccess('Employees retrieved', $result);
    }

    /**
     * Resolve which employee id the current request targets:
     * explicit employee_id (managers only) or the logged-in user's own employee record.
     */
    protected function resolveEmployeeId(): ?int
    {
        $requested = $this->request->getPost('employee_id') ?? $this->request->getGet('employee_id');
        if ($requested && in_array(session()->get('user_role'), ['intern', 'permanent_employee'], true) === false) {
            return (int) $requested;
        }

        $row = $this->employeeModel->where('user_id', session()->get('user_id'))->first();
        return $row ? (int) $row['id'] : null;
    }
}
