<?php
namespace App\Models;

use CodeIgniter\Model;

class PayrollPeriodsModel extends Model
{
    protected $table      = 'payroll_periods';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'name', 'period', 'start_date', 'end_date', 'status',
        'processed_by', 'processed_at', 'created_at', 'updated_at',
    ];

    public function current(): ?array
    {
        return $this->where('start_date <=', date('Y-m-d'))
            ->where('end_date >=', date('Y-m-d'))
            ->first() ?: null;
    }
}
