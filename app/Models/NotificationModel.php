<?php
namespace App\Models;
use CodeIgniter\Model;
class NotificationModel extends Model {
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id','type','title','message','reference_id','reference_type','is_read','read_at'];
    protected $createdField = 'created_at';
    public function getUserNotifications(int $userId, int $limit=20) {
        return $this->where('user_id',$userId)->orderBy('created_at','DESC')->limit($limit)->findAll();
    }
    public function getUnreadCount(int $userId): int {
        return $this->where('user_id',$userId)->where('is_read',0)->countAllResults();
    }
    public function markRead(int $id): void { $this->update($id,['is_read'=>1,'read_at'=>date('Y-m-d H:i:s')]); }
    public function markAllRead(int $userId): void { $this->where('user_id',$userId)->where('is_read',0)->set(['is_read'=>1,'read_at'=>date('Y-m-d H:i:s')])->update(); }
}
