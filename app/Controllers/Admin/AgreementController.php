<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AgreementModel;
use App\Models\ClientModel;
use App\Services\PDFService;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use App\Services\WhatsAppNotificationService;
use App\Services\NotificationService;

class AgreementController extends BaseController
{
    protected AgreementModel $am;

    public function __construct()
    {
        $this->am = new AgreementModel();
    }

    // ── LIST ──────────────────────────────────────────────────
    public function index()
    {
        if($r=$this->requireModule('agreements','view'))return $r;
        $agreements = $this->db->table('agreements')
            ->select('agreements.*, clients.name as client_name, projects.name as project_name')
            ->join('clients',  'clients.id  = agreements.client_id',  'left')
            ->join('projects', 'projects.id = agreements.project_id', 'left')
            ->orderBy('agreements.created_at', 'DESC')
            ->get()->getResultArray();

        return view('admin/agreements/index', [
            'title'      => 'Agreements',
            'agreements' => $agreements,
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────
    public function create()
    {
        return view('admin/agreements/create', [
            'title'   => 'New Agreement',
            'clients' => (new ClientModel())->orderBy('name')->findAll(),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store()
    {
        $post = $this->request->getPost();
        unset($post['csrf_test_name']);

        $post['agreement_number'] = $this->generateNumber('AGR', $this->am);
        $post['created_by']       = session()->get('user_id');
        if (empty($post['status'])) $post['status'] = 'draft';
        if (empty($post['project_id'])) $post['project_id'] = null;

        $id = $this->am->insert($post);
        $this->logActivity('agreements', $id, 'create', "Created {$post['agreement_number']}");
        return redirect()->to("admin/agreements/$id")->with('success', 'Agreement created!');
    }

    // ── SHOW ──────────────────────────────────────────────────
    public function show($id)
    {
        $agreement = $this->am->getWithDetails($id);
        if (!$agreement) return redirect()->to('admin/agreements')->with('error', 'Not found.');
        return view('admin/agreements/show', ['title' => 'Agreement', 'agreement' => $agreement]);
    }

    // ── EDIT ──────────────────────────────────────────────────
    public function edit($id)
    {
        $agreement = $this->am->find($id);
        if (!$agreement) return redirect()->to('admin/agreements')->with('error', 'Not found.');

        // Load project list for current client
        $projects = $this->db->table('projects')
            ->select('id, name')
            ->where('client_id', $agreement['client_id'])
            ->where('deleted_at IS NULL')
            ->findAll() ?? [];

        return view('admin/agreements/edit', [
            'title'     => 'Edit Agreement',
            'agreement' => $agreement,
            'clients'   => (new ClientModel())->orderBy('name')->findAll(),
            'projects'  => $projects,
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────────
    public function update($id)
    {
        $post = $this->request->getPost();
        unset($post['csrf_test_name']);
        if (empty($post['project_id'])) $post['project_id'] = null;
        $this->am->update($id, $post);
        $this->logActivity('agreements', $id, 'update', 'Updated agreement');
        return redirect()->to("admin/agreements/$id")->with('success', 'Agreement updated!');
    }

    // ── DELETE ────────────────────────────────────────────────
    public function delete($id)
    {
        $a = $this->am->find($id);
        if (!$a) return $this->jsonError('Agreement not found.');
        if ($a['status'] === 'signed') return $this->jsonError('Cannot delete a signed agreement.');
        $this->am->delete($id);
        $this->logActivity('agreements', $id, 'delete', "Deleted {$a['agreement_number']}");
        return $this->jsonSuccess('Agreement deleted.');
    }

    // ── STATUS ────────────────────────────────────────────────
    public function updateStatus($id)
    {
        $status  = $this->request->getPost('status');
        $allowed = ['draft', 'sent', 'signed', 'rejected'];
        if (!in_array($status, $allowed)) return $this->jsonError('Invalid status.');

        $data = ['status' => $status];
        if ($status === 'signed') {
            $data['signed_at']    = date('Y-m-d H:i:s');
            $data['signature_ip'] = $this->request->getIPAddress();
        }
        if ($status === 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        $this->am->update($id, $data);
        $this->logActivity('agreements', $id, 'status', "Status → $status");
        return $this->jsonSuccess("Status updated to $status.");
    }

    // ── PDF ───────────────────────────────────────────────────
    public function generatePDF($id)
    {
        $agreement = $this->am->getWithDetails($id);
        if (!$agreement) return redirect()->to('admin/agreements')->with('error', 'Agreement not found.');
        return (new PDFService())->generateAgreement($agreement, $this->settings);
    }

    // ── SEND EMAIL ────────────────────────────────────────────
    public function sendEmail($id)
    {
        $a    = $this->am->getWithDetails($id);
        $path = (new PDFService())->saveAgreementPDF($a, $this->settings);
        $link = base_url("portal/agreements/sign/{$id}");
        $body = "<p>Dear {$a['client_name']},</p>
                 <p>Agreement <strong>{$a['title']}</strong> is ready for your review and signature.</p>
                 <p><a href='{$link}' style='background:#0d6efd;color:#fff;padding:10px 24px;border-radius:5px;text-decoration:none'>Review &amp; Sign</a></p>
                 <p>Or copy this link: <br><small>{$link}</small></p>";
        $res = (new EmailService())->send($a['client_email'], "Agreement: {$a['title']}", $body, $path);
        if ($res) {
            $this->am->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            (new NotificationService())->createForClient($a['client_id'], 'agreement_sent', 'Agreement Ready to Sign', "\"{$a['title']}\" needs your signature", (int) $id, 'agreement');
            return $this->jsonSuccess('Email sent!');
        }
        return $this->jsonError('Failed to send email.');
    }

    // ── SEND WHATSAPP ─────────────────────────────────────────
    public function sendWhatsApp($id)
    {
        $a   = $this->am->getWithDetails($id);
        if (!$a || empty($a['client_whatsapp'])) return $this->jsonError('Client WhatsApp number not available.');
        // WhatsApp template: erp_agreement_sent
        // Updated to support PDF document in header - pass pdfUrl & pdfFilename optionally:
        $pdfUrl = '';
        $pdfFilename = '';
        if (!empty($a['agreement_pdf_url'])) {
            $pdfUrl = (string)($a['agreement_pdf_url']);
            $pdfFilename = basename($pdfUrl); // e.g., 'Agreement-001.pdf'
        }
        $res = (new WhatsAppNotificationService())->agreementSent(
            (string)$a['client_whatsapp'],
            (string)($a['client_name'] ?? ''),
            (string)($a['agreement_number'] ?? $a['title'] ?? ''),
            $pdfUrl,
            $pdfFilename
        );
        if ($res) {
            $this->am->update($id, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
            (new NotificationService())->createForClient($a['client_id'], 'agreement_sent', 'Agreement Ready to Sign', "\"{$a['title']}\" needs your signature", (int) $id, 'agreement');
            return $this->jsonSuccess('WhatsApp sent!');
        }
        return $this->jsonError('Failed to send WhatsApp message.');
    }

    // ── MANUAL REMINDER ─────────────────────────────────────
    // Route: POST admin/agreements/remind/(:num)
    public function remind($id)
    {
        $a = $this->am->getWithDetails($id);
        if (!$a) return $this->jsonError('Agreement not found.');
        if (empty($a['client_whatsapp'])) return $this->jsonError('Client WhatsApp number not available.');
        if ($a['status'] === 'signed') return $this->jsonError('This agreement is already signed.');
        $link = base_url('agreements/sign/' . $a['id']);
        $ok = (new WhatsAppNotificationService())->general(
            (string) $a['client_whatsapp'],
            (string) ($a['client_name'] ?? ''),
            "Reminder: agreement {$a['agreement_number']} ({$a['title']}) is awaiting your signature. Review & sign: {$link}"
        );
        return $ok ? $this->jsonSuccess('WhatsApp reminder sent!') : $this->jsonError('Failed to send WhatsApp message.');
    }

    // ── PAYMENT LINK ──────────────────────────────────────────
    // Route: POST admin/agreements/payment-link/(:num)
    // Agreements have no amount of their own — the link is created
    // against the first open invoice of the same client.
    public function generatePaymentLink($id)
    {
        $a = $this->am->getWithDetails($id);
        if (!$a) return $this->jsonError('Agreement not found.');

        $invoice = $this->db->table('invoices')
            ->select('id, invoice_number, balance_due, client_id')
            ->where('client_id', (int) $a['client_id'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->orderBy('due_date', 'ASC')
            ->get()->getRowArray();

        if (!$invoice) return $this->jsonError('No open invoice found for this client. Record the invoice first.');

        $order = (new \App\Services\PaymentService())->createOrder(
            (float) $invoice['balance_due'],
            'invoice',
            (int) $invoice['id'],
            (int) $invoice['client_id']
        );
        if (!$order) return $this->jsonError('Could not create Razorpay order. Check your keys in Settings.');

        return $this->jsonSuccess('Order created.', [
            'order_id'     => $order['id'],
            'amount'       => $order['amount'],
            'razorpay_key' => $this->settings['razorpay_key'] ?? '',
            'pay_url'      => base_url('portal/pay/' . $invoice['id']),
        ]);
    }
}
