<?php
namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table      = 'employees';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;
    protected $allowedFields = [
        'user_id', 'employee_id', 'department_id', 'designation_id',
        'employment_type', 'employment_status', 'joining_date',
        'probation_end_date', 'termination_date', 'last_working_date',
        'profile_photo', 'emergency_contact_name', 'emergency_contact_phone',
        'emergency_contact_relation', 'address_line1', 'address_line2',
        'city', 'state', 'pincode', 'country', 'blood_group', 'marital_status',
        'nationality', 'pan_number', 'aadhar_number', 'bank_name',
        'bank_account_number', 'bank_ifsc', 'designation_at_joining',
        'department_at_joining', 'resignation_reason', 'notes',
    ];

    // ── Employment status scopes ──────────────────────────────────────────────

    /** Only active employees */
    public function active()
    {
        return $this->where('employment_status', 'active');
    }

    /** Only interns */
    public function interns()
    {
        return $this->where('employment_type', 'internship');
    }

    /** Only permanent employees */
    public function permanent()
    {
        return $this->where('employment_type', 'permanent');
    }

    /** On leave */
    public function onLeave()
    {
        return $this->where('employment_status', 'on_leave');
    }

    /** Probationers */
    public function probationers()
    {
        return $this->where('employment_status', 'probation');
    }

    /** Resigned or terminated */
    public function inactive()
    {
        return $this->whereIn('employment_status', ['resigned', 'terminated', 'inactive']);
    }

    // ── Employment type scopes ────────────────────────────────────────────────

    /** Full-time */
    public function fullTime()
    {
        return $this->where('employment_type', 'full_time');
    }

    /** Part-time */
    public function partTime()
    {
        return $this->where('employment_type', 'part_time');
    }

    /** Contract */
    public function contract()
    {
        return $this->where('employment_type', 'contract');
    }

    /** Probation */
    public function probation()
    {
        return $this->where('employment_type', 'probation');
    }

    // ── Scope: linked to a user ───────────────────────────────────────────────

    public function forUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    // ── Lookups ──────────────────────────────────────────────────────────────

    /** Find employee by employee_id */
    public function findByEmployeeId(string $employeeId): ?array
    {
        return $this->where('employee_id', $employeeId)->first();
    }

    /** Find employee by user_id */
    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /** Find employee by aadhar_number */
    public function findByAadhar(string $aadhar): ?array
    {
        return $this->where('aadhar_number', $aadhar)->first();
    }

    /** Count by status */
    public function countByStatus(): array
    {
        $rows = $this->select('employment_status, COUNT(*) as cnt')->groupBy('employment_status')->findAll();
        return array_column($rows, 'cnt', 'employment_status');
    }

    /** Count by employment type */
    public function countByEmploymentType(): array
    {
        $rows = $this->select('employment_type, COUNT(*) as cnt')->groupBy('employment_type')->findAll();
        return array_column($rows, 'cnt', 'employment_type');
    }

    /** Count by department */
    public function countByDepartment(): array
    {
        $rows = $this->select('department_id, COUNT(*) as cnt')->whereNotNull('department_id')->groupBy('department_id')->findAll();
        return array_column($rows, 'cnt', 'department_id');
    }

    /** Get with details including department and designation */
    public function getWithDetails(array $filters = []): array
    {
        $b = $this->db->table('employees')
            ->select('employees.*, departments.name as department_name, designations.title as designation_title, users.name as user_name, users.email as user_email')
            ->join('employee_departments as departments', 'departments.id = employees.department_id', 'left')
            ->join('employee_designations as designations', 'designations.id = employees.designation_id', 'left')
            ->join('users as users', 'users.id = employees.user_id', 'left');

        foreach (['employment_type', 'employment_status', 'department_id'] as $key) {
            if (!empty($filters[$key])) $b->where('employees.' . $key, $filters[$key]);
        }

        return $b->orderBy('employees.joining_date', 'DESC')->get()->getResultArray();
    }

    /**
     * Chainable scope: attach department/designation/user details to the
     * model's shared builder, so subsequent where()/paginate()/find() calls
     * work on the joined query.
     */
    public function withDetails()
    {
        $this->builder()
            ->select('employees.*, departments.name as department_name, designations.title as designation_title, users.name as user_name, users.email as user_email')
            ->join('employee_departments as departments', 'departments.id = employees.department_id', 'left')
            ->join('employee_designations as designations', 'designations.id = employees.designation_id', 'left')
            ->join('users as users', 'users.id = employees.user_id', 'left');
        return $this;
    }
}