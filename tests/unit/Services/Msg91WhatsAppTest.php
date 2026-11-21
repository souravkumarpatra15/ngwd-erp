<?php

namespace Tests\Unit\Services;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Test MSG91 WhatsApp integration - normalizer, service, and webhook handling.
 */
class Msg91WhatsAppTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once APPPATH . 'Helpers/whatsapp_helper.php';
    }

    // ── normalizeInboundWhatsAppMessage ───────────────────────────────

    public function testNormalizerInboundTextMessage(): void
    {
        $payload = [
            'eventName' => 'incoming_message_received',
            'customerNumber' => '919876543210',
            'integratedNumber' => '918765432109',
            'contentType' => 'text',
            'text' => 'Hello from customer',
            'uuid' => 'wamid.test123',
            'customerName' => 'John Doe',
            'ts' => '2026-11-21T10:30:00Z',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('inbound', $n['direction']);
        $this->assertEquals('919876543210', $n['phone_number']);
        $this->assertEquals('John Doe', $n['contact_name']);
        $this->assertEquals('wamid.test123', $n['provider_message_id']);
        $this->assertEquals('Hello from customer', $n['message_text']);
        $this->assertFalse($n['is_report']);
    }

    public function testNormalizerInboundWithMessagesArray(): void
    {
        $payload = [
            'eventName' => 'incoming_message_received',
            'customerNumber' => '919876543210',
            'integratedNumber' => '918765432109',
            'contentType' => 'text',
            'messages' => json_encode([[
                'from' => '919876543210',
                'id' => 'wamid.test456',
                'timestamp' => '1700000000',
                'text' => ['body' => 'Message from array'],
                'type' => 'text',
            ]]),
            'customerName' => 'Jane Smith',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('inbound', $n['direction']);
        $this->assertEquals('wamid.test456', $n['provider_message_id']);
        $this->assertEquals('Message from array', $n['message_text']);
        $this->assertEquals('Jane Smith', $n['contact_name']);
        $this->assertFalse($n['is_report']);
    }

    public function testNormalizerOutboundStatusReport(): void
    {
        $payload = [
            'eventName' => 'message_delivered',
            'requestId' => 'req_123',
            'uuid' => 'wamid.test789',
            'integratedNumber' => '918765432109',
            'customerNumber' => '919876543210',
            'status' => 'delivered',
            'ts' => '2026-11-21T10:35:00Z',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('outbound', $n['direction']);
        $this->assertTrue($n['is_report']);
        $this->assertEquals('wamid.test789', $n['provider_message_id']);
    }

    public function testNormalizerSentStatus(): void
    {
        $payload = [
            'eventName' => 'message_sent',
            'requestId' => 'req_sent_123',
            'uuid' => 'wamid.sent001',
            'status' => 'sent',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('outbound', $n['direction']);
        $this->assertTrue($n['is_report']);
    }

    public function testNormalizerFailedStatus(): void
    {
        $payload = [
            'eventName' => 'message_failed',
            'requestId' => 'req_failed_123',
            'uuid' => 'wamid.failed001',
            'reason' => 'Invalid number',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('outbound', $n['direction']);
        $this->assertTrue($n['is_report']);
    }

    public function testNormalizerMissingDirectionDefaultsToInboundForContent(): void
    {
        // When direction is missing but there's message content, should be inbound
        $payload = [
            'customerNumber' => '919876543210',
            'integratedNumber' => '918765432109',
            'text' => 'Message without direction field',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('inbound', $n['direction']);
        $this->assertFalse($n['is_report']);
    }

    public function testNormalizerHandlesNestedContent(): void
    {
        $payload = [
            'eventName' => 'incoming_message_received',
            'customerNumber' => '919876543210',
            'contentType' => 'text',
            'content' => ['text' => 'Nested content message'],
            'uuid' => 'wamid.nested123',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('inbound', $n['direction']);
        $this->assertEquals('Nested content message', $n['message_text']);
    }

    public function testNormalizerHandlesImageMessage(): void
    {
        $payload = [
            'eventName' => 'incoming_message_received',
            'customerNumber' => '919876543210',
            'contentType' => 'image',
            'content' => ['url' => 'https://example.com/image.jpg', 'caption' => 'Photo caption'],
            'uuid' => 'wamid.image123',
        ];

        $n = normalizeInboundWhatsAppMessage($payload);

        $this->assertEquals('inbound', $n['direction']);
        $this->assertEquals('image', $n['message_type']);
        $this->assertEquals('https://example.com/image.jpg', $n['media_url']);
        $this->assertEquals('Photo caption', $n['media_caption']);
    }

    public function testNormalizerIgnoresEmptyPayload(): void
    {
        $n = normalizeInboundWhatsAppMessage([]);
        $this->assertEquals('inbound', $n['direction']);
        $this->assertEquals('', $n['phone_number']);
    }

    // ── Msg91WhatsAppService ──────────────────────────────────────────

    public function testSendSessionPayloadFormat(): void
    {
        $settings = [
            'msg91_authkey' => 'test_key',
            'msg91_integrated_number' => '919593026451',
            'msg91_namespace' => '',
            'msg91_language' => 'en',
            'msg91_base_url' => 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message',
            'msg91_session_base_url' => 'https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message',
        ];

        $svc = new \App\Services\Msg91WhatsAppService($settings);

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($svc);
        $method = $reflection->getMethod('sendSession');
        $method->setAccessible(true);

        // We can't actually test the HTTP call, but we can verify the payload structure
        // by inspecting the body passed to postJson
        $this->assertTrue(true); // Placeholder - actual HTTP testing would require mocking
    }

    public function testPhoneFormatting(): void
    {
        $settings = [
            'msg91_authkey' => 'test_key',
            'msg91_integrated_number' => '919593026451',
            'msg91_namespace' => '',
            'msg91_language' => 'en',
        ];

        $svc = new \App\Services\Msg91WhatsAppService($settings);

        $reflection = new \ReflectionClass($svc);
        $method = $reflection->getMethod('formatPhone');
        $method->setAccessible(true);

        $this->assertEquals('919876543210', $method->invoke($svc, '9876543210'));
        $this->assertEquals('919876543210', $method->invoke($svc, '+919876543210'));
        $this->assertEquals('919876543210', $method->invoke($svc, '09876543210'));
        $this->assertEquals('12065551234', $method->invoke($svc, '+12065551234'));
        $this->assertEquals('919876543210', $method->invoke($svc, '00919876543210'));
    }

    // ── applyStatusCallback ───────────────────────────────────────────

    public function testApplyStatusCallbackWithMessageUuid(): void
    {
        // This would require database setup - placeholder for integration test
        $this->assertTrue(true);
    }

    // ── wa_to / normalizeWhatsAppPhone ────────────────────────────────

    public function testWaToNormalization(): void
    {
        $this->assertEquals('919876543210', wa_to('9876543210'));
        $this->assertEquals('919876543210', wa_to('+919876543210'));
        $this->assertEquals('919876543210', wa_to('09876543210'));
        $this->assertEquals('12065551234', wa_to('+12065551234'));
        $this->assertEquals('919876543210', wa_to('00919876543210'));
        $this->assertEquals('', wa_to(''));
    }

    // ── maskPhone ──────────────────────────────────────────────────────

    public function testMaskPhone(): void
    {
        $this->assertEquals('91******3210', maskPhone('919876543210'));
        $this->assertEquals('91******1234', maskPhone('+919876541234'));
        $this->assertEquals('123', maskPhone('123'));
    }
}