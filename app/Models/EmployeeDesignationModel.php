<?php
namespace App\Models;

use CodeIgniter\Model;

class EmployeeDesignationModel extends Model
{
    protected $table      = 'employee_designations';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;
    protected $allowedFields = [
        'client_id', 'title', 'code', 'department_id', 'description',
        'reporting_manager_title', 'employment_category', 'status', 'sort_order',
    ];

    /** Get active designations for a client (null client returns all active) */
    public function activeForClient(?int $clientId = null)
    {
        $q = $this->where('status', 'active');
        if ($clientId !== null) {
            $q->where('client_id', $clientId);
        }
        return $q;
    }

    /** Get designation with department name */
    public function getWithDepartment(int $designationId = null): array
    {
        $b = $this->db->table('employee_designations')
            ->select('employee_designations.*, employee_departments.name as department_name')
            ->join('employee_departments', 'employee_departments.id = employee_designations.department_id', 'left');

        if ($designationId !== null) {
            $b->where('employee_designations.id', $designationId);
        }

        return $b->orderBy('employee_designations.title', 'ASC')->get()->getResultArray();
    }
}