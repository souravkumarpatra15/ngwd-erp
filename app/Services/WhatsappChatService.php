<?php

namespace App\Services;

use App\Models\WhatsappConversationModel;
use App\Models\WhatsappMessageModel;

/**
 * Canonical WhatsApp conversation backend (MSG91 stays the only provider).
 *
 * Outbound: insert queued → provider → sent/failed + provider id.
 * Inbound:  normalize → idempotent insert → link client/lead → unread++.
 */
class WhatsappChatService
{
    public const TEMPLATES = [
        'erp_client_welcome'      => ['Client name', 'Username', 'Password', 'Login link'],
        'erp_lead_created2'       => ['Staff name', 'Lead name'],
        'erp_lead_followup'       => ['Staff name', 'Follow-up date'],
        'erp_proposal_sent'       => ['Client name', 'Proposal number', 'Amount'],
        'erp_agreement_sent'      => ['Client name', 'Agreement note'],
        'erp_project_created'     => ['Client name', 'Project name'],
        'erp_milestone_updated'   => ['Client name', 'Project name', 'Milestone name', 'Status'],
        'erp_milestone_due'       => ['Client name', 'Milestone name', 'Due date'],
        'erp_invoice_created'     => ['Client name', 'Invoice number', 'Amount'],
        'erp_invoice_sent2'       => ['Client name', 'Invoice number', 'Amount', 'Due date', 'Company name'],
        'erp_invoice_reminder'    => ['Client name', 'Invoice number', 'Amount', 'Due date'],
        'erp_invoice_overdue'     => ['Client name', 'Invoice number', 'Amount'],
        'erp_payment_received'    => ['Client name', 'Invoice number', 'Amount'],
        'erp_task_assigned'       => ['Assignee name', 'Task name', 'Due date'],
        'erp_general_notification' => ['Recipient name', 'Message'],
    ];

    protected WhatsappConversationModel $convs;
    protected WhatsappMessageModel $msgs;

    public function __construct()
    {
        $this->convs = new WhatsappConversationModel();
        $this->msgs = new WhatsappMessageModel();
    }

    public function normalize(string $phone): string
    {
        return function_exists('normalizeWhatsAppPhone') ? normalizeWhatsAppPhone($phone) : wa_to($phone);
    }

    /**
     * client → lead → unknown (client wins when both match).
     * @return array{client_id:?int,lead_id:?int,name:string,type:string}
     */
    public function resolveContact(string $phone, string $fallbackName = ''): array
    {
        $db = \Config\Database::connect();
        $last10 = substr($phone, -10);
        $like = "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(%s,''),'+',''),' ',''),'-',''),'(', '') LIKE";
        $c = $db->table('clients')
            ->groupStart()
                ->where(sprintf($like, 'whatsapp') . " '%" . $db->escapeLikeString($last10) . "%'", null, false)
                ->orWhere(sprintf($like, 'phone') . " '%" . $db->escapeLikeString($last10) . "%'", null, false)
            ->groupEnd()
            ->get()->getRowArray();
        if ($c) {
            return ['client_id' => (int) $c['id'], 'lead_id' => null, 'name' => (string) ($c['name'] ?? $fallbackName), 'type' => 'client'];
        }
        $l = $db->table('leads')
            ->groupStart()
                ->where(sprintf($like, 'whatsapp') . " '%" . $db->escapeLikeString($last10) . "%'", null, false)
                ->orWhere(sprintf($like, 'mobile') . " '%" . $db->escapeLikeString($last10) . "%'", null, false)
            ->groupEnd()
            ->get()->getRowArray();
        if ($l) {
            return ['client_id' => null, 'lead_id' => (int) $l['id'], 'name' => (string) ($l['name'] ?? $fallbackName), 'type' => 'lead'];
        }
        return ['client_id' => null, 'lead_id' => null, 'name' => $fallbackName !== '' ? $fallbackName : 'Unknown Contact', 'type' => 'unknown'];
    }

    public function getOrCreateConversation(string $phone, string $name = '', string $integratedNumber = ''): ?array
    {
        if ($phone === '') return null;
        $conv = null;
        if ($integratedNumber !== '') {
            $conv = $this->convs->where('integrated_number', $integratedNumber)->where('phone_number', $phone)->first();
        }
        if (! $conv) $conv = $this->convs->findByPhone($phone);
        if ($conv) {
            // Backfill link when an unknown contact later matches CRM data.
            if (($conv['contact_type'] ?? 'unknown') === 'unknown') {
                $r = $this->resolveContact($phone, $name);
                if ($r['type'] !== 'unknown') {
                    $this->convs->update($conv['id'], [
                        'client_id' => $r['client_id'], 'lead_id' => $r['lead_id'],
                        'contact_name' => $r['name'], 'contact_type' => $r['type'],
                    ]);
                    $conv = $this->convs->find($conv['id']);
                }
            }
            return $conv;
        }
        $r = $this->resolveContact($phone, $name);
        $id = $this->convs->insert([
            'client_id' => $r['client_id'], 'lead_id' => $r['lead_id'], 'phone_number' => $phone,
            'contact_name' => $r['name'], 'contact_type' => $r['type'], 'status' => 'open',
            'integrated_number' => $integratedNumber !== '' ? $integratedNumber : null,
        ], true);
        return $id ? $this->convs->find($id) : null;
    }

