<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\DomainModel;
use App\Models\ClientModel;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class DomainController extends BaseController
{
    protected $dm;
    public function __construct() { $this->dm = new DomainModel(); }
    public function index() { return view('admin/domains/index',['title'=>'Domains','domains'=>$this->dm->getAllWithClient(),'expiring_soon'=>$this->dm->getExpiringCount(30),'expired'=>$this->dm->where('status','expired')->countAllResults()]); }
    public function create() { return view('admin/domains/create',['title'=>'Add Domain','clients'=>(new ClientModel())->findAll()]); }
    public function store() {
        $data=array_merge($this->request->getPost(),['created_by'=>session()->get('user_id')]);unset($data['csrf_test_name']);
        $days=(strtotime($data['expiry_date'])-time())/86400;$data['status']=$days<=0?'expired':($days<=30?'expiring_soon':'active');
        $this->dm->insert($data);return redirect()->to('admin/domains')->with('success','Domain added!');
    }
    public function edit($id) { return view('admin/domains/edit',['title'=>'Edit Domain','domain'=>$this->dm->find($id),'clients'=>(new ClientModel())->findAll()]); }
    public function update($id) {
        $data=$this->request->getPost();unset($data['csrf_test_name']);
        $days=(strtotime($data['expiry_date'])-time())/86400;$data['status']=$days<=0?'expired':($days<=30?'expiring_soon':'active');
        $this->dm->update($id,$data);return redirect()->to('admin/domains')->with('success','Updated!');
    }
    public function delete($id) { $this->dm->delete($id);return $this->jsonSuccess('Deleted'); }
    public function sendReminder($id) {
        $d=$this->db->table('domains')->select('domains.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = domains.client_id')->where('domains.id',$id)->get()->getRowArray();
        (new EmailService())->sendRenewalReminder($d,'domain');
        $days=ceil((strtotime($d['expiry_date'])-time())/86400);
        $msg="⚠️ Domain Renewal\n\nDear {$d['client_name']},\nDomain *{$d['domain_name']}* expires in *{$days} days*.\nRenewal: ₹{$d['renewal_cost']}\n\nContact us.\n".($this->settings['company_name']??'');
        (new WhatsAppService())->sendMessage($d['client_whatsapp'],$msg);
        $this->dm->update($id,['last_reminder_sent'=>date('Y-m-d H:i:s')]);
        return $this->jsonSuccess('Reminders sent!');
    }
}
