<?php
namespace App\Models;
use CodeIgniter\Model;

class TaskAttachmentModel extends Model
{
    protected $table = 'task_attachments';
    protected $primaryKey = 'id';
    protected $useTimestamps = false; // created_at set manually below
    protected $allowedFields = ['task_id', 'filename', 'original_name', 'mime_type', 'size', 'is_image', 'uploaded_by', 'created_at'];

    public function forTask(int $taskId): array
    {
        return $this->select('task_attachments.*, users.name as uploader_name')
            ->join('users', 'users.id = task_attachments.uploaded_by', 'left')
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