    // ── Outbound ────────────────────────────────────────────────

    protected function queue(int $convId, string $phone, string $type, ?string $text, array $extra, ?int $createdBy): int
    {
        return (int) $this->msgs->insert([
            'conversation_id' => $convId, 'phone_number' => $phone, 'direction' => 'outbound',
            'message_type' => $type, 'message_text' => $text, 'created_by' => $createdBy, 'status' => 'queued',
        ] + $extra, true);
    }

    protected function settle(int $msgId, int $convId, string $preview, array $res): array
    {
        if (! empty($res['ok'])) {
            $this->msgs->setStatus($msgId, 'sent', [
                'provider_message_id' => $res['message_id'] ?? null,
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
            $this->convs->touch($convId, $preview, 'outbound', false);
            return ['ok' => true, 'message' => $this->msgs->formatRow($this->msgs->find($msgId))];
        }
        $err = (string) ($res['error'] ?? 'Send failed.');
        $this->msgs->setStatus($msgId, 'failed', ['error_message' => mb_substr($err, 0, 450)]);
        log_message('error', 'WhatsApp chat send failed: ' . $err);
        return ['ok' => false, 'message' => $this->msgs->formatRow($this->msgs->find($msgId)), 'error' => 'Unable to send message. Please try again.'];
    }

    public function sendText(int $convId, string $text, ?int $createdBy = null): array
    {
        $conv = $this->convs->find($convId);
        $text = trim($text);
        if (! $conv || $text === '') return ['ok' => false, 'error' => 'Message required.'];
        if (mb_strlen($text) > 2000) return ['ok' => false, 'error' => 'Message too long (max 2000 chars).'];
        $msgId = $this->queue($convId, (string) $conv['phone_number'], 'text', $text, [], $createdBy);
        if (! $msgId) return ['ok' => false, 'error' => 'Unable to queue message.'];
        return $this->settle($msgId, $convId, $text, (new Msg91WhatsAppService())->sendText((string) $conv['phone_number'], $text));
    }

    public function sendImage(int $convId, string $publicUrl, string $caption, ?int $createdBy = null): array
    {
        $conv = $this->convs->find($convId);
        if (! $conv || ! filter_var($publicUrl, FILTER_VALIDATE_URL)) return ['ok' => false, 'error' => 'Valid image URL required.'];
        $msgId = $this->queue($convId, (string) $conv['phone_number'], 'image', $caption !== '' ? $caption : null,
            ['media_url' => $publicUrl, 'media_type' => 'image'], $createdBy);
        if (! $msgId) return ['ok' => false, 'error' => 'Unable to queue message.'];
        return $this->settle($msgId, $convId, $caption !== '' ? $caption : '[image]',
            (new Msg91WhatsAppService())->sendImage((string) $conv['phone_number'], $publicUrl, $caption));
    }

    public function sendTemplate(int $convId, string $template, array $params, ?int $createdBy = null, array $options = []): array
    {
        $conv = $this->convs->find($convId);
        if (! $conv) return ['ok' => false, 'error' => 'Conversation not found.'];
        if (! isset(self::TEMPLATES[$template])) return ['ok' => false, 'error' => 'Unknown template.'];
        $labels = self::TEMPLATES[$template];
        if (count($params) < count($labels)) return ['ok' => false, 'error' => 'All template variables are required.'];
        $params = array_values(array_map(static fn($v) => (string) $v, array_slice($params, 0, count($labels))));
        if (! function_exists('wa_send_template')) return ['ok' => false, 'error' => 'WhatsApp helper unavailable.'];
        $msgId = $this->queue($convId, (string) $conv['phone_number'], 'template', null, [
            'template_name' => $template, 'template_params' => json_encode($params),
        ], $createdBy);
        if (! $msgId) return ['ok' => false, 'error' => 'Unable to queue message.'];
        $preview = $template . ': ' . implode(' · ', $params);
        return $this->settle($msgId, $convId, $preview, wa_send_template((string) $conv['phone_number'], wa_template($template, $params, $options)));
    }

    public function retry(int $msgId): array
    {
        $m = $this->msgs->find($msgId);
        if (! $m || ($m['direction'] ?? '') !== 'outbound' || ($m['status'] ?? '') !== 'failed') {
            return ['ok' => false, 'error' => 'Only failed outgoing messages can be retried.'];
        }
        $conv = $this->convs->find((int) $m['conversation_id']);
        if (! $conv) return ['ok' => false, 'error' => 'Conversation not found.'];
        $svc = new Msg91WhatsAppService();
        $res = match ($m['message_type'] ?? 'text') {
            'image' => $svc->sendImage((string) $conv['phone_number'], (string) ($m['media_url'] ?? ''), (string) ($m['message_text'] ?? '')),
            'template' => function_exists('wa_send_template')
                ? wa_send_template((string) $conv['phone_number'], wa_template((string) ($m['template_name'] ?? ''), json_decode((string) ($m['template_params'] ?? '[]'), true) ?: []))
                : ['ok' => false, 'error' => 'WhatsApp helper unavailable.'],
            default => $svc->sendText((string) $conv['phone_number'], (string) ($m['message_text'] ?? '')),
        };
        return $this->settle($msgId, (int) $conv['id'], (string) ($m['message_text'] ?? $m['template_name'] ?? '[message]'), $res);
    }

    // ── Inbound ─────────────────────────────────────────────────

    /** Store an inbound message. Returns conversation id or null (reports/empty). */
    public function inbound(array $payload): ?int
    {
        if (! function_exists('normalizeInboundWhatsAppMessage')) {
            log_message('error', 'WhatsApp inbound: normalizer unavailable.');
            return null;
        }
        $n = normalizeInboundWhatsAppMessage($payload);
        // Status callbacks resolve against previously sent messages.
        if ($n['is_report']) {
            $this->applyStatusCallback($payload);
            return null;
        }
        if ($n['phone_number'] === '') {
            log_message('warning', 'WhatsApp inbound ignored: no sender phone in payload.');
            return null;
        }
        // Idempotency: provider may redeliver the same event.
        if ($n['provider_message_id'] !== '' && $this->msgs->findByProviderId($n['provider_message_id'])) {
            log_message('info', 'WhatsApp inbound duplicate ignored: {mid}', ['mid' => $n['provider_message_id']]);
            return null;
        }
        $conv = $this->getOrCreateConversation($n['phone_number'], $n['contact_name'], $n['integrated_number']);
        if (! $conv) {
            log_message('error', 'WhatsApp inbound: conversation create failed for {phone}', ['phone' => $n['phone_number']]);
            return null;
        }
        $mediaKind = in_array($n['message_type'], ['image', 'document', 'audio', 'video'], true) ? $n['message_type'] : null;
        $isImage = $mediaKind === 'image' && $n['media_url'] !== '';
        $text = $n['message_text'] !== '' ? $n['message_text'] : ($n['media_url'] !== '' ? '[' . ($mediaKind ?? $n['message_type']) . ']' : '');
        $newId = $this->msgs->insert([
            'conversation_id' => (int) $conv['id'], 'provider_message_id' => $n['provider_message_id'] !== '' ? $n['provider_message_id'] : null,
            'phone_number' => $n['phone_number'], 'direction' => 'inbound',
            'message_type' => $isImage ? 'image' : ($mediaKind ?? $n['message_type']), 'message_text' => mb_substr($text, 0, 2000),
            'media_url' => $n['media_url'] !== '' ? $n['media_url'] : null, 'media_type' => $mediaKind,
            'raw_payload' => json_encode($n['raw_payload']),
            'status' => 'received',
        ]);
        $msgId = (int) $newId;
        if ($msgId <= 0) throw new \RuntimeException('Message insert failed.');
        log_message('info', 'WhatsApp inbound stored: conv={conv} msg={msg} phone={phone}', [
            'conv' => (int) $conv['id'], 'msg' => $msgId,
            'phone' => function_exists('maskPhone') ? maskPhone($n['phone_number']) : $n['phone_number'],
        ]);
        $this->convs->touch((int) $conv['id'], $text !== '' ? $text : '[message]', 'inbound', true);
        return (int) $conv['id'];
    }

    /** Best-effort mapping of provider delivery reports onto sent messages. */
    public function applyStatusCallback(array $payload): void
    {
        $data = $payload['data'] ?? $payload;
        $pid = (string) ($data['requestId'] ?? $data['campaignRequestId'] ?? $data['message_id'] ?? $data['uuid'] ?? '');
        if ($pid === '') return;
        $m = $this->msgs->findByProviderId($pid);
        if (! $m) return; // sent outside chat (e.g. notification templates) — nothing to update
        $s = strtolower((string) ($data['status'] ?? $data['paymentStatus'] ?? $data['reason'] ?? ''));
        $now = date('Y-m-d H:i:s');
        if (str_contains($s, 'read')) $this->msgs->setStatus((int) $m['id'], 'read', ['read_at' => $now]);
        elseif (str_contains($s, 'deliver')) $this->msgs->setStatus((int) $m['id'], 'delivered', ['delivered_at' => $now]);
        elseif (str_contains($s, 'fail') || str_contains($s, 'error') || str_contains($s, 'reject')) {
            $this->msgs->setStatus((int) $m['id'], 'failed', ['error_message' => mb_substr($s, 0, 450)]);
        } elseif (str_contains($s, 'sent') || str_contains($s, 'submit')) {
            $this->msgs->setStatus((int) $m['id'], 'sent', ['sent_at' => $now]);
        }
    }
}
