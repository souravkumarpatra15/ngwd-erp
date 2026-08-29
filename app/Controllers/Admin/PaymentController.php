<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\ProjectModel;
use App\Models\ClientModel;

class PaymentController extends BaseController
{
    protected $pm;
    public function __construct(){ $this->pm=new PaymentModel(); }
    public function index(){ if($r=$this->requireModule('payments','view'))return $r; return view('admin/payments/index',['title'=>'Payments']); }
    public function datatable(){ if($r=$this->requireModule('payments','view'))return $r;
        $search=$this->request->getGet('search');
        $result=$this->pm->getDataTable(is_array($search)?trim((string)($search['value']??'')):'',max(0,(int)$this->request->getGet('start')),(int)$this->request->getGet('length'));
        return $this->response->setJSON(['draw'=>max(0,(int)$this->request->getGet('draw')),'recordsTotal'=>(int)$result['total'],'recordsFiltered'=>(int)$result['filtered'],'data'=>$result['data']]);
    }
    public function create(){ return view('admin/payments/create',['title'=>'Record Payment','clients'=>(new ClientModel())->orderBy('name')->findAll()]); }
    public function store(){
        if(!$this->validate(['client_id'=>'required|integer','amount'=>'required|decimal|greater_than[0]','currency'=>'required|exact_length[3]|alpha','method'=>'required|in_list[razorpay,upi,bank_transfer,cash,cheque]','payment_date'=>'required|valid_date'])) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $invoiceId=$this->request->getPost('invoice_id')?:null; $projectId=$this->request->getPost('project_id')?:null; $milestoneId=$this->request->getPost('milestone_id')?:null; $transactionId=trim((string)($this->request->getPost('transaction_id')?:'')); $amount=(float)$this->request->getPost('amount'); $clientId=(int)$this->request->getPost('client_id'); $currency=strtoupper(trim((string)$this->request->getPost('currency')));
        $this->db->transBegin();
        try{
            if(!(new ClientModel())->find($clientId)) throw new \RuntimeException('Client not found.');
            if($transactionId && $this->db->table('payments')->where('transaction_id',$transactionId)->countAllResults()>0) throw new \InvalidArgumentException('A payment with this transaction ID already exists.');
            $invoice=null;
            if($invoiceId){ $invoice=$this->db->query('SELECT * FROM invoices WHERE id = ? FOR UPDATE',[$invoiceId])->getRowArray(); if(!$invoice)throw new \RuntimeException('Linked invoice not found.'); if((int)$invoice['client_id']!==$clientId)throw new \RuntimeException('Invoice does not belong to the selected client.'); if(strtoupper((string)($invoice['currency']??'INR'))!==$currency)throw new \InvalidArgumentException('Payment currency must match the linked invoice currency.'); if($projectId&&(int)($invoice['project_id']??0)!==(int)$projectId)throw new \RuntimeException('Invoice does not belong to the selected project.'); if($milestoneId&&(int)($invoice['milestone_id']??0)!==(int)$milestoneId)throw new \RuntimeException('Invoice does not belong to the selected milestone.'); }
            $project=null;
            if($projectId){ $project=$this->db->query('SELECT * FROM projects WHERE id = ? FOR UPDATE',[$projectId])->getRowArray(); if(!$project)throw new \RuntimeException('Linked project not found.'); if((int)$project['client_id']!==$clientId)throw new \RuntimeException('Project does not belong to the selected client.'); }
            if($milestoneId){ $milestone=$this->db->query('SELECT milestones.*, projects.client_id AS project_client_id FROM milestones LEFT JOIN projects ON projects.id=milestones.project_id WHERE milestones.id=? FOR UPDATE',[$milestoneId])->getRowArray(); if(!$milestone)throw new \RuntimeException('Linked milestone not found.'); if((int)$milestone['project_client_id']!==$clientId)throw new \RuntimeException('Milestone does not belong to the selected client.'); if(strtoupper((string)($milestone['currency']??'INR'))!==$currency)throw new \InvalidArgumentException('Payment currency must match the linked milestone currency.'); if($projectId&&(int)$milestone['project_id']!==(int)$projectId)throw new \RuntimeException('Milestone does not belong to the selected project.'); }
            if($invoice){ $outstanding=max(0,(float)$invoice['total']-(float)$invoice['paid_amount']); if($outstanding<=0)throw new \InvalidArgumentException('The linked invoice is already fully paid.'); if($amount>$outstanding)throw new \InvalidArgumentException('Payment amount cannot exceed the invoice balance of '.number_format($outstanding,2).'.'); }
            $payNo=sprintf('PAY/%s/%s',date('Y'),strtoupper(bin2hex(random_bytes(4))));
            $pid=$this->pm->insert(['payment_number'=>$payNo,'client_id'=>$clientId,'project_id'=>$projectId,'invoice_id'=>$invoiceId,'milestone_id'=>$milestoneId,'amount'=>$amount,'currency'=>$currency,'method'=>$this->request->getPost('method'),'transaction_id'=>$transactionId?:null,'payment_date'=>$this->request->getPost('payment_date'),'notes'=>$this->request->getPost('notes')?:null,'status'=>'completed','created_by'=>session()->get('user_id')]);
            if($pid===false)throw new \RuntimeException('Unable to create payment record.');
            if($invoiceId){ $newPaid=(float)$invoice['paid_amount']+$amount; $newBalance=max(0,(float)$invoice['total']-$newPaid); $newStatus=$newBalance<=0?'paid':'partial'; $updateData=['paid_amount'=>$newPaid,'balance_due'=>$newBalance,'status'=>$newStatus]; if($newStatus==='paid')$updateData['paid_at']=date('Y-m-d H:i:s'); if(!(new InvoiceModel())->update($invoiceId,$updateData))throw new \RuntimeException('Unable to update linked invoice.'); }
            if($projectId&&!(new ProjectModel())->update($projectId,['total_paid'=>(float)$project['total_paid']+$amount]))throw new \RuntimeException('Unable to update linked project.');
            if($milestoneId&&!$this->db->table('milestones')->where('id',$milestoneId)->update(['status'=>'paid']))throw new \RuntimeException('Unable to update linked milestone.');
            if(!$this->db->transStatus())throw new \RuntimeException('Payment transaction failed.'); $this->db->transCommit();
        }catch(\InvalidArgumentException $e){$this->db->transRollback();return redirect()->back()->withInput()->with('error',$e->getMessage());}
        catch(\Throwable $e){$this->db->transRollback();log_message('error','Payment transaction failed: {message}',['message'=>$e->getMessage()]);return redirect()->back()->withInput()->with('error','Payment could not be recorded. No financial changes were saved.');}
        return redirect()->to('/admin/payments')->with('success','Payment recorded successfully.');
    }
}