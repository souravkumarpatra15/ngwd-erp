<?php
namespace App\Controllers\Client;
use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\ProposalModel;
use App\Models\AgreementModel;
use App\Models\DocumentModel;
use App\Models\MilestoneModel;

class PortalController extends BaseController
{
    protected function cid(): int { return (int) session()->get('client_id'); }

    public function dashboard() {
        $cid = $this->cid();
        return view('client/dashboard/index', [
            'title'            => 'My Dashboard',
            'projects'         => (new ProjectModel())->where('client_id',$cid)->findAll(),
            'pending_invoices' => (new InvoiceModel())->where('client_id',$cid)->whereNotIn('status',['paid','cancelled'])->findAll(),
            'recent_payments'  => (new PaymentModel())->where('client_id',$cid)->orderBy('created_at','DESC')->limit(5)->findAll(),
            'total_projects'   => (new ProjectModel())->where('client_id',$cid)->countAllResults(),
            'total_paid'       => $this->db->table('payments')->where('client_id',$cid)->where('status','completed')->selectSum('amount')->get()->getRowArray()['amount'] ?? 0,
        ]);
    }

    public function projects() {
        return view('client/projects/index', ['title'=>'My Projects','projects'=>(new ProjectModel())->where('client_id',$this->cid())->where('deleted_at IS NULL')->orderBy('created_at','DESC')->findAll()]);
    }

    public function projectDetail($id) {
        $project = (new ProjectModel())->getWithClient($id);
        if (!$project || $project['client_id'] != $this->cid()) return redirect()->to('portal/projects');
        return view('client/projects/detail', [
            'title'      => $project['name'],
            'project'    => $project,
            'milestones' => (new MilestoneModel())->where('project_id',$id)->orderBy('sort_order')->findAll(),
        ]);
    }

    public function invoices() {
        $inv = $this->db->table('invoices')->select('invoices.*, projects.name as project_name')->join('projects','projects.id = invoices.project_id','left')->where('invoices.client_id',$this->cid())->orderBy('invoices.created_at','DESC')->get()->getResultArray();
        return view('client/invoices/index', ['title'=>'Invoices','invoices'=>$inv]);
    }

    public function invoiceDetail($id) {
        $inv = $this->db->table('invoices')->select('invoices.*, projects.name as project_name, clients.name as client_name, clients.address as client_address, clients.gst_number as client_gst')->join('projects','projects.id = invoices.project_id','left')->join('clients','clients.id = invoices.client_id','left')->where('invoices.id',$id)->where('invoices.client_id',$this->cid())->get()->getRowArray();
        if (!$inv) return redirect()->to('portal/invoices');
        $items = $this->db->table('invoice_items')->where('invoice_id',$id)->orderBy('sort_order')->get()->getResultArray();
        return view('client/invoices/detail', ['title'=>'Invoice '.$inv['invoice_number'],'invoice'=>$inv,'items'=>$items]);
    }

    public function payments() {
        $pays = $this->db->table('payments')->select('payments.*, projects.name as project_name, invoices.invoice_number')->join('projects','projects.id = payments.project_id','left')->join('invoices','invoices.id = payments.invoice_id','left')->where('payments.client_id',$this->cid())->where('payments.status','completed')->orderBy('payments.created_at','DESC')->get()->getResultArray();
        return view('client/payments/index', ['title'=>'Payment History','payments'=>$pays]);
    }

    public function proposals() {
        $props = (new ProposalModel())->where('client_id',$this->cid())->whereIn('status',['sent','accepted','rejected'])->findAll();
        return view('client/proposals/index', ['title'=>'Proposals','proposals'=>$props]);
    }

    public function proposalDetail($id) {
        $p = (new ProposalModel())->where('id',$id)->where('client_id',$this->cid())->first();
        if (!$p) return redirect()->to('portal/proposals');
        return view('client/proposals/detail', ['title'=>'Proposal','proposal'=>$p]);
    }

    public function agreements() {
        $ags = $this->db->table('agreements')->where('client_id',$this->cid())->whereIn('status',['sent','signed','rejected'])->get()->getResultArray();
        return view('client/agreements/index', ['title'=>'Agreements','agreements'=>$ags]);
    }

    public function signAgreement($id) {
        $ag = $this->db->table('agreements')->where('id',$id)->where('client_id',$this->cid())->where('status','sent')->get()->getRowArray();
        if (!$ag) return redirect()->to('portal/agreements');
        return view('client/agreements/sign', ['title'=>'Sign Agreement','agreement'=>$ag]);
    }

    public function processSign($id) {
        $ag = $this->db->table('agreements')->where('id',$id)->where('client_id',$this->cid())->get()->getRowArray();
        if (!$ag) return redirect()->to('portal/agreements');
        $action = $this->request->getPost('action');
        if ($action === 'sign') {
            $this->db->table('agreements')->where('id',$id)->update(['status'=>'signed','signed_at'=>date('Y-m-d H:i:s'),'signature_ip'=>$this->request->getIPAddress()]);
            return redirect()->to('portal/agreements')->with('success','Agreement signed successfully!');
        }
        $this->db->table('agreements')->where('id',$id)->update(['status'=>'rejected']);
        return redirect()->to('portal/agreements')->with('info','Agreement rejected.');
    }

    public function documents() {
        $docs = $this->db->table('documents')->where('client_id',$this->cid())->orderBy('created_at','DESC')->get()->getResultArray();
        return view('client/documents/index', ['title'=>'Documents','documents'=>$docs]);
    }
}
