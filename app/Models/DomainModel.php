<?php
namespace App\Models;
use CodeIgniter\Model;
class DomainModel extends Model {
    protected $table = 'domains';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['client_id','project_id','domain_name','registrar','registration_date','expiry_date','cost','renewal_cost','auto_renew','notes','status','last_reminder_sent','created_by'];
    public function getAllWithClient() {
        return $this->db->table('domains')->select('domains.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = domains.client_id','left')->orderBy('domains.expiry_date','ASC')->get()->getResultArray();
    }
    public function getExpiringCount($days) {
        return $this->where('expiry_date <=',date('Y-m-d',strtotime("+{$days} days")))->where('status !=','expired')->countAllResults();
    }
    public function getUpcomingRenewals($days) {
        return $this->db->table('domains')->select('domains.*, clients.name as client_name')->join('clients','clients.id = domains.client_id','left')->where('expiry_date <=',date('Y-m-d',strtotime("+{$days} days")))->where('status !=','expired')->orderBy('expiry_date','ASC')->get()->getResultArray();
    }
}
