<?php
namespace App\Models;

use CodeIgniter\Model;

class ApplicationStatusHistoryModel extends Model
{
    protected $table      = 'application_status_history';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'application_id', 'previous_status', 'new_status',
        'changed_by', 'changed_by_name', 'change_reason',
        'notes', 'changed_at',
    ];

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('changed_at', 'DESC')
            ->findAll();
    }
}
