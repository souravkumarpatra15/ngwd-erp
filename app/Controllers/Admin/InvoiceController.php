<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\ClientModel;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use App\Services\PaymentService;

class InvoiceController extends BaseController
{
    protected $invoiceModel;
    protected $itemModel;

    public function __construct() {
        $this->invoiceModel = new InvoiceModel();
        $this->itemModel    = new InvoiceItemModel();
    }

    public function index() { return view('admin/invoices/index', ['title'=>'Invoices']); }

    public function datatable() {
        $result = $this->invoiceModel->getDataTable($this->request->getGet('search')['value']??'',$this->request->getGet('start')??0,$this->request->getGet('length')??10,$this->request->getGet('status')??'');
        return $this->response->setJSON(['draw'=>intval($this->request->getGet('draw')),'recordsTotal'=>$result['total'],'recordsFiltered'=>$result['filtered'],'data'=>$result['data']]);
    }

    public function create() {
        return view('admin/invoices/create', ['title'=>'Create Invoice','clients'=>(new ClientModel())->findAll(),'default_tax'=>$this->settings['tax_percent']??18,'default_terms'=>$this->settings['invoice_terms']??'']);
    }

    public function store() {
        $db = \Config\Database::connect();
        $db->transStart();
        $items    = $this->request->getPost('items');
        $subtotal = 0;
        foreach ($items as $item) $subtotal += ($item['quantity'] * $item['unit_price']);
        $taxPct  = (float)($this->request->getPost('tax_percent') ?? 0);
        $taxAmt  = $subtotal * ($taxPct / 100);
        $discount = (float)($this->request->getPost('discount') ?? 0);
        $total   = $subtotal + $taxAmt - $discount;
        $invoiceNo = sprintf('%s/%s/%05d', $this->settings['invoice_prefix']??'NGWD', date('Y'), $this->invoiceModel->countAll()+1);
        $invoiceId = $this->invoiceModel->insert(['invoice_number'=>$invoiceNo,'client_id'=>$this->request->getPost('client_id'),'project_id'=>$this->request->getPost('project_id')?:null,'milestone_id'=>$this->request->getPost('milestone_id')?:null,'invoice_date'=>$this->request->getPost('invoice_date'),'due_date'=>$this->request->getPost('due_date'),'subtotal'=>$subtotal,'tax_percent'=>$taxPct,'tax_amount'=>$taxAmt,'discount'=>$discount,'total'=>$total,'is_gst'=>$this->request->getPost('is_gst')?1:0,'notes'=>$this->request->getPost('notes'),'terms'=>$this->request->getPost('terms'),'status'=>'draft','created_by'=>session()->get('user_id')]);
        foreach ($items as $order => $item) {
            $this->itemModel->insert(['invoice_id'=>$invoiceId,'description'=>$item['description'],'quantity'=>$item['quantity'],'unit_price'=>$item['unit_price'],'total'=>$item['quantity']*$item['unit_price'],'sort_order'=>$order]);
        }
        $db->transComplete();
        $this->logActivity('invoices', $invoiceId, 'created', 'Invoice '.$invoiceNo);
        return redirect()->to("admin/invoices/$invoiceId")->with('success', 'Invoice created: '.$invoiceNo);
    }

    public function show($id) {
        return view('admin/invoices/show', ['title'=>'Invoice','invoice'=>$this->invoiceModel->getWithDetails($id),'items'=>$this->itemModel->where('invoice_id',$id)->orderBy('sort_order')->findAll()]);
    }

    public function edit($id) {
        return view('admin/invoices/edit', ['title'=>'Edit Invoice','invoice'=>$this->invoiceModel->getWithDetails($id),'items'=>$this->itemModel->where('invoice_id',$id)->orderBy('sort_order')->findAll(),'clients'=>(new ClientModel())->findAll()]);
    }

    public function update($id) {
        $data = $this->request->getPost(); unset($data['csrf_test_name'],$data['items']);
        $this->invoiceModel->update($id, $data);
        return redirect()->to("admin/invoices/$id")->with('success','Invoice updated!');
    }

    public function generatePDF($id) {
        $invoice = $this->invoiceModel->getWithDetails($id);
        $items   = $this->itemModel->where('invoice_id',$id)->orderBy('sort_order')->findAll();
        return (new PDFService())->generateInvoice($invoice, $items, $this->settings);
    }

    public function sendEmail($id) {
        $invoice = $this->invoiceModel->getWithDetails($id);
        $items   = $this->itemModel->where('invoice_id',$id)->findAll();
        $pdf     = (new PDFService())->generateInvoicePDF($invoice, $items, $this->settings);
        $result  = (new EmailService())->sendInvoice($invoice, $pdf);
        if ($result) { $this->invoiceModel->update($id,['status'=>'sent','sent_at'=>date('Y-m-d H:i:s')]); return $this->jsonSuccess('Invoice emailed!'); }
        return $this->jsonError('Failed to send email');
    }

    public function sendWhatsApp($id) {
        $invoice = $this->invoiceModel->getWithDetails($id);
        $msg = "Dear {$invoice['client_name']},\n\nInvoice *{$invoice['invoice_number']}* for ₹".number_format($invoice['total'],2)." is ready.\nDue: {$invoice['due_date']}\nDownload: ".base_url("admin/invoices/pdf/$id")."\n\nThank you!\n".($this->settings['company_name']??'');
        $result = (new WhatsAppService())->sendMessage($invoice['client_whatsapp'], $msg);
        if ($result) { $this->invoiceModel->update($id,['status'=>'sent','sent_at'=>date('Y-m-d H:i:s')]); return $this->jsonSuccess('WhatsApp sent!'); }
        return $this->jsonError('Failed to send');
    }

    public function generatePaymentLink($id) {
        $invoice = $this->invoiceModel->getWithDetails($id);
        $order = (new PaymentService())->createOrder($invoice['balance_due'], 'invoice', $id, $invoice['client_id']);
        return $order ? $this->jsonSuccess('Link generated',['url'=>base_url("portal/pay/$id"),'order'=>$order]) : $this->jsonError('Failed to create link');
    }
}
