<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Services\PaymentService;

class PaymentController extends BaseController
{
    public function checkout($invoiceId)
    {
        $cid = session()->get('client_id');

        $inv = $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, clients.email as client_email, projects.name as project_name')
            ->join('clients',  'clients.id  = invoices.client_id',  'left')
            ->join('projects', 'projects.id = invoices.project_id', 'left')
            ->where('invoices.id', $invoiceId)
            ->where('invoices.client_id', $cid)
            ->get()->getRowArray();

        if (!$inv) {
            return redirect()->to('portal/invoices')->with('error', 'Invoice not found.');
        }

        // balance_due is a generated column in DB; calculate fallback just in case
        $balanceDue = (float) ($inv['balance_due'] ?? ($inv['total'] - $inv['paid_amount']));

        if ($balanceDue <= 0) {
            return redirect()->to('portal/invoices')->with('info', 'This invoice is already fully paid.');
        }

        // Create Razorpay order
        $order = (new PaymentService())->createOrder($balanceDue, 'invoice', (int)$invoiceId, (int)$cid);

        return view('client/payments/checkout', [
            'title'          => 'Pay Invoice',
            'invoice'        => $inv,
            'razorpay_order' => $order,
            'razorpay_key'   => $this->settings['razorpay_key'] ?? '',
            'settings'       => $this->settings,
        ]);
    }

    public function verify()
    {
        // Accepts JSON body from fetch() in the checkout view
        $raw  = $this->request->getBody();
        $data = json_decode($raw, true);

        if (!$data) {
            // fallback: try POST fields (form-submit)
            $data = $this->request->getPost();
        }

        $orderId   = $data['razorpay_order_id']   ?? '';
        $paymentId = $data['razorpay_payment_id']  ?? '';
        $signature = $data['razorpay_signature']   ?? '';
        $invoiceId = (int) ($data['invoice_id']    ?? 0);
        $cid       = (int) session()->get('client_id');

        if (!$orderId || !$paymentId || !$signature || !$invoiceId) {
            return $this->jsonError('Missing payment data.');
        }

        $valid = (new PaymentService())->verifyPayment($orderId, $paymentId, $signature);

        if (!$valid) {
            return $this->jsonError('Payment verification failed. Please contact support.');
        }

        // Fetch invoice
        $inv = $this->db->table('invoices')
            ->where('id', $invoiceId)
            ->where('client_id', $cid)
            ->get()->getRowArray();

        if (!$inv) {
            return $this->jsonError('Invoice not found.');
        }

        $balanceDue  = (float) ($inv['balance_due'] ?? ($inv['total'] - $inv['paid_amount']));
        $newPaidAmt  = (float) $inv['paid_amount'] + $balanceDue;
        $newStatus   = $newPaidAmt >= (float) $inv['total'] ? 'paid' : 'partial';
        $paidAt      = $newStatus === 'paid' ? date('Y-m-d H:i:s') : null;

        // Update invoice
        $updateData = ['paid_amount' => $newPaidAmt, 'status' => $newStatus];
        if ($paidAt) $updateData['paid_at'] = $paidAt;
        $this->db->table('invoices')->where('id', $invoiceId)->update($updateData);

        // Record payment
        $paymentNumber = 'PAY-' . strtoupper(uniqid());
        $this->db->table('payments')->insert([
            'payment_number'      => $paymentNumber,
            'client_id'           => $cid,
            'invoice_id'          => $invoiceId,
            'project_id'          => $inv['project_id'] ?? null,
            'milestone_id'        => $inv['milestone_id'] ?? null,
            'amount'              => $balanceDue,
            'method'              => 'razorpay',
            'transaction_id'      => $paymentId,
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'status'              => 'completed',
            'payment_date'        => date('Y-m-d'),
            'created_by'          => $cid,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        // Update milestone if linked
        if ($inv['milestone_id'] ?? null) {
            $this->db->table('milestones')
                ->where('id', $inv['milestone_id'])
                ->update(['status' => 'paid']);
        }

        log_message('info', "Razorpay payment verified: {$paymentId} for invoice {$invoiceId}");

        return $this->jsonSuccess('Payment successful! Thank you.');
    }
}
