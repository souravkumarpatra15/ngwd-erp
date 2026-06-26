<?php
namespace App\Controllers\Api;
use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\ProjectModel;
use App\Models\RazorpayOrderModel;
use App\Services\NotificationService;

class WebhookController extends BaseController
{
    public function razorpay() {
        $payload = file_get_contents('php://input');
        $sig     = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
        $secret  = (new \App\Models\SettingModel())->getSetting('razorpay_secret');
        if (!hash_equals(hash_hmac('sha256',$payload,$secret), $sig)) {
            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        }
        $event = json_decode($payload, true);
        if ($event['event'] === 'payment.captured') $this->handleCapture($event['payload']['payment']['entity']);
        return $this->response->setStatusCode(200)->setBody('OK');
    }

    protected function handleCapture(array $pay) {
        $om  = new RazorpayOrderModel();
        $pm  = new PaymentModel();
        $im  = new InvoiceModel();
        $prm = new ProjectModel();
        $order = $om->where('order_id',$pay['order_id'])->first();
        if (!$order) return;
        $om->update($order['id'],['status'=>'paid','payment_id'=>$pay['id']]);
        $payNo = sprintf('PAY/%s/%05d',date('Y'),$pm->countAll()+1);
        $pid   = $pm->insert(['payment_number'=>$payNo,'client_id'=>$order['client_id'],'invoice_id'=>$order['entity_type']==='invoice'?$order['entity_id']:null,'amount'=>$order['amount'],'method'=>'razorpay','transaction_id'=>$pay['id'],'razorpay_order_id'=>$pay['order_id'],'razorpay_payment_id'=>$pay['id'],'payment_date'=>date('Y-m-d'),'status'=>'completed','created_by'=>1]);
        if ($order['entity_type']==='invoice') {
            $inv = $im->find($order['entity_id']);
            $newPaid = $inv['paid_amount'] + $order['amount'];
            $status  = $newPaid >= $inv['total'] ? 'paid' : 'partial';
            $im->update($order['entity_id'],['paid_amount'=>$newPaid,'status'=>$status,'paid_at'=>$status==='paid'?date('Y-m-d H:i:s'):null]);
            if ($inv['project_id']) {
                $pr = $prm->find($inv['project_id']);
                $prm->update($inv['project_id'],['total_paid'=>$pr['total_paid']+$order['amount']]);
            }
        }
        (new NotificationService())->create(1,'payment_received','Payment Received','₹'.$order['amount'].' via Razorpay',$pid,'payment');
    }
}
