<?php
namespace App\Controllers\Api;
use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\ProjectModel;
use App\Models\RazorpayOrderModel;
use App\Services\NotificationService;
use App\Services\WhatsAppNotificationService;

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
            $currency = $inv['currency'] ?? 'INR';
        } elseif ($order['entity_type']==='milestone') {
            $ms = (new \App\Models\MilestoneModel())->find($order['entity_id']);
            $currency = $ms['currency'] ?? 'INR';
        } else {
            $currency = 'INR';
        }
        $amountStr = currencySymbol($currency) . number_format($order['amount'], 2);
        (new NotificationService())->create(0,'payment_received','Payment Received',$amountStr.' via Razorpay',$pid,'payment');
        (new NotificationService())->createForClient((int) $order['client_id'],'payment_confirmed','Payment Received',"We've received your payment of {$amountStr}. Thank you!",$pid,'payment');

        // ── Send WhatsApp after payment record ────────────────────────
        if (trim((string)($order['client_whatsapp'] ?? '')) !== '') {
            $wa = new WhatsAppNotificationService();
            $invNumber = '';
            if ($order['entity_type'] === 'invoice') {
                $inv = $im->find($order['entity_id']);
                $invNumber = $inv['invoice_number'] ?? '';
            }
            $wa->paymentReceived(
                (string)($order['client_whatsapp'] ?? ''),
                (string)($order['client_name'] ?? ''),
                $invNumber,
                $amountStr
            );
        }
    }

    /**
     * POST webhook/whatsapp — MSG91 inbound callback.
     * Stores the customer's message in chat_messages so it appears
     * in admin/chat like a normal WhatsApp reply. Configure this URL
     * in the MSG91 panel as the inbound/webhook URL.
     * CSRF-exempt by design: verified via shared secret instead.
     */
    public function whatsappIncoming()
    {
        $secret = trim((string) ((new \App\Models\SettingModel())->getSetting('whatsapp_webhook_secret') ?? ''));
        if ($secret !== '') {
            $given = (string) ($this->request->getGet('secret') ?? $this->request->getHeaderLine('X-Webhook-Secret'));
            if (! hash_equals($secret, $given)) {
                return $this->response->setStatusCode(403)->setBody('Forbidden');
            }
        }
        $raw = file_get_contents('php://input');
        $in = json_decode((string) $raw, true);
        if (! is_array($in)) $in = $this->request->getPost() ?: [];
        // Shapes: generic {from,message} / {data:{...}} / MSG91 custom
        // webhook {customerNumber,customerName,text,messageType,url,caption}.
        $data = $in['data'] ?? $in;
        $from = (string) ($data['from'] ?? $data['sender'] ?? $data['phone'] ?? $data['mobile']
            ?? $data['customerNumber'] ?? $data['customer_number'] ?? '');
        $text = '';
        if (isset($data['message'])) {
            $text = is_array($data['message'])
                ? (string) ($data['message']['content']['text'] ?? $data['message']['text'] ?? $data['message']['body'] ?? '')
                : (string) $data['message'];
        }
        $text = trim($text !== '' ? $text : (string) ($data['text'] ?? $data['body'] ?? $data['caption'] ?? ''));
        $mediaUrl = trim((string) ($data['url'] ?? ''));
        $msgType = strtolower((string) ($data['messageType'] ?? $data['contentType'] ?? 'text'));
        // Delivery/status reports carry no text — acknowledge and ignore.
        if ($from === '' || ($text === '' && $mediaUrl === '')) {
            return $this->response->setStatusCode(200)->setBody('OK');
        }
        $digits = preg_replace('/\D/', '', $from);
        if (strlen($digits) === 10) $digits = '91' . $digits;
        // Users table has no phone column — match via clients.whatsapp,
        // then resolve the portal login (users.client_id).
        $last10 = substr($digits, -10);
        $client = $this->db->table('clients')
            ->where("REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(whatsapp,''),'+',''),' ',''),'-',''),'(', '') LIKE", '%' . $last10 . '%')
            ->get()->getRowArray();
        $userId = 0;
        if ($client) {
            $user = $this->db->table('users')->where('client_id', $client['id'])->where('is_active', 1)->orderBy('id', 'ASC')->get()->getRowArray();
            $userId = (int) ($user['id'] ?? 0);
        }
        if ($userId <= 0) {
            $cname = trim((string) ($data['customerName'] ?? $data['customer_name'] ?? ''));
            log_message('info', 'WhatsApp inbound from unknown number: {n} {name}', ['n' => $digits, 'name' => $cname]);
            return $this->response->setStatusCode(200)->setBody('OK');
        }
        $adminId = (int) ($this->db->table('users')->selectMin('id')->whereIn('role', ['superadmin', 'admin'])->get()->getRowArray()['id'] ?? 1);
        $isImage = $mediaUrl !== '' && str_contains($msgType, 'image');
        if ($text === '' && $mediaUrl !== '') $text = trim((string) ($data['caption'] ?? '')) !== '' ? (string) $data['caption'] : '[media]';
        (new \App\Models\ChatMessageModel())->insert([
            'admin_id' => $adminId,
            'user_id' => $userId,
            'message' => mb_substr($text, 0, 2000),
            'image_url' => $isImage ? $mediaUrl : null,
            'message_type' => $isImage ? 'image' : 'text',
            'is_admin' => 0,
            'is_read' => 0,
        ]);
        return $this->response->setStatusCode(200)->setBody('OK');
    }
}
