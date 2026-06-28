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

    public function __construct()
    {
        $this->pm = new PaymentModel();
    }

    // ── LIST ──────────────────────────────────────────────────
    public function index()
    {
        return view('admin/payments/index', ['title' => 'Payments']);
    }

    public function datatable()
    {
        $result = $this->pm->getDataTable(
            $this->request->getGet('search')['value'] ?? '',
            (int) $this->request->getGet('start'),
            (int) $this->request->getGet('length')
        );
        return $this->response->setJSON([
            'draw'            => intval($this->request->getGet('draw')),
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['total'],
            'data'            => $result['data'],
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────
    public function create()
    {
        return view('admin/payments/create', [
            'title'   => 'Record Payment',
            'clients' => (new ClientModel())->orderBy('name')->findAll(),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store()
    {
        // Validate — method enum matches DB exactly
        if (!$this->validate([
            'client_id'    => 'required|integer',
            'amount'       => 'required|decimal|greater_than[0]',
            'method'       => 'required|in_list[razorpay,upi,bank_transfer,cash,cheque]',
            'payment_date' => 'required|valid_date',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $invoiceId   = $this->request->getPost('invoice_id')   ?: null;
        $projectId   = $this->request->getPost('project_id')   ?: null;
        $milestoneId = $this->request->getPost('milestone_id') ?: null;
        $amount      = (float) $this->request->getPost('amount');
        $clientId    = (int)   $this->request->getPost('client_id');

        // Auto-generate payment number
        $payNo = sprintf('PAY/%s/%05d', date('Y'), $this->pm->countAll() + 1);

        $pid = $this->pm->insert([
            'payment_number'  => $payNo,
            'client_id'       => $clientId,
            'project_id'      => $projectId,
            'invoice_id'      => $invoiceId,
            'milestone_id'    => $milestoneId,
            'amount'          => $amount,
            'method'          => $this->request->getPost('method'),
            'transaction_id'  => $this->request->getPost('transaction_id') ?: null,
            'payment_date'    => $this->request->getPost('payment_date'),
            'notes'           => $this->request->getPost('notes') ?: null,
            'status'          => 'completed',
            'created_by'      => session()->get('user_id'),
        ]);

        // ── Update linked invoice paid_amount + balance_due + status ──
        if ($invoiceId) {
            $im  = new InvoiceModel();
            $inv = $im->find($invoiceId);
            if ($inv) {
                $newPaid    = (float) $inv['paid_amount'] + $amount;
                $newBalance = max(0, (float) $inv['total'] - $newPaid);
                $newStatus  = $newPaid >= (float) $inv['total'] ? 'paid' : 'partial';

                $updateData = [
                    'paid_amount' => $newPaid,
                    'balance_due' => $newBalance,
                    'status'      => $newStatus,
                ];
                if ($newStatus === 'paid') {
                    $updateData['paid_at'] = date('Y-m-d H:i:s');
                }
                $im->update($invoiceId, $updateData);
            }
        }

        // ── Update linked project total_paid ──────────────────
        if ($projectId) {
            $prm = new ProjectModel();
            $pr  = $prm->find($projectId);
            if ($pr) {
                $prm->update($projectId, ['total_paid' => (float) $pr['total_paid'] + $amount]);
            }
        }

        // ── Update linked milestone status ────────────────────
        if ($milestoneId) {
            $this->db->table('milestones')->where('id', $milestoneId)->update(['status' => 'paid']);
        }

        $this->logActivity('payments', $pid, 'created', "Payment {$payNo}: ₹{$amount}");
        return redirect()->to("admin/payments/$pid")->with('success', "Payment recorded: $payNo");
    }

    // ── SHOW ──────────────────────────────────────────────────
    public function show($id)
    {
        $p = $this->db->table('payments')
            ->select('payments.*, clients.name as client_name, clients.email as client_email,
                      projects.name as project_name, invoices.invoice_number')
            ->join('clients',  'clients.id  = payments.client_id',  'left')
            ->join('projects', 'projects.id = payments.project_id', 'left')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'left')
            ->where('payments.id', $id)
            ->get()->getRowArray();

        if (!$p) return redirect()->to('admin/payments');
        return view('admin/payments/show', ['title' => 'Payment', 'payment' => $p]);
    }

    // ── RECEIPT PDF ───────────────────────────────────────────
    public function receipt($id)
    {
        $p = $this->db->table('payments')
            ->select('payments.*, clients.name as client_name, clients.address as client_address,
                      clients.email as client_email, projects.name as project_name')
            ->join('clients',  'clients.id  = payments.client_id',  'left')
            ->join('projects', 'projects.id = payments.project_id', 'left')
            ->where('payments.id', $id)
            ->get()->getRowArray();

        return (new PDFService())->generateReceipt($p, $this->settings);
    }

    // ── AJAX: milestones by project ───────────────────────────
    // Called by payments/create.php when project changes
    public function milestonesByProject($projectId)
    {
        $milestones = $this->db->table('milestones')
            ->select('id, title, amount, status')
            ->where('project_id', $projectId)
            ->whereNotIn('status', ['paid'])
            ->orderBy('sort_order')
            ->get()->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $milestones]);
    }
}
