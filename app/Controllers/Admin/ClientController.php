<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\ProjectModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\DomainModel;
use App\Models\HostingModel;
use App\Models\UserModel;

class ClientController extends BaseController
{
    protected $clientModel;
    public function __construct() { $this->clientModel = new ClientModel(); }

    public function index() { return view('admin/clients/index', ['title'=>'Clients']); }

    public function datatable() {
        $search = $this->request->getGet('search')['value'] ?? '';
        $start  = $this->request->getGet('start') ?? 0;
        $length = $this->request->getGet('length') ?? 10;
        $b = $this->db->table('clients')->where('deleted_at IS NULL');
        if ($search) $b->groupStart()->like('name',$search)->orLike('email',$search)->orLike('phone',$search)->orLike('company_name',$search)->groupEnd();
        $total = (clone $b)->countAllResults();
        $data  = $b->orderBy('created_at','DESC')->limit($length,$start)->get()->getResultArray();
        return $this->response->setJSON(['draw'=>intval($this->request->getGet('draw')),'recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$data]);
    }

    public function create() { return view('admin/clients/create', ['title'=>'Add Client']); }

    public function store() {
        if (!$this->validate(['name'=>'required|min_length[2]','phone'=>'required|min_length[10]'])) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        $data = array_merge($this->request->getPost(), ['client_number'=>$this->generateNumber('CLT',$this->clientModel),'created_by'=>session()->get('user_id')]);
        unset($data['csrf_test_name']);
        $clientId = $this->clientModel->insert($data);
        if (!empty($data['email'])) {
            $um = new UserModel();
            if (!$um->where('email',$data['email'])->first()) {
                $um->insert(['name'=>$data['name'],'email'=>$data['email'],'password'=>password_hash('Client@'.rand(1000,9999),PASSWORD_BCRYPT),'role'=>'client','client_id'=>$clientId,'is_active'=>1]);
            }
        }
        $this->logActivity('clients', $clientId, 'created', 'Client: '.$data['name']);
        return redirect()->to("admin/clients/$clientId")->with('success','Client created!');
    }

    public function show($id) {
        $client = $this->clientModel->find($id);
        if (!$client) return redirect()->to('admin/clients');
        return view('admin/clients/show', [
            'title'        => $client['name'],
            'client'       => $client,
            'projects'     => (new ProjectModel())->where('client_id',$id)->findAll(),
            'invoices'     => (new InvoiceModel())->where('client_id',$id)->orderBy('created_at','DESC')->limit(10)->findAll(),
            'payments'     => (new PaymentModel())->where('client_id',$id)->orderBy('created_at','DESC')->limit(10)->findAll(),
            'domains'      => (new DomainModel())->where('client_id',$id)->findAll(),
            'hostings'     => (new HostingModel())->where('client_id',$id)->findAll(),
            'total_billed' => (new InvoiceModel())->where('client_id',$id)->selectSum('total')->get()->getRowArray()['total'] ?? 0,
            'total_paid'   => (new PaymentModel())->where('client_id',$id)->where('status','completed')->selectSum('amount')->get()->getRowArray()['amount'] ?? 0,
        ]);
    }

    public function edit($id) { return view('admin/clients/edit', ['title'=>'Edit Client','client'=>$this->clientModel->find($id)]); }
    public function update($id) { $data=$this->request->getPost();unset($data['csrf_test_name']);$this->clientModel->update($id,$data);return redirect()->to("admin/clients/$id")->with('success','Client updated!'); }
    public function delete($id) { $this->clientModel->delete($id);return $this->jsonSuccess('Client deleted'); }
}
