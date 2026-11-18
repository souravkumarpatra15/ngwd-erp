<?php
namespace App\Models;

use CodeIgniter\Model;

class PayrollComponentsModel extends Model
{
    protected $table      = 'payroll_components';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'component_name', 'component_type', 'is_statutory',
        'configurable_rate', 'default_rate', 'applicable_threshold',
        'description', 'sort_order', 'created_at', 'updated_at',
    ];

    public function earnings(): array
    {
        return $this->where('component_type', 'earning')->orderBy('sort_order', 'ASC')->findAll();
    }

    public function deductions(): array
    {
        return $this->where('component_type', 'deduction')->orderBy('sort_order', 'ASC')->findAll();
    }
}
