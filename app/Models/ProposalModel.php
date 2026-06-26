<?php
namespace App\Models;
use CodeIgniter\Model;
class ProposalModel extends Model {
    protected $table = 'proposals';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['proposal_number','client_id','project_id','lead_id','title','introduction','project_overview','scope_of_work','deliverables','timeline','pricing','terms','total_amount','valid_until','status','sent_at','accepted_at','notes','created_by'];
    public function getWithDetails($id) {
        return $this->db->table('proposals')->select('proposals.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')
            ->join('clients','clients.id = proposals.client_id','left')->where('proposals.id',$id)->get()->getRowArray();
    }
}
