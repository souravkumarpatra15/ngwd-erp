<?php
namespace App\Models;
use CodeIgniter\Model;
class ProjectModel extends Model {
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $allowedFields = ['project_number','client_id','name','type','description','start_date','delivery_date','budget','advance_paid','total_paid','status','notes','created_by'];
    public function getWithClient($id) {
        return $this->db->table('projects')->select('projects.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp, clients.phone as client_phone')
            ->join('clients','clients.id = projects.client_id','left')->where('projects.id',$id)->get()->getRowArray();
    }
    public function getStatusChart() {
        $rows = $this->db->table('projects')->select('status, COUNT(*) as count')->where('deleted_at IS NULL')->groupBy('status')->get()->getResultArray();
        return array_column($rows,'count','status');
    }
    public function getProgress($projectId): float {
        $ms = $this->db->table('milestones')->where('project_id',$projectId)->get()->getResultArray();
        if (empty($ms)) return 0;
        $done = count(array_filter($ms, fn($m) => in_array($m['status'],['completed','paid'])));
        return round(($done/count($ms))*100,0);
    }
}
