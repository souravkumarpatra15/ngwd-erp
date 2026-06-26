<?php
namespace App\Models;
use CodeIgniter\Model;
class TaskModel extends Model {
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['project_id','title','description','priority','due_date','completed_date','status','sort_order','notes','assigned_to','created_by'];
}
