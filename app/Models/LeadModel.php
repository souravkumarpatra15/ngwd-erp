<?php
namespace App\Models;
use CodeIgniter\Model;
class LeadModel extends Model {
    protected $table = 'leads';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $allowedFields = ['lead_number','name','company_name','mobile','whatsapp','email','address','source','budget','requirement','notes','status','follow_up_date','assigned_date','converted_client_id','created_by'];
    public function getDataTable($search='', $start=0, $length=10, $status='') {
        $b = $this->db->table('leads')->select('leads.*, users.name as created_by_name')
            ->join('users','users.id = leads.created_by','left')->where('leads.deleted_at IS NULL');
        if ($search) $b->groupStart()->like('leads.name',$search)->orLike('leads.mobile',$search)->orLike('leads.email',$search)->orLike('leads.company_name',$search)->groupEnd();
        if ($status) $b->where('leads.status',$status);
        $total = (clone $b)->countAllResults();
        $data = $b->orderBy('leads.created_at','DESC')->limit($length,$start)->get()->getResultArray();
        return compact('total','data') + ['filtered'=>$total];
    }
    public function getTodaysFollowUps() {
        return $this->where('follow_up_date',date('Y-m-d'))->whereNotIn('status',['converted','lost'])->findAll();
    }
    public function getRecent($limit=5) { return $this->orderBy('created_at','DESC')->limit($limit)->findAll(); }
    public function getConversionChart() {
        $rows = $this->db->table('leads')->select('status, COUNT(*) as count')->where('deleted_at IS NULL')->groupBy('status')->get()->getResultArray();
        return array_column($rows,'count','status');
    }
    public function search($term) {
        return $this->like('name',$term)->orLike('mobile',$term)->orLike('email',$term)->select('id,name,mobile,email')->limit(10)->findAll();
    }
}
