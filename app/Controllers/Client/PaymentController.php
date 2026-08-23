<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Services\PaymentService;

class PaymentController extends BaseController
{
    public function checkout($invoiceId)
    {
        $cid = (int) session()->get('client_id');
        $inv = $this->db->table('invoices')
            ->select('invoices.*, clients.name as client_name, clients.email as client_email, projects.name as project_name')
            ->join('clients', 'clients.id = invoices.client_id', 'left')
            ->join('projects', 'projects.id = invoices.project_id', 'left')
            ->where('invoices.id', $invoiceId)->where('invoices.client_id', $cid)
            ->get()->getRowArray();

        if (!$inv) return redirect()->to('portal/invoices')->with('error', 'Invoice not found.');

        $balanceDue = max(0, (float) ($inv['balance_due'] ?? ((float) $inv['total'] - (float) $inv['paid_amount'])));
        if ($balanceDue <= 0) return redirect()->to('portal/invoices')->with('info', 'This invoice is already fully paid.');

        $order = (new PaymentService())->createOrder($balanceDue, 'invoice', (int) $invoiceId, $cid);
        if (!$order) return redirect()->to('portal/invoices')->with('error', 'Unable to start payment. Please try again.');

        return view('client/payments/checkout', [
            'title' => 'Pay Invoice', 'invoice' => $inv, 'razorpay_order' => $order,
            'razorpay_key' => $this->settings['razorpay_key'] ?? '', 'settings' => $this->settings,
        ]);
    }

