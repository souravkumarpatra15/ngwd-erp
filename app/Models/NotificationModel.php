<?php
namespace App\Models;
use CodeIgniter\Model;
class NotificationModel extends Model {
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id','type','title','message','reference_id','reference_type','is_read','read_at','created_at'];
    protected $createdField = 'created_at';

    public function getUserNotifications(int $userId, int $limit=20) {
        return $this->where('user_id',$userId)->orderBy('created_at','DESC')->limit($limit)->findAll();
    }

    public function getUnreadCount(int $userId): int {
        return $this->where('user_id',$userId)->where('is_read',0)->countAllResults();
    }

    public function markRead(int $id, int $userId): bool {
        return $this->where('id', $id)
            ->where('user_id', $userId)
            ->set(['is_read'=>1,'read_at'=>date('Y-m-d H:i:s')])
            ->update();
    }

    public function markAllRead(int $userId): void {
        $this->where('user_id',$userId)
            ->where('is_read',0)
            ->set(['is_read'=>1,'read_at'=>date('Y-m-d H:i:s')])
            ->update();
    }

    public function notify(int $userId, string $type, string $title, string $message, ?int $referenceId=null, ?string $referenceType=null): int|false {
        if ($userId <= 0) return false;
        return $this->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'is_read' => 0,
            'read_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function notifyMany(array $userIds, string $type, string $title, string $message, ?int $referenceId=null, ?string $referenceType=null): void {
        foreach (array_unique(array_map('intval', $userIds)) as $userId) {
            $this->notify($userId, $type, $title, $message, $referenceId, $referenceType);
        }
    }
}