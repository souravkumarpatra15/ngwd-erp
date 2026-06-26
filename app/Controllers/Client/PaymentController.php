<?php
namespace App\Controllers\Client;
use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Services\PaymentService;

class PaymentController extends BaseController
{
    public function checkout($invoiceId) {
        $inv = $this->db->table('invoices')->select('invoices.*, clients.name as client_name, clients.email as client_email')->join('clients','clients.id = invoices.client_id','left')->where('invoices.id',$invoiceId)->where('invoices.client_id',session()->get('client_id'))->get()->getRowArray();
        if (!$inv || $inv['balance_due'] <= 0) return redirect()->to('portal/invoices')->with('info','Invoice already paid or not found');
        $order = (new PaymentService())->createOrder($inv['balance_due'],'invoice',$invoiceId,$inv['client_id']);
        return view('client/payments/checkout', ['title'=>'Pay Invoice','invoice'=>$inv,'razorpay_order'=>$order,'razorpay_key'=>$this->settings['razorpay_key']??'','settings'=>$this->settings]);
    }

    public function verify() {
        $data = $this->request->getJSON(true);
        $valid = (new PaymentService())->verifyPayment($data['razorpay_order_id'],$data['razorpay_payment_id'],$data['razorpay_signature']);
        return $valid ? $this->jsonSuccess('Payment verified') : $this->jsonError('Verification failed');
    }
}