    public function verify()
    {
        $data = json_decode($this->request->getBody(), true) ?: $this->request->getPost();
        $orderId = trim((string) ($data['razorpay_order_id'] ?? ''));
        $paymentId = trim((string) ($data['razorpay_payment_id'] ?? ''));
        $signature = trim((string) ($data['razorpay_signature'] ?? ''));
        $invoiceId = (int) ($data['invoice_id'] ?? 0);
        $cid = (int) session()->get('client_id');

        if (!$orderId || !$paymentId || !$signature || !$invoiceId || !$cid) return $this->jsonError('Missing payment data.');
        if (!(new PaymentService())->verifyPayment($orderId, $paymentId, $signature)) return $this->jsonError('Payment verification failed. Please contact support.');

        $this->db->transBegin();
        try {
            // Lock the Razorpay order first. This makes verification idempotent and
            // prevents the same successful callback from creating two payments.
            $order = $this->db->query('SELECT * FROM razorpay_orders WHERE order_id = ? FOR UPDATE', [$orderId])->getRowArray();
            if (!$order || (int) $order['client_id'] !== $cid || $order['entity_type'] !== 'invoice' || (int) $order['entity_id'] !== $invoiceId) {
                throw new \RuntimeException('Payment order does not belong to this invoice.');
            }
            if ($order['status'] === 'paid') {
                if (($order['payment_id'] ?? '') === $paymentId) {
                    $this->db->transCommit();
                    return $this->jsonSuccess('Payment already processed.');
                }
                throw new \RuntimeException('This payment order has already been processed.');
            }

            // Lock the invoice while validating the amount and updating its balance.
            $inv = $this->db->query('SELECT * FROM invoices WHERE id = ? AND client_id = ? FOR UPDATE', [$invoiceId, $cid])->getRowArray();
            if (!$inv) throw new \RuntimeException('Invoice not found.');

            $orderAmount = (float) $order['amount'];
            $balanceDue = max(0, (float) ($inv['balance_due'] ?? ((float) $inv['total'] - (float) $inv['paid_amount'])));
            if ($balanceDue <= 0) throw new \RuntimeException('Invoice is already fully paid.');
            if (abs($orderAmount - $balanceDue) > 0.01) throw new \RuntimeException('Payment amount no longer matches the invoice balance. Please create a new payment order.');

            $newPaidAmt = min((float) $inv['total'], (float) $inv['paid_amount'] + $orderAmount);
            $newStatus = $newPaidAmt >= (float) $inv['total'] ? 'paid' : 'partial';
            $updateData = ['paid_amount' => $newPaidAmt, 'status' => $newStatus];
            if ($newStatus === 'paid') $updateData['paid_at'] = date('Y-m-d H:i:s');

            if (!$this->db->table('invoices')->where('id', $invoiceId)->update($updateData)) throw new \RuntimeException('Unable to update invoice.');

            $inserted = $this->db->table('payments')->insert([
                'payment_number' => 'PAY-' . strtoupper(bin2hex(random_bytes(8))),
                'client_id' => $cid, 'invoice_id' => $invoiceId, 'project_id' => $inv['project_id'] ?? null,
                'milestone_id' => $inv['milestone_id'] ?? null, 'amount' => $orderAmount, 'method' => 'razorpay',
                'transaction_id' => $paymentId, 'razorpay_order_id' => $orderId, 'razorpay_payment_id' => $paymentId,
                'status' => 'completed', 'payment_date' => date('Y-m-d'), 'created_by' => $cid,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$inserted) throw new \RuntimeException('Unable to record payment.');

            if (!empty($inv['milestone_id']) && !$this->db->table('milestones')->where('id', $inv['milestone_id'])->update(['status' => 'paid'])) {
                throw new \RuntimeException('Unable to update milestone.');
            }

            if (!$this->db->table('razorpay_orders')->where('id', $order['id'])->update(['status' => 'paid', 'payment_id' => $paymentId])) {
                throw new \RuntimeException('Unable to finalize payment order.');
            }

            if (!$this->db->transStatus()) throw new \RuntimeException('Payment transaction failed.');
            $this->db->transCommit();
            log_message('info', "Razorpay payment verified: {$paymentId} for invoice {$invoiceId}");
            return $this->jsonSuccess('Payment successful! Thank you.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Invoice payment verification failed: {message}', ['message' => $e->getMessage()]);
            return $this->jsonError($e->getMessage() === 'Invoice is already fully paid.' ? $e->getMessage() : 'Payment could not be completed. Please try again.');
        }
    }

    public function checkoutMilestone($milestoneId)
    {
        $cid = (int) session()->get('client_id');
        $ms = $this->db->table('milestones')
            ->select('milestones.*, projects.name as project_name, projects.client_id as project_client_id')
            ->join('projects', 'projects.id = milestones.project_id', 'left')
            ->where('milestones.id', $milestoneId)->get()->getRowArray();

        if (!$ms || (int) $ms['project_client_id'] !== $cid) return redirect()->to('portal/projects')->with('error', 'Milestone not found.');
        if (in_array($ms['status'], ['completed', 'paid'], true)) return redirect()->to('portal/projects/' . $ms['project_id'])->with('info', 'This milestone is already paid.');
        if ((float) $ms['amount'] <= 0) return redirect()->to('portal/projects/' . $ms['project_id'])->with('error', 'Invalid milestone amount.');

        $order = (new PaymentService())->createOrder((float) $ms['amount'], 'milestone', (int) $milestoneId, $cid);
        if (!$order) return redirect()->to('portal/projects/' . $ms['project_id'])->with('error', 'Unable to start payment. Please try again.');

        return view('client/payments/checkout_milestone', [
            'title' => 'Pay Milestone', 'milestone' => $ms, 'razorpay_order' => $order,
            'razorpay_key' => $this->settings['razorpay_key'] ?? '', 'settings' => $this->settings,
        ]);
    }

    public function verifyMilestone()
    {
        $data = json_decode($this->request->getBody(), true) ?: $this->request->getPost();
        $orderId = trim((string) ($data['razorpay_order_id'] ?? ''));
        $paymentId = trim((string) ($data['razorpay_payment_id'] ?? ''));
        $signature = trim((string) ($data['razorpay_signature'] ?? ''));
        $milestoneId = (int) ($data['milestone_id'] ?? 0);
        $cid = (int) session()->get('client_id');

        if (!$orderId || !$paymentId || !$signature || !$milestoneId || !$cid) return $this->jsonError('Missing payment data.');
        if (!(new PaymentService())->verifyPayment($orderId, $paymentId, $signature)) return $this->jsonError('Payment verification failed. Please contact support.');

        $this->db->transBegin();
        try {
            $order = $this->db->query('SELECT * FROM razorpay_orders WHERE order_id = ? FOR UPDATE', [$orderId])->getRowArray();
            if (!$order || (int) $order['client_id'] !== $cid || $order['entity_type'] !== 'milestone' || (int) $order['entity_id'] !== $milestoneId) {
                throw new \RuntimeException('Payment order does not belong to this milestone.');
            }
            if ($order['status'] === 'paid') {
                if (($order['payment_id'] ?? '') === $paymentId) {
                    $this->db->transCommit();
                    return $this->jsonSuccess('Payment already processed.');
                }
                throw new \RuntimeException('This payment order has already been processed.');
            }

            $ms = $this->db->query('SELECT milestones.*, projects.client_id as project_client_id FROM milestones LEFT JOIN projects ON projects.id = milestones.project_id WHERE milestones.id = ? FOR UPDATE', [$milestoneId])->getRowArray();
            if (!$ms || (int) $ms['project_client_id'] !== $cid) throw new \RuntimeException('Milestone not found.');
            if (in_array($ms['status'], ['completed', 'paid'], true)) throw new \RuntimeException('This milestone is already paid.');

            $orderAmount = (float) $order['amount'];
            if (abs($orderAmount - (float) $ms['amount']) > 0.01) throw new \RuntimeException('Payment amount no longer matches the milestone amount. Please create a new payment order.');

            if (!$this->db->table('milestones')->where('id', $milestoneId)->update(['status' => 'paid', 'completed_date' => date('Y-m-d')])) throw new \RuntimeException('Unable to update milestone.');

            if (!$this->db->table('payments')->insert([
                'payment_number' => 'PAY-' . strtoupper(bin2hex(random_bytes(8))), 'client_id' => $cid,
                'project_id' => $ms['project_id'], 'invoice_id' => null, 'milestone_id' => $milestoneId,
                'amount' => $orderAmount, 'method' => 'razorpay', 'transaction_id' => $paymentId,
                'razorpay_order_id' => $orderId, 'razorpay_payment_id' => $paymentId, 'status' => 'completed',
                'payment_date' => date('Y-m-d'), 'created_by' => $cid, 'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ])) throw new \RuntimeException('Unable to record payment.');

            if (!$this->db->table('razorpay_orders')->where('id', $order['id'])->update(['status' => 'paid', 'payment_id' => $paymentId])) throw new \RuntimeException('Unable to finalize payment order.');
            if (!$this->db->transStatus()) throw new \RuntimeException('Payment transaction failed.');

            $this->db->transCommit();
            log_message('info', "Razorpay payment verified: {$paymentId} for milestone {$milestoneId}");
            return $this->jsonSuccess('Payment successful! Thank you.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Milestone payment verification failed: {message}', ['message' => $e->getMessage()]);
            return $this->jsonError($e->getMessage() === 'This milestone is already paid.' ? $e->getMessage() : 'Payment could not be completed. Please try again.');
        }
    }
}