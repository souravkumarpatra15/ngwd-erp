<?php
namespace App\Models;
use CodeIgniter\Model;
class AgreementModel extends Model {
    protected $table = 'agreements';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['agreement_number','client_id','project_id','proposal_id','title','client_information','project_information','deliverables','timeline','payment_terms','support_terms','cancellation_terms','terms_conditions','status','sent_at','signed_at','signature_ip','created_by'];
    public function getWithDetails($id) {
        return $this->db->table('agreements')->select('agreements.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp, projects.name as project_name')
            ->join('clients','clients.id = agreements.client_id','left')
            ->join('projects','projects.id = agreements.project_id','left')
            ->where('agreements.id',$id)->get()->getRowArray();
    }
}
