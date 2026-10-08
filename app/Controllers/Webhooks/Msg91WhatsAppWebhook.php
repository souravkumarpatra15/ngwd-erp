<?php

namespace App\Controllers\Webhooks;

use App\Controllers\BaseController;
use App\Services\WhatsappChatService;

/**
 * MSG91 WhatsApp webhook — inbound messages + outbound status reports.
 *
 * Route: POST /webhooks/msg91/whatsapp (no login session; secret-gated).
 * Shares the canonical WhatsappChatService backend with the CRM inbox,
 * so every accepted event lands in the same conversations the chat UI reads.
 */
class Msg91WhatsAppWebhook extends BaseController
{
    public function index()
    {
        // Alive-check: open the URL in a browser → 200 means the route is
        // deployed; 404 means live hasn't pulled the webhook code yet.
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(200)->setJSON(['success' => true, 'alive' => true, 'endpoint' => 'webhooks/msg91/whatsapp']);
        }

        // Secret: .env first (never in source), settings row as fallback,
        // then the shared ?secret= / X-Webhook-Secret transport.
        $secret = trim((string) (env('MSG91_WEBHOOK_SECRET') ?? ''));
        if ($secret === '') {
            try {
                $secret = trim((string) ((new \App\Models\SettingModel())->getSetting('whatsapp_webhook_secret') ?? ''));
            } catch (\Throwable $e) {
                $secret = '';
            }
        }
        if ($secret !== '') {
            $given = (string) ($this->request->getGet('secret') ?? $this->request->getHeaderLine('X-Webhook-Secret') ?? $this->request->getHeaderLine('X-MSG91-WEBHOOK-SECRET'));
            if (! hash_equals($secret, $given)) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'error' => 'Forbidden.']);
            }
        }

        $raw = file_get_contents('php://input');
        $in = json_decode((string) $raw, true);
        if (! is_array($in) || $in === []) $in = $this->request->getPost() ?: [];
        if ($in === []) {
            log_message('warning', 'MSG91 WhatsApp webhook: empty payload.');
            return $this->response->setStatusCode(200)->setJSON(['success' => false, 'error' => 'Empty payload.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $svc = new WhatsappChatService();
            $n = function_exists('normalizeInboundWhatsAppMessage') ? normalizeInboundWhatsAppMessage($in) : null;
            if ($n === null) throw new \RuntimeException('Normalizer unavailable.');
            log_message('info', 'MSG91 WhatsApp webhook received direction={dir} phone={phone} message_id={mid} event={event}', [
                'dir' => $n['direction'],
                'phone' => function_exists('maskPhone') ? maskPhone($n['phone_number']) : $n['phone_number'],
                'mid' => $n['provider_message_id'] !== '' ? $n['provider_message_id'] : '(none)',
                'event' => $n['event'] !== '' ? $n['event'] : '(none)',
            ]);
            $convId = $svc->inbound($in);
            if (! $db->transStatus()) throw new \RuntimeException('Transaction failed.');
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'MSG91 WhatsApp webhook failed: {message}', ['message' => $e->getMessage()]);
            // 200 (not 500): never cause provider retry storms for our errors.
            return $this->response->setStatusCode(200)->setJSON(['success' => false, 'error' => 'Processing failed.']);
        }

        if ($convId === null) {
            // Status report applied, duplicate, or content-less event.
            return $this->response->setStatusCode(200)->setJSON(['success' => true, 'duplicate' => true]);
        }
        return $this->response->setStatusCode(200)->setJSON(['success' => true, 'conversation_id' => $convId]);
    }
}
