<?php
namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Services\PaymentService;

class PaymentController extends BaseController
{
    // ── CHECKOUT PAGE ─────────────────────────────────────────
    public function checkout($invoiceId)
    {
        $cid = (int) session()->get('client_id');

        $inv = $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, clients.email as client_email,
                      projects.name as project_name')
            ->join('clients',  'clients.id  = invoices.client_id',  'left')
            ->join('projects', 'projects.id = invoices.project_id', 'left')
            ->where('invoices.id', $invoiceId)
            ->where('invoices.client_id', $cid)
            ->get()->getRowArray();

        if (!$inv) {
            return redirect()->to('portal/invoices')->with('error', 'Invoice not found.');
        }

        // balance_due is a regular column — read it directly
        $balanceDue = (float) $inv['balance_due'];
        if ($balanceDue <= 0) {
            return redirect()->to('portal/invoices/' . $invoiceId)
                             ->with('info', 'This invoice is already fully paid.');
        }

        // Create Razorpay order
        $order = (new PaymentService())->createOrder($balanceDue, 'invoice', (int)$invoiceId, $cid);

        return view('client/payments/checkout', [
            'title'          => 'Pay Invoice',
            'invoice'        => $inv,
            'razorpay_order' => $order,        // array with 'id', 'amount', etc.
            'razorpay_key'   => $this->settings['razorpay_key'] ?? '',
            'settings'       => $this->settings,
        ]);
    }

    // ── VERIFY (called via fetch() JSON POST from checkout view) ──
    public function verify()
    {
        // Try JSON body first (fetch API), then fallback to POST fields
        $raw  = $this->request->getBody();
        $data = json_decode($raw, true);
        if (empty($data)) {
            $data = $this->request->getPost();
        }

        $orderId   = trim($data['razorpay_order_id']   ?? '');
        $paymentId = trim($data['razorpay_payment_id'] ?? '');
        $signature = trim($data['razorpay_signature']  ?? '');
        $invoiceId = (int) ($data['invoice_id']        ?? 0);
        $cid       = (int) session()->get('client_id');

        if (!$orderId || !$paymentId || !$signature || !$invoiceId) {
            return $this->jsonError('Missing payment data. Please try again.');
        }

        // Verify Razorpay signature
        $valid = (new PaymentService())->verifyPayment($orderId, $paymentId, $signature);
        if (!$valid) {
            log_message('error', "Razorpay signature mismatch: order={$orderId} pay={$paymentId}");
            return $this->jsonError('Payment verification failed. Please contact support.');
        }

        // Fetch invoice (verify belongs to this client)
        $im  = new InvoiceModel();
        $inv = $this->db->table('invoices')
            ->where('id', $invoiceId)
            ->where('client_id', $cid)
            ->get()->getRowArray();

        if (!$inv) {
            return $this->jsonError('Invoice not found.');
        }

        $balanceDue = (float) $inv['balance_due'];
        if ($balanceDue <= 0) {
            return $this->jsonError('This invoice already has no balance due.');
        }

        $newPaid    = (float) $inv['paid_amount'] + $balanceDue;
        $newBalance = 0; // fully paid at checkout
        $newStatus  = $newPaid >= (float) $inv['total'] ? 'paid' : 'partial';

        // Update invoice
        $invUpdate = [
            'paid_amount' => $newPaid,
            'balance_due' => $newBalance,
            'status'      => $newStatus,
        ];
        if ($newStatus === 'paid') {
            $invUpdate['paid_at'] = date('Y-m-d H:i:s');
        }
        $this->db->table('invoices')->where('id', $invoiceId)->update($invUpdate);

        // Generate payment number
        $total      = $this->db->table('payments')->countAll();
        $payNumber  = sprintf('PAY/%s/%05d', date('Y'), $total + 1);

        // Insert payment record (only DB-valid fields)
        $this->db->table('payments')->insert([
            'payment_number'      => $payNumber,
            'client_id'           => $cid,
            'invoice_id'          => $invoiceId,
            'project_id'          => $inv['project_id']   ?? null,
            'milestone_id'        => $inv['milestone_id'] ?? null,
            'amount'              => $balanceDue,
            'method'              => 'razorpay',                 // valid enum
            'transaction_id'      => $paymentId,
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'payment_date'        => date('Y-m-d'),
            'status'              => 'completed',
            'created_by'          => $cid,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        // Update project total_paid
        if ($inv['project_id'] ?? null) {
            $pr = $this->db->table('projects')->where('id', $inv['project_id'])->get()->getRowArray();
            if ($pr) {
                $this->db->table('projects')->where('id', $inv['project_id'])
                         ->update(['total_paid' => (float)$pr['total_paid'] + $balanceDue]);
            }
        }

        // Update milestone status if linked
        if ($inv['milestone_id'] ?? null) {
            $this->db->table('milestones')
                ->where('id', $inv['milestone_id'])
                ->update(['status' => 'paid', 'completed_date' => date('Y-m-d')]);
        }

        log_message('info', "Payment verified: {$paymentId} invoice={$invoiceId} amount={$balanceDue}");

        return $this->jsonSuccess('Payment successful! Thank you.');
    }
}
