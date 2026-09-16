<?php
namespace App\Models;

use CodeIgniter\Model;

class DeliverableFileModel extends Model
{
    protected $table = 'deliverable_files';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['deliverable_id', 'filename', 'original_name', 'mime_type', 'size', 'is_image', 'is_video', 'uploaded_by', 'created_at'];

    public function forDeliverable(int $deliverableId): array
    {
        return $this->select('deliverable_files.*, users.name as uploader_name')
            ->join('users', 'users.id = deliverable_files.uploaded_by', 'left')
            ->where('deliverable_id', $deliverableId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function countByDeliverables(array $ids): array
    {
        if (empty($ids)) return [];
        $rows = $this->select('deliverable_id, COUNT(*) as cnt')->whereIn('deliverable_id', $ids)->groupBy('deliverable_id')->findAll();
        return array_column($rows, 'cnt', 'deliverable_id');
    }
}
