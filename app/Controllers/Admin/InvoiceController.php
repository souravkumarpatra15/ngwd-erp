<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\ClientModel;
use App\Models\ProjectModel;
use App\Models\MilestoneModel;
use App\Models\DomainModel;
use App\Models\HostingModel;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use App\Services\PaymentService;

class InvoiceController extends BaseController
{
    protected InvoiceModel $im;

    public function __construct()
    {
        $this->im = new InvoiceModel();
    }

    // ── LIST ──────────────────────────────────────────────────
    public function index()
    {
        return view('admin/invoices/index', ['title' => 'Invoices']);
    }

    public function datatable()
    {
        $result = $this->im->getDataTable(
            $this->request->getGet('search')['value'] ?? '',
            (int) $this->request->getGet('start'),
            (int) $this->request->getGet('length'),
            $this->request->getGet('status') ?? ''
        );
        return $this->response->setJSON([
            'draw'            => intval($this->request->getGet('draw')),
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $result['data'],
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────
    public function create()
    {
        return view('admin/invoices/create', [
            'title'        => 'Create Invoice',
            'clients'      => (new ClientModel())->orderBy('name')->findAll(),
            'default_tax'  => $this->settings['tax_percent'] ?? 18,
            'default_terms'=> $this->settings['invoice_terms'] ?? '',
            // Optional prefill — lets "Create Invoice" buttons on the Domain/Hosting
            // pages jump straight here with the right client/type/item pre-selected.
            'prefill' => [
                'type'        => $this->request->getGet('type') ?? '',
                'client_id'   => $this->request->getGet('client_id') ?? '',
                'domain_id'   => $this->request->getGet('domain_id') ?? '',
                'hosting_id'  => $this->request->getGet('hosting_id') ?? '',
                'milestone_id'=> $this->request->getGet('milestone_id') ?? '',
            ],
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store()
    {
        $post = $this->request->getPost();
        $subtotal = (float) ($post['subtotal'] ?? 0);
        $tax_pct  = (float) ($post['tax_percent'] ?? 0);
        $tax_amt  = round($subtotal * $tax_pct / 100, 2);
        $discount = (float) ($post['discount'] ?? 0);
        $total    = round($subtotal + $tax_amt - $discount, 2);

        $invoiceData = [
            'invoice_number' => $this->generateNumber($this->settings['invoice_prefix'] ?? 'INV', $this->im),
            'client_id'      => $post['client_id'],
            'project_id'     => !empty($post['project_id'])   ? $post['project_id']   : null,
            'milestone_id'   => !empty($post['milestone_id']) ? $post['milestone_id'] : null,
            'domain_id'      => !empty($post['domain_id'])    ? $post['domain_id']    : null,
            'hosting_id'     => !empty($post['hosting_id'])   ? $post['hosting_id']   : null,
            'invoice_date'   => $post['invoice_date'] ?? date('Y-m-d'),
            'due_date'       => $post['due_date']     ?? date('Y-m-d', strtotime('+15 days')),
            'subtotal'       => $subtotal,
            'tax_percent'    => $tax_pct,
            'tax_amount'     => $tax_amt,
            'discount'       => $discount,
            'total'          => $total,
            'paid_amount'    => 0,
            'balance_due'    => $total,          // ← always set on create
            'is_gst'         => !empty($post['is_gst']) ? 1 : 0,
            'notes'          => $post['notes']  ?? '',
            'terms'          => $post['terms']  ?? '',
            'status'         => 'draft',
            'created_by'     => session()->get('user_id'),
        ];

        $id = $this->im->insert($invoiceData);

        // Line items
        $iim = new InvoiceItemModel();
        foreach (($post['items'] ?? []) as $idx => $item) {
            $qty   = (float) ($item['quantity']  ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $iim->insert([
                'invoice_id'  => $id,
                'description' => $item['description'] ?? '',
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total'       => round($qty * $price, 2),
                'sort_order'  => $idx,
            ]);
        }

        $this->logActivity('invoices', $id, 'create', "Created {$invoiceData['invoice_number']}");
        return redirect()->to("admin/invoices/$id")->with('success', 'Invoice created!');
    }

    // ── SHOW ──────────────────────────────────────────────────
    public function show($id)
    {
        $invoice = $this->im->getWithDetails($id);
        if (!$invoice) return redirect()->to('admin/invoices');
        $items = (new InvoiceItemModel())->where('invoice_id', $id)->orderBy('sort_order')->findAll();
        return view('admin/invoices/show', [
            'title'   => 'Invoice '.$invoice['invoice_number'],
            'invoice' => $invoice,
            'items'   => $items,
            'settings'=> $this->settings,
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────────
    public function edit($id)
    {
        $invoice = $this->im->find($id);
        if (!$invoice) return redirect()->to('admin/invoices');
        $items   = (new InvoiceItemModel())->where('invoice_id', $id)->orderBy('sort_order')->findAll();
        return view('admin/invoices/edit', [
            'title'   => 'Edit Invoice',
            'invoice' => $invoice,
            'items'   => $items,
            'clients' => (new ClientModel())->orderBy('name')->findAll(),
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────────
    public function update($id)
    {
        $post     = $this->request->getPost();
        $inv      = $this->im->find($id);

        $subtotal = (float) ($post['subtotal'] ?? 0);
        $tax_pct  = (float) ($post['tax_percent'] ?? 0);
        $tax_amt  = round($subtotal * $tax_pct / 100, 2);
        $discount = (float) ($post['discount'] ?? 0);
        $total    = round($subtotal + $tax_amt - $discount, 2);
        $paidAmt  = (float) $inv['paid_amount'];

        $this->im->update($id, [
            'client_id'   => $post['client_id'],
            'project_id'  => !empty($post['project_id']) ? $post['project_id'] : null,
            'milestone_id'=> !empty($post['milestone_id']) ? $post['milestone_id'] : null,
            'domain_id'   => !empty($post['domain_id'])    ? $post['domain_id']    : null,
            'hosting_id'  => !empty($post['hosting_id'])   ? $post['hosting_id']   : null,
            'invoice_date'=> $post['invoice_date'],
            'due_date'    => $post['due_date'],
            'subtotal'    => $subtotal,
            'tax_percent' => $tax_pct,
            'tax_amount'  => $tax_amt,
            'discount'    => $discount,
            'total'       => $total,
            'balance_due' => max(0, $total - $paidAmt),  // ← recalculate
            'is_gst'      => !empty($post['is_gst']) ? 1 : 0,
            'notes'       => $post['notes'] ?? '',
            'terms'       => $post['terms'] ?? '',
            // status stays as-is unless already draft
        ]);

        // Rebuild line items
        $iim = new InvoiceItemModel();
        $iim->where('invoice_id', $id)->delete();
        foreach (($post['items'] ?? []) as $idx => $item) {
            $qty   = (float) ($item['quantity']  ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $iim->insert([
                'invoice_id'  => $id,
                'description' => $item['description'] ?? '',
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total'       => round($qty * $price, 2),
                'sort_order'  => $idx,
            ]);
        }

        $this->logActivity('invoices', $id, 'update', 'Updated invoice');
        return redirect()->to("admin/invoices/$id")->with('success', 'Invoice updated!');
    }

    // ── DELETE ────────────────────────────────────────────────
    public function delete($id)
    {
        $inv = $this->im->find($id);
        if (!$inv) return $this->jsonError('Invoice not found.');
        if (in_array($inv['status'], ['paid', 'partial'])) {
            return $this->jsonError('Cannot delete a paid invoice. Void it instead.');
        }
        (new InvoiceItemModel())->where('invoice_id', $id)->delete();
        $this->im->delete($id);
        $this->logActivity('invoices', $id, 'delete', "Deleted {$inv['invoice_number']}");
        return $this->jsonSuccess('Invoice deleted.');
    }

    // ── VOID ──────────────────────────────────────────────────
    public function void($id)
    {
        $inv = $this->im->find($id);
        if (!$inv) return $this->jsonError('Invoice not found.');
        if ($inv['status'] === 'paid') {
            return $this->jsonError('Cannot void a fully paid invoice.');
        }
        $this->im->update($id, ['status' => 'cancelled']);
        $this->logActivity('invoices', $id, 'void', "Voided {$inv['invoice_number']}");
        return $this->jsonSuccess('Invoice voided.');
    }

    // ── PDF ───────────────────────────────────────────────────
    public function generatePDF($id)
    {
        $invoice = $this->im->getWithDetails($id);
        $items   = (new InvoiceItemModel())->where('invoice_id', $id)->orderBy('sort_order')->findAll();
        return (new PDFService())->generateInvoice($invoice, $items, $this->settings);
    }

    // ── SEND EMAIL ────────────────────────────────────────────
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

    // ── SEND WHATSAPP ─────────────────────────────────────────
    public function sendWhatsApp($id)
    {
        $inv = $this->im->getWithDetails($id);
        $msg = "Invoice *{$inv['invoice_number']}*\nAmount: ₹" . number_format($inv['total'], 2)
            . "\nDue: {$inv['due_date']}\n\n" . ($this->settings['company_name'] ?? '');
        $res = (new WhatsAppService())->sendMessage($inv['client_whatsapp'], $msg);
        if ($res) {
            $this->im->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            return $this->jsonSuccess('WhatsApp sent!');
        }
        return $this->jsonError('Failed.');
    }

    // ── PAYMENT LINK ──────────────────────────────────────────
    public function generatePaymentLink($id)
    {
        $inv = $this->im->getWithDetails($id);
        $bal = (float) ($inv['balance_due'] ?? ($inv['total'] - $inv['paid_amount']));
        if ($bal <= 0) return $this->jsonError('No balance due on this invoice.');

        $order = (new PaymentService())->createOrder($bal, 'invoice', (int)$id, (int)$inv['client_id']);
        if (!$order) return $this->jsonError('Could not create Razorpay order. Check your keys in Settings.');

        return $this->jsonSuccess('Order created.', [
            'order_id'    => $order['id'],
            'amount'      => $order['amount'],
            'razorpay_key'=> $this->settings['razorpay_key'] ?? '',
            'pay_url'     => base_url("portal/pay/$id"),
        ]);
    }

    // ── AJAX: invoices by client (for payment create page) ────
    public function byClient($clientId)
    {
        $invoices = $this->db->table('invoices')
            ->select('id, invoice_number, total, paid_amount, balance_due, status')
            ->where('client_id', $clientId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $invoices]);
    }

    // ── AJAX: open milestones for a project (for "Invoice For: Milestone") ──
    public function ajaxMilestones($projectId)
    {
        $rows = (new MilestoneModel())
            ->where('project_id', $projectId)
            ->whereNotIn('status', ['paid'])
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        return $this->response->setJSON($rows);
    }

    // ── AJAX: a client's domains (for "Invoice For: Domain Renewal") ────────
    public function ajaxDomains($clientId)
    {
        $rows = (new DomainModel())
            ->where('client_id', $clientId)
            ->orderBy('expiry_date', 'ASC')
            ->findAll();

        return $this->response->setJSON($rows);
    }

    // ── AJAX: a client's hostings (for "Invoice For: Hosting Renewal") ──────
    public function ajaxHostings($clientId)
    {
        $rows = (new HostingModel())
            ->where('client_id', $clientId)
            ->orderBy('expiry_date', 'ASC')
            ->findAll();

        return $this->response->setJSON($rows);
    }
}
