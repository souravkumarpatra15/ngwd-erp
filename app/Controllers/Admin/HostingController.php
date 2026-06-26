<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\HostingModel;
use App\Models\ClientModel;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class HostingController extends BaseController
{
    protected $hm;
    public function __construct() { $this->hm = new HostingModel(); }
    public function index() { return view('admin/hostings/index',['title'=>'Hosting','hostings'=>$this->hm->getAllWithClient(),'expiring_soon'=>$this->hm->getExpiringCount(30),'expired'=>$this->hm->where('status','expired')->countAllResults()]); }
    public function create() { return view('admin/hostings/create',['title'=>'Add Hosting','clients'=>(new ClientModel())->findAll()]); }
    public function store() {
        $data=array_merge($this->request->getPost(),['created_by'=>session()->get('user_id')]);unset($data['csrf_test_name']);
        $days=(strtotime($data['expiry_date'])-time())/86400;$data['status']=$days<=0?'expired':($days<=30?'expiring_soon':'active');
        $this->hm->insert($data);return redirect()->to('admin/hostings')->with('success','Hosting added!');
    }
    public function edit($id) { return view('admin/hostings/edit',['title'=>'Edit Hosting','hosting'=>$this->hm->find($id),'clients'=>(new ClientModel())->findAll()]); }
    public function update($id) {
        $data=$this->request->getPost();unset($data['csrf_test_name']);
        $days=(strtotime($data['expiry_date'])-time())/86400;$data['status']=$days<=0?'expired':($days<=30?'expiring_soon':'active');
        $this->hm->update($id,$data);return redirect()->to('admin/hostings')->with('success','Updated!');
    }
    public function delete($id) { $this->hm->delete($id);return $this->jsonSuccess('Deleted'); }
    public function sendReminder($id) {
        $h=$this->db->table('hostings')->select('hostings.*, clients.name as client_name, clients.email as client_email, clients.whatsapp as client_whatsapp')->join('clients','clients.id = hostings.client_id')->where('hostings.id',$id)->get()->getRowArray();
        (new EmailService())->sendRenewalReminder($h,'hosting');
        $days=ceil((strtotime($h['expiry_date'])-time())/86400);
        $msg="⚠️ Hosting Renewal\n\nDear {$h['client_name']},\nHosting *{$h['provider']}* expires in *{$days} days*.\nRenewal: ₹{$h['renewal_cost']}\n\nContact us.\n".($this->settings['company_name']??'');
        (new WhatsAppService())->sendMessage($h['client_whatsapp'],$msg);
        $this->hm->update($id,['last_reminder_sent'=>date('Y-m-d H:i:s')]);
        return $this->jsonSuccess('Reminders sent!');
    }
}
