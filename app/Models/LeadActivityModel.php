<?php
namespace App\Models;
use CodeIgniter\Model;
class LeadActivityModel extends Model {
    protected $table = 'lead_activities';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['lead_id','user_id','action','notes','follow_up_date'];
    protected $createdField = 'created_at';
}
