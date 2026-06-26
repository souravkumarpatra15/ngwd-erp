<?php
namespace App\Models;
use CodeIgniter\Model;
class HostingModel extends Model {
    protected $table = 'hostings';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['client_id','project_id','provider','package','server_ip','server_details','username','control_panel_url','purchase_date','expiry_date','cost','renewal_cost','notes','status','last_reminder_sent','created_by'];
    public function getAllWithClient() {
        return $this->db->table('hostings')->select('hostings.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = hostings.client_id','left')->orderBy('hostings.expiry_date','ASC')->get()->getResultArray();
    }
    public function getExpiringCount($days) {
        return $this->where('expiry_date <=',date('Y-m-d',strtotime("+{$days} days")))->where('status !=','expired')->countAllResults();
    }
    public function getUpcomingRenewals($days) {
        return $this->db->table('hostings')->select('hostings.*, clients.name as client_name')->join('clients','clients.id = hostings.client_id','left')->where('expiry_date <=',date('Y-m-d',strtotime("+{$days} days")))->where('status !=','expired')->orderBy('expiry_date','ASC')->get()->getResultArray();
    }
}
