<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\ProjectModel;
use App\Models\ClientModel;
use App\Services\PDFService;

class PaymentController extends BaseController
{
    protected $pm;
    public function __construct() { $this->pm = new PaymentModel(); }

    public function index() { return view('admin/payments/index', ['title'=>'Payments']); }

    public function datatable() {
        $result = $this->pm->getDataTable($this->request->getGet('search')['value']??'',$this->request->getGet('start')??0,$this->request->getGet('length')??10);
        return $this->response->setJSON(['draw'=>intval($this->request->getGet('draw')),'recordsTotal'=>$result['total'],'recordsFiltered'=>$result['total'],'data'=>$result['data']]);
    }

    public function create() {
        return view('admin/payments/create', ['title'=>'Record Payment','clients'=>(new ClientModel())->findAll()]);
    }

    public function store() {
        if (!$this->validate(['client_id'=>'required|integer','amount'=>'required|decimal|greater_than[0]','method'=>'required','payment_date'=>'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        $payNo = sprintf('PAY/%s/%05d', date('Y'), $this->pm->countAll()+1);
        $data  = ['payment_number'=>$payNo,'client_id'=>$this->request->getPost('client_id'),'project_id'=>$this->request->getPost('project_id')?:null,'invoice_id'=>$this->request->getPost('invoice_id')?:null,'milestone_id'=>$this->request->getPost('milestone_id')?:null,'amount'=>$this->request->getPost('amount'),'method'=>$this->request->getPost('method'),'transaction_id'=>$this->request->getPost('transaction_id'),'payment_date'=>$this->request->getPost('payment_date'),'notes'=>$this->request->getPost('notes'),'status'=>'completed','created_by'=>session()->get('user_id')];
        $pid = $this->pm->insert($data);
        if ($data['invoice_id']) {
            $im = new InvoiceModel();
            $inv = $im->find($data['invoice_id']);
            $newPaid = $inv['paid_amount'] + $data['amount'];
            $im->update($data['invoice_id'],['paid_amount'=>$newPaid,'status'=>($newPaid>=$inv['total'])?'paid':'partial','paid_at'=>($newPaid>=$inv['total'])?date('Y-m-d H:i:s'):null]);
        }
        if ($data['project_id']) {
            $prm = new ProjectModel();
            $pr = $prm->find($data['project_id']);
            $prm->update($data['project_id'],['total_paid'=>$pr['total_paid']+$data['amount']]);
        }
        $this->logActivity('payments',$pid,'created','Payment: ₹'.$data['amount']);
        return redirect()->to("admin/payments/$pid")->with('success','Payment recorded: '.$payNo);
    }

    public function show($id) {
        $p = $this->db->table('payments')->select('payments.*, clients.name as client_name, projects.name as project_name, invoices.invoice_number')->join('clients','clients.id = payments.client_id','left')->join('projects','projects.id = payments.project_id','left')->join('invoices','invoices.id = payments.invoice_id','left')->where('payments.id',$id)->get()->getRowArray();
        return view('admin/payments/show', ['title'=>'Payment','payment'=>$p]);
    }

    public function receipt($id) {
        $p = $this->db->table('payments')->select('payments.*, clients.name as client_name, clients.address as client_address, clients.email as client_email, projects.name as project_name')->join('clients','clients.id = payments.client_id','left')->join('projects','projects.id = payments.project_id','left')->where('payments.id',$id)->get()->getRowArray();
        return (new PDFService())->generateReceipt($p, $this->settings);
    }
}
