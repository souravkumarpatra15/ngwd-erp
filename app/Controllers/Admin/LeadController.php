<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\ClientModel;
use App\Models\LeadActivityModel;
use App\Services\EmailService;
use App\Services\WhatsAppService;

class LeadController extends BaseController
{
    protected $leadModel;
    public function __construct() { $this->leadModel = new LeadModel(); }

    public function index() { return view('admin/leads/index', ['title' => 'Leads']); }

    public function datatable() {
        $result = $this->leadModel->getDataTable(
            $this->request->getGet('search')['value'] ?? '',
            $this->request->getGet('start') ?? 0,
            $this->request->getGet('length') ?? 10,
            $this->request->getGet('status') ?? ''
        );
        return $this->response->setJSON(['draw'=>intval($this->request->getGet('draw')),'recordsTotal'=>$result['total'],'recordsFiltered'=>$result['filtered'],'data'=>$result['data']]);
    }

    public function create() { return view('admin/leads/create', ['title' => 'Add Lead']); }

    public function store() {
        if (!$this->validate(['name'=>'required|min_length[2]','mobile'=>'required|min_length[10]','source'=>'required'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = array_merge($this->request->getPost(), ['lead_number'=>$this->generateNumber('LEAD',$this->leadModel),'status'=>'new','created_by'=>session()->get('user_id')]);
        unset($data['csrf_test_name']);
        $id = $this->leadModel->insert($data);
        $this->logActivity('leads', $id, 'created', 'Lead added: '.$data['name']);
        return redirect()->to('admin/leads')->with('success', 'Lead added successfully!');
    }

    public function show($id) {
        $lead = $this->leadModel->find($id);
        if (!$lead) return redirect()->to('admin/leads');
        return view('admin/leads/show', [
            'title'      => 'Lead: '.$lead['name'],
            'lead'       => $lead,
            'activities' => (new LeadActivityModel())->where('lead_id',$id)->orderBy('created_at','DESC')->findAll(),
        ]);
    }

    public function edit($id) {
        return view('admin/leads/edit', ['title'=>'Edit Lead','lead'=>$this->leadModel->find($id)]);
    }

    public function update($id) {
        $data = $this->request->getPost(); unset($data['csrf_test_name']);
        $this->leadModel->update($id, $data);
        return redirect()->to("admin/leads/$id")->with('success', 'Lead updated!');
    }

    public function delete($id) { $this->leadModel->delete($id); return $this->jsonSuccess('Lead deleted'); }

    public function convertToClient($id) {
        $lead = $this->leadModel->find($id);
        if (!$lead) return $this->jsonError('Lead not found');
        $clientModel = new ClientModel();
        $clientData  = ['client_number'=>$this->generateNumber('CLT',$clientModel),'name'=>$lead['name'],'company_name'=>$lead['company_name'],'phone'=>$lead['mobile'],'whatsapp'=>$lead['whatsapp'],'email'=>$lead['email'],'address'=>$lead['address'],'lead_id'=>$id,'created_by'=>session()->get('user_id')];
        $clientId = $clientModel->insert($clientData);
        $this->leadModel->update($id, ['status'=>'converted','converted_client_id'=>$clientId]);
        $this->logActivity('leads', $id, 'converted', 'Converted to client #'.$clientId);
        return $this->jsonSuccess('Lead converted!', ['client_id'=>$clientId]);
    }

    public function addActivity($id) {
        $data = $this->request->getPost();
        (new LeadActivityModel())->insert(['lead_id'=>$id,'user_id'=>session()->get('user_id'),'action'=>$data['action'],'notes'=>$data['notes']??'','follow_up_date'=>$data['follow_up_date']??null]);
        if (!empty($data['follow_up_date'])) $this->leadModel->update($id, ['follow_up_date'=>$data['follow_up_date'],'status'=>'follow_up']);
        return $this->jsonSuccess('Activity logged');
    }

    public function sendWhatsApp($id) {
        $lead = $this->leadModel->find($id);
        $result = (new WhatsAppService())->sendMessage($lead['whatsapp'] ?: $lead['mobile'], $this->request->getPost('message'));
        return $result ? $this->jsonSuccess('WhatsApp sent!') : $this->jsonError('Failed to send');
    }

    public function sendEmail($id) {
        $lead = $this->leadModel->find($id);
        $result = (new EmailService())->send($lead['email'], $this->request->getPost('subject'), $this->request->getPost('message'));
        return $result ? $this->jsonSuccess('Email sent!') : $this->jsonError('Failed to send');
    }
}
