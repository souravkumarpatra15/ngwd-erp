<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\ClientModel;
use App\Models\ProjectModel;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use App\Services\PaymentService;

/**
 * InvoiceController — adds missing delete(), void(), and cancel()
 */
class InvoiceController extends BaseController
{
    protected InvoiceModel $im;

    public function __construct()
    {
        $this->im = new InvoiceModel();
    }

    public function index()
    {
        return view('admin/invoices/index', ['title' => 'Invoices']);
    }

    public function datatable()
    {
        $draw   = $this->request->getGet('draw');
        $start  = (int) $this->request->getGet('start');
        $length = (int) $this->request->getGet('length');
        $search = $this->request->getGet('search')['value'] ?? '';
        $status = $this->request->getGet('status') ?? '';
        $result = $this->im->getDataTable($search, $start, $length, $status);
        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $result['data'],
        ]);
    }

    public function create()
    {
        return view('admin/invoices/create', [
            'title'        => 'Create Invoice',
            'clients'      => (new ClientModel())->findAll(),
            'tax_percent'  => $this->settings['tax_percent'] ?? 18,
            'default_terms'=> $this->settings['invoice_terms'] ?? '',
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();
        $invoiceData = [
            'invoice_number' => $this->generateNumber($this->settings['invoice_prefix'] ?? 'INV', $this->im),
            'client_id'      => $post['client_id'],
            'project_id'     => $post['project_id'] ?: null,
            'milestone_id'   => $post['milestone_id'] ?: null,
            'invoice_date'   => $post['invoice_date'],
            'due_date'       => $post['due_date'],
            'subtotal'       => $post['subtotal'],
            'tax_percent'    => $post['tax_percent'],
            'tax_amount'     => $post['tax_amount'],
            'discount'       => $post['discount'] ?? 0,
            'total'          => $post['total'],
            'paid_amount'    => 0,
            'is_gst'         => $post['is_gst'] ?? 0,
            'notes'          => $post['notes'] ?? '',
            'terms'          => $post['terms'] ?? '',
            'status'         => 'draft',
            'created_by'     => session()->get('user_id'),
        ];
        $id = $this->im->insert($invoiceData);

        // Line items
        $iim = new InvoiceItemModel();
        foreach (($post['items'] ?? []) as $item) {
            $iim->insert([
                'invoice_id'  => $id,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'rate'        => $item['rate'],
                'amount'      => $item['amount'],
            ]);
        }
        $this->logActivity('invoices', $id, 'create', "Created invoice {$invoiceData['invoice_number']}");
        return redirect()->to("admin/invoices/$id")->with('success', 'Invoice created!');
    }

    public function show($id)
    {
        $invoice = $this->im->getWithDetails($id);
        $items   = (new InvoiceItemModel())->where('invoice_id', $id)->findAll();
        return view('admin/invoices/show', ['title' => 'Invoice', 'invoice' => $invoice, 'items' => $items]);
    }

    public function edit($id)
    {
        return view('admin/invoices/edit', [
            'title'    => 'Edit Invoice',
            'invoice'  => $this->im->find($id),
            'items'    => (new InvoiceItemModel())->where('invoice_id', $id)->findAll(),
            'clients'  => (new ClientModel())->findAll(),
        ]);
    }

    public function update($id)
    {
        $post = $this->request->getPost();
        unset($post['csrf_test_name']);
        $this->im->update($id, $post);

        // Rebuild line items
        $iim = new InvoiceItemModel();
        $iim->where('invoice_id', $id)->delete();
        foreach (($post['items'] ?? []) as $item) {
            $iim->insert(array_merge($item, ['invoice_id' => $id]));
        }
        $this->logActivity('invoices', $id, 'update', 'Updated invoice');
        return redirect()->to("admin/invoices/$id")->with('success', 'Updated!');
    }

    // ── NEW: delete invoice ────────────────────────────────────
    public function delete($id)
    {
        $inv = $this->im->find($id);
        if (!$inv) {
            return $this->jsonError('Invoice not found.');
        }
        if (in_array($inv['status'], ['paid', 'partial'])) {
            return $this->jsonError('Cannot delete a paid or partially paid invoice. Void it instead.');
        }
        (new InvoiceItemModel())->where('invoice_id', $id)->delete();
        $this->im->delete($id);
        $this->logActivity('invoices', $id, 'delete', "Deleted invoice {$inv['invoice_number']}");
        return $this->jsonSuccess('Invoice deleted.');
    }

    // ── NEW: void invoice ──────────────────────────────────────
    public function void($id)
    {
        $inv = $this->im->find($id);
        if (!$inv) {
            return $this->jsonError('Invoice not found.');
        }
        if ($inv['status'] === 'paid') {
            return $this->jsonError('Cannot void a fully paid invoice. Process a refund through payments.');
        }
        $this->im->update($id, ['status' => 'cancelled']);
        $this->logActivity('invoices', $id, 'void', "Voided invoice {$inv['invoice_number']}");
        return $this->jsonSuccess('Invoice voided.');
    }

    public function generatePDF($id)
    {
        $invoice = $this->im->getWithDetails($id);
        $items   = (new InvoiceItemModel())->where('invoice_id', $id)->findAll();
        return (new PDFService())->generateInvoice($invoice, $items, $this->settings);
    }

    public function sendEmail($id)
    {
        $invoice = $this->im->getWithDetails($id);
        $items   = (new InvoiceItemModel())->where('invoice_id', $id)->findAll();
        $path    = (new PDFService())->generateInvoicePDF($invoice, $items, $this->settings);
        $res     = (new EmailService())->sendInvoice($invoice, $path);
        if ($res) {
            $this->im->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            return $this->jsonSuccess('Invoice emailed!');
        }
        return $this->jsonError('Failed to send email.');
    }

    public function sendWhatsApp($id)
    {
        $inv = $this->im->getWithDetails($id);
        $msg = "Invoice *{$inv['invoice_number']}*\nAmount: ₹" . number_format($inv['total'], 2) . "\nDue: {$inv['due_date']}\n\n" . ($this->settings['company_name'] ?? '');
        $res = (new WhatsAppService())->sendMessage($inv['client_whatsapp'], $msg);
        if ($res) {
            $this->im->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            return $this->jsonSuccess('WhatsApp sent!');
        }
        return $this->jsonError('Failed.');
    }

    public function generatePaymentLink($id)
    {
        $inv   = $this->im->getWithDetails($id);
        $order = (new PaymentService())->createOrder(
            (float) $inv['total'] - (float) $inv['paid_amount'],
            'invoice', $id, $inv['client_id']
        );
        if (!$order) {
            return $this->jsonError('Could not create Razorpay order.');
        }
        return $this->jsonSuccess('Order created', [
            'order_id'    => $order['id'],
            'amount'      => $order['amount'],
            'razorpay_key'=> $this->settings['razorpay_key'] ?? '',
        ]);
    }
}
