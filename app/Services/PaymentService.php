<?php
namespace App\Services;
use App\Models\SettingModel;
use App\Models\RazorpayOrderModel;

class PaymentService
{
    protected $key;
    protected $secret;
    protected $base = 'https://api.razorpay.com/v1';

    public function __construct() {
        $s = (new SettingModel())->getAllSettings();
        $this->key    = $s['razorpay_key'] ?? '';
        $this->secret = $s['razorpay_secret'] ?? '';
    }

    public function createOrder(float $amount, string $type, int $entityId, int $clientId): ?array {
        $res = $this->call('POST','/orders',['amount'=>(int)($amount*100),'currency'=>'INR','notes'=>['entity_type'=>$type,'entity_id'=>$entityId,'client_id'=>$clientId]]);
        if ($res && isset($res['id'])) {
            (new RazorpayOrderModel())->insert(['order_id'=>$res['id'],'entity_type'=>$type,'entity_id'=>$entityId,'client_id'=>$clientId,'amount'=>$amount,'status'=>'created']);
            return $res;
        }
        return null;
    }

    public function verifyPayment(string $orderId, string $payId, string $sig): bool {
        return hash_equals(hash_hmac('sha256', $orderId.'|'.$payId, $this->secret), $sig);
    }

    protected function call(string $method, string $ep, array $data=[]): ?array {
        try {
            $ch = curl_init($this->base.$ep);
            curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>$this->key.':'.$this->secret,CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
            if ($method==='POST'){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));}
            $res = curl_exec($ch); curl_close($ch);
            return json_decode($res, true);
        } catch(\Exception $e){log_message('error','Razorpay: '.$e->getMessage());return null;}
    }
}
