<?php
namespace App\Models;
use CodeIgniter\Model;
class MilestoneModel extends Model {
    protected $table = 'milestones';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['project_id','title','description','amount','due_date','completed_date','status','sort_order'];
}
