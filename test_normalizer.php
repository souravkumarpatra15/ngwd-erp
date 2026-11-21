<?php
require 'vendor/autoload.php';
require 'app/Helpers/whatsapp_helper.php';

$payload = json_decode('{
  "companyId": "509026",
  "requestedAt": "2026-11-02T06:18:28+05:30",
  "customerNumber": "917001767624",
  "bsuid": "IN.1600417845018220",
  "uuid": "wamid.HBgMOTE3MDAxNzY3NjI0FQIAEhggQTU1QThGQUVFNDdDOUI4RjVCQjMxQUIzMDhDQUI2OTAA",
  "integratedNumber": "919593026451",
  "customerName": "Kooheli Astrologyy of Resshmi",
  "contentType": "text",
  "text": "Thank you for contacting Kooheli Astrologyy of Resshmi!",
  "messages": "[{\"from\":\"917001767624\",\"from_user_id\":\"IN.1600417845018220\",\"id\":\"wamid.HBgMOTE3MDAxNzY3NjI0FQIAEhggQTU1QThGQUVFNDdDOUI4RjVCQjMxQUIzMDhDQUI2OTAA\",\"timestamp\":\"1793580506\",\"text\":{\"body\":\"Thank you for contacting Kooheli Astrologyy of Resshmi!\"},\"type\":\"text\"}]",
  "ts": "2026-11-02T06:20:30+05:30"
}', true);

if (function_exists('normalizeInboundWhatsAppMessage')) {
    $n = normalizeInboundWhatsAppMessage($payload);
    print_r($n);
    echo "\n--- is_report: " . ($n['is_report'] ? 'true' : 'false') . "\n";
    echo "--- direction: " . $n['direction'] . "\n";
    echo "--- phone: " . $n['phone_number'] . "\n";
    echo "--- text: " . $n['message_text'] . "\n";
} else {
    echo 'Normalizer not loaded';
}