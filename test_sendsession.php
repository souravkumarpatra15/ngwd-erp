<?php
require 'vendor/autoload.php';
require 'app/Helpers/whatsapp_helper.php';

// Test the payload structure with actual sendText call
$settings = [
    'msg91_authkey' => 'test',
    'msg91_integrated_number' => '919593026451',
    'msg91_namespace' => '',
    'msg91_language' => 'en',
    'msg91_base_url' => 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message',
    'msg91_session_base_url' => 'https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message',
];

$svc = new \App\Services\Msg91WhatsAppService($settings);

// Use reflection to call protected method
$reflection = new ReflectionClass($svc);
$method = $reflection->getMethod('sendSession');
$method->setAccessible(true);

$result = $method->invoke($svc, '918172010250', 'text', ['text' => 'hi', 'preview_url' => true]);

echo "Result:\n";
print_r($result);