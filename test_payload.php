<?php
require 'vendor/autoload.php';
require 'app/Helpers/whatsapp_helper.php';

// Test the payload structure
$to = '919876543210';
$body = 'Test message';

// Simulate what sendSession now produces
$type = 'text';
$content = ['text' => $body, 'preview_url' => true];

$messageContent = [];
switch ($type) {
    case 'text':
        $messageContent = ['text' => ['body' => $content['text'] ?? '', 'preview_url' => $content['preview_url'] ?? true]];
        break;
    case 'image':
    case 'video':
    case 'document':
    case 'audio':
        $messageContent = [$type => $content];
        break;
    case 'interactive':
        $messageContent = ['interactive' => $content];
        break;
    default:
        $messageContent = $content;
}

$message = ['type' => $type, 'content' => $messageContent];

$payload = [
    'integrated_number' => '919593026451',
    'content_type' => $type,
    'to' => $to,
    'recipient_number' => $to,
    'from' => '919593026451',
    'message' => $message,
    'payload' => [
        'to' => $to,
        'recipient_number' => $to,
        'from' => '919593026451',
        'message' => $message,
    ],
];

echo "Generated payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT);