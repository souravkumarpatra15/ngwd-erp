<?php
namespace App\Services;
use App\Models\SettingModel;

class WhatsAppService
{
    protected $token;
    protected $phoneNumberId;

    public function __construct() {
        $s = (new SettingModel())->getAllSettings();
        $this->token        = $s['whatsapp_token'] ?? '';
        $this->phoneNumberId = $s['whatsapp_phone_id'] ?? '';
    }

    public function sendMessage(string $phone, string $message): bool {
        if (!$this->token || !$this->phoneNumberId) { log_message('error','WhatsApp credentials not set'); return false; }
        $phone = preg_replace('/[^0-9]/','', $phone);
        if (strlen($phone) === 10) $phone = '91'.$phone;
        return $this->post(['messaging_product'=>'whatsapp','to'=>$phone,'type'=>'text','text'=>['body'=>$message]]);
    }

    protected function post(array $payload): bool {
        try {
            $url = "https://graph.facebook.com/v17.0/{$this->phoneNumberId}/messages";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>['Content-Type: application/json',"Authorization: Bearer {$this->token}"]]);
            $res  = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $json = json_decode($res, true);
            if ($code !== 200 || isset($json['error'])) { log_message('error','WhatsApp: '.$res); return false; }
            return true;
        } catch (\Exception $e) { log_message('error','WhatsApp: '.$e->getMessage()); return false; }
    }
}
