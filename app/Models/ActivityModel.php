<?php
namespace App\Models;
use CodeIgniter\Model;
class ActivityModel extends Model {
    protected $table = 'activities';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id','module','module_id','action','description','ip_address'];
    protected $createdField = 'created_at';
}
