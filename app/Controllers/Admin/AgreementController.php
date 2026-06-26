<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\AgreementModel;
use App\Models\ClientModel;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class AgreementController extends BaseController
{
    protected $am;
    public function __construct() { $this->am = new AgreementModel(); }
    public function index() { return view('admin/agreements/index',['title'=>'Agreements']); }
    public function create() { return view('admin/agreements/create',['title'=>'New Agreement','clients'=>(new ClientModel())->findAll(),'default_terms'=>$this->settings['agreement_terms']??'']); }
    public function store() {
        $data=array_merge($this->request->getPost(),['agreement_number'=>$this->generateNumber('AGR',$this->am),'created_by'=>session()->get('user_id'),'status'=>'draft']);
        unset($data['csrf_test_name']);$id=$this->am->insert($data);
        return redirect()->to("admin/agreements/$id")->with('success','Agreement created!');
    }
    public function show($id) { return view('admin/agreements/show',['title'=>'Agreement','agreement'=>$this->am->getWithDetails($id)]); }
    public function edit($id) { return view('admin/agreements/edit',['title'=>'Edit Agreement','agreement'=>$this->am->find($id),'clients'=>(new ClientModel())->findAll()]); }
    public function update($id) { $data=$this->request->getPost();unset($data['csrf_test_name']);$this->am->update($id,$data);return redirect()->to("admin/agreements/$id")->with('success','Updated!'); }
    public function generatePDF($id) { return (new PDFService())->generateAgreement($this->am->getWithDetails($id),$this->settings); }
    public function sendEmail($id) {
        $a=$this->am->getWithDetails($id);$path=(new PDFService())->saveAgreementPDF($a,$this->settings);
        $body="<p>Dear {$a['client_name']},</p><p>Agreement <strong>{$a['title']}</strong> is ready.</p><p>Sign here: ".base_url("portal/agreements/sign/{$id}")."</p>";
        $res=(new EmailService())->send($a['client_email'],"Agreement: {$a['title']}",$body,$path);
        if($res){$this->am->update($id,['status'=>'sent','sent_at'=>date('Y-m-d H:i:s')]);return $this->jsonSuccess('Emailed!');}
        return $this->jsonError('Failed');
    }
    public function sendWhatsApp($id) {
        $a=$this->am->getWithDetails($id);
        $msg="Dear {$a['client_name']},\n\nAgreement *{$a['title']}* is ready for your signature.\nSign here: ".base_url("portal/agreements/sign/$id")."\n\nRegards,\n".($this->settings['company_name']??'');
        $res=(new WhatsAppService())->sendMessage($a['client_whatsapp'],$msg);
        if($res){$this->am->update($id,['status'=>'sent','sent_at'=>date('Y-m-d H:i:s')]);return $this->jsonSuccess('WhatsApp sent!');}
        return $this->jsonError('Failed');
    }
}
