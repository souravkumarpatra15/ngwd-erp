<?php

namespace App\Services;

use App\Models\SettingModel;

/**
 * MSG91 WhatsApp Business API client.
 *
 * Covers BOTH message classes:
 *  - Session messages (inside the 24h customer-service window, no template
 *    needed): text, link preview, image, video, document, audio, reply
 *    buttons, CTA (URL / call) buttons, lists.
 *  - Template messages (start a conversation): approved template + params.
 *
 * Endpoint: POST {base_url}/bulk/  (one endpoint for single + bulk —
 *   per MSG91's own SDK, only the payload shape differs: `to` for a single
 *   recipient, `to_and_components` for bulk)
 *   base_url default: https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message
 * Auth:  header  authkey: <MSG91 auth key>
 * Numbers: country code, digits only, no plus (919876543210).
 *
 * Every send*() returns ['ok'=>bool,'message_id'=>?string,'error'=>?string,'response'=>mixed].
 */
class Msg91WhatsAppService
{
    protected string $authKey;
    protected string $integratedNumber;
    protected string $namespace;
    protected string $language;
    protected string $baseUrl;

    public function __construct(?array $settings = null)
    {
        $settings ??= (new SettingModel())->getAllSettings();
        $this->authKey          = trim((string)($settings['msg91_authkey'] ?? ''));
        $this->integratedNumber = preg_replace('/\D/', '', (string)($settings['msg91_integrated_number'] ?? ''));
        $this->namespace        = trim((string)($settings['msg91_namespace'] ?? ''));
        $this->language         = trim((string)($settings['msg91_language'] ?? 'en')) ?: 'en';
        $this->baseUrl          = rtrim(trim((string)($settings['msg91_base_url'] ?? '')), '/')
            ?: 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message';
    }

    public function isConfigured(): bool
    {
        return $this->authKey !== '' && $this->integratedNumber !== '';
    }

    /** Digits + country code, no plus. 10-digit Indian numbers get 91. */
    public function formatPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', trim($phone));
        if (strlen($digits) === 10) $digits = '91' . $digits;
        return $digits;
    }

    // ── Session: text ────────────────────────────────────────────
    public function sendText(string $to, string $body, bool $previewUrl = true): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (trim($body) === '') return $this->fail('Message body is empty.');
        return $this->send('text', [
            'messaging_product' => 'whatsapp',
            'to'   => $to,
            'type' => 'text',
            'text' => ['body' => $body, 'preview_url' => $previewUrl],
        ]);
    }

    /** Link message = text carrying a URL (preview_url renders the card). */
    public function sendLink(string $to, string $url, string $caption = ''): array
    {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) return $this->fail('Invalid URL for link message.');
        $body = trim($caption) !== '' ? trim($caption) . "\n" . $url : $url;
        return $this->sendText($to, $body, true);
    }

    // ── Session: media ───────────────────────────────────────────
    public function sendImage(string $to, string $link, string $caption = ''): array
    {
        return $this->sendMedia($to, 'image', $link, $caption);
    }

    public function sendVideo(string $to, string $link, string $caption = ''): array
    {
        return $this->sendMedia($to, 'video', $link, $caption);
    }

    public function sendDocument(string $to, string $link, string $filename = '', string $caption = ''): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (!filter_var($link, FILTER_VALIDATE_URL)) return $this->fail('Invalid media URL.');
        $doc = ['link' => $link];
        if (trim($filename) !== '') $doc['filename'] = trim($filename);
        if (trim($caption) !== '') $doc['caption'] = trim($caption);
        return $this->send('document', [
            'messaging_product' => 'whatsapp',
            'to' => $to, 'type' => 'document', 'document' => $doc,
        ]);
    }

    public function sendAudio(string $to, string $link): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (!filter_var($link, FILTER_VALIDATE_URL)) return $this->fail('Invalid media URL.');
        return $this->send('audio', [
            'messaging_product' => 'whatsapp',
            'to' => $to, 'type' => 'audio', 'audio' => ['link' => $link],
        ]);
    }

    protected function sendMedia(string $to, string $type, string $link, string $caption): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (!filter_var($link, FILTER_VALIDATE_URL)) return $this->fail('Invalid media URL.');
        $media = ['link' => $link];
        if (trim($caption) !== '') $media['caption'] = trim($caption);
        return $this->send($type, [
            'messaging_product' => 'whatsapp',
            'to' => $to, 'type' => $type, $type => $media,
        ]);
    }

    // ── Session: interactive ─────────────────────────────────────
    /**
     * Quick-reply buttons (max 3).
     * $buttons: [['id'=>'yes','title'=>'Yes'], ...]
     * $header: null | ['type'=>'text','text'=>..] | ['type'=>'image|video|document','link'=>..]
     */
    public function sendReplyButtons(string $to, string $body, array $buttons, ?array $header = null, string $footer = ''): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        $buttons = array_values(array_filter(array_map(fn($b) => [
            'type'  => 'reply',
            'reply' => ['id' => substr((string)($b['id'] ?? ''), 0, 256), 'title' => substr((string)($b['title'] ?? ''), 0, 20)],
        ], $buttons), fn($b) => $b['reply']['id'] !== '' && $b['reply']['title'] !== ''));
        if (empty($buttons)) return $this->fail('At least one button (id + title) is required.');
        if (count($buttons) > 3) $buttons = array_slice($buttons, 0, 3);
        $interactive = [
            'type'   => 'button',
            'body'   => ['text' => $body],
            'action' => ['buttons' => $buttons],
        ];
        if ($header) $interactive['header'] = $this->normaliseHeader($header);
        if (trim($footer) !== '') $interactive['footer'] = ['text' => $footer];
        return $this->send('interactive', [
            'messaging_product' => 'whatsapp',
            'to' => $to, 'type' => 'interactive', 'interactive' => $interactive,
        ]);
    }

    /**
     * CTA buttons: URL and/or call (max 2, at most one of each kind).
     * $buttons: [['kind'=>'url','title'=>'Pay Now','value'=>'https://....'],
     *           ['kind'=>'phone','title'=>'Call Us','value'=>'+9198...']]
     */
    public function sendCtaButtons(string $to, string $body, array $buttons, ?array $header = null, string $footer = ''): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        $out = []; $seen = [];
        foreach ($buttons as $b) {
            $kind = strtolower((string)($b['kind'] ?? 'url'));
            if (!in_array($kind, ['url', 'phone'], true) || isset($seen[$kind])) continue;
            $title = substr(trim((string)($b['title'] ?? '')), 0, 20);
            $value = trim((string)($b['value'] ?? ''));
            if ($title === '' || $value === '') continue;
            if ($kind === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) continue;
            $btn = ['type' => 'cta_url', 'display_text' => $title];
            if ($kind === 'url') { $btn['url'] = $value; }
            else { $btn['type'] = 'cta_call'; $btn['phone_number'] = $this->formatPhone($value); unset($btn['url']); }
            $out[] = $btn; $seen[$kind] = true;
            if (count($out) === 2) break;
        }
        if (empty($out)) return $this->fail('At least one valid CTA button (url/phone + title + value) is required.');
        $interactive = [
            'type'   => 'cta_url_recommendation',
            'body'   => ['text' => $body],
            'action' => ['name' => 'cta_url', 'parameters' => ['display_text' => $out[0]['display_text'] ?? '', 'url' => $out[0]['url'] ?? '']],
        ];
        // Mixed CTA sets fall back to plain button type with per-button actions.
        if (count($out) > 1 || isset($seen['phone'])) {
            $interactive = ['type' => 'button', 'body' => ['text' => $body], 'action' => ['buttons' => array_map(
                fn($b, $i) => $b['type'] === 'cta_call'
                    ? ['type' => 'cta_call', 'cta_call' => ['display_text' => $b['display_text'], 'phone_number' => $b['phone_number']], 'index' => $i]
                    : ['type' => 'cta_url', 'cta_url' => ['display_text' => $b['display_text'], 'url' => $b['url']], 'index' => $i],
                $out, array_keys($out)
            )]];
        }
        if ($header) $interactive['header'] = $this->normaliseHeader($header);
        if (trim($footer) !== '') $interactive['footer'] = ['text' => $footer];
        return $this->send('interactive', [
            'messaging_product' => 'whatsapp',
            'to' => $to, 'type' => 'interactive', 'interactive' => $interactive,
        ]);
    }

    /**
     * Interactive list (single-select menu).
     * $sections: [['title'=>'Plans','rows'=>[['id'=>'p1','title'=>'Basic','description'=>'..'],...]], ...]
     */
    public function sendList(string $to, string $body, string $buttonText, array $sections, ?array $header = null, string $footer = ''): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        $sections = array_values(array_filter(array_map(function ($s) {
            $rows = array_values(array_filter(array_map(fn($r) => [
                'id' => substr((string)($r['id'] ?? ''), 0, 256),
                'title' => substr((string)($r['title'] ?? ''), 0, 24),
                'description' => substr((string)($r['description'] ?? ''), 0, 72),
            ], (array)($s['rows'] ?? [])), fn($r) => $r['id'] !== '' && $r['title'] !== ''));
            if (empty($rows)) return null;
            return ['title' => substr((string)($s['title'] ?? ''), 0, 24), 'rows' => array_slice($rows, 0, 10)];
        }, $sections)));
        if (empty($sections) || trim($buttonText) === '') return $this->fail('List needs a button label and at least one section with rows.');
        $interactive = [
            'type'   => 'list',
            'body'   => ['text' => $body],
            'action' => ['button' => substr($buttonText, 0, 20), 'sections' => array_slice($sections, 0, 10)],
        ];
        if ($header) $interactive['header'] = $this->normaliseHeader($header);
        if (trim($footer) !== '') $interactive['footer'] = ['text' => $footer];
        return $this->send('interactive', [
            'messaging_product' => 'whatsapp',
            'to' => $to, 'type' => 'interactive', 'interactive' => $interactive,
        ]);
    }

    protected function normaliseHeader(array $header): array
    {
        $type = strtolower((string)($header['type'] ?? 'text'));
        if (in_array($type, ['image', 'video', 'document'], true) && !empty($header['link'])) {
            $media = ['link' => $header['link']];
            if (!empty($header['filename'])) $media['filename'] = $header['filename'];
            return ['type' => $type, $type => $media];
        }
        return ['type' => 'text', 'text' => substr((string)($header['text'] ?? ''), 0, 60)];
    }

    // ── Template (starts a conversation) ─────────────────────────
    /**
     * $bodyValues: ordered {{1}},{{2}}... values.
     * $options: ['language'=>..,'namespace'=>..,'header'=>['type'=>'text|image|video|document','value'=>..,'filename'=>..],
     *           'buttons'=>[['subtype'=>'url|quick_reply|...','value'=>..],...]]
     */
    public function sendTemplate(string $to, string $template, array $bodyValues = [], array $options = []): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        $template = trim($template);
        if ($template === '') return $this->fail('Template name is required.');
        $language  = trim((string)($options['language'] ?? $this->language)) ?: 'en';
        $namespace = trim((string)($options['namespace'] ?? $this->namespace));
        if ($namespace === '') return $this->fail('Template namespace is not configured (Settings → WhatsApp).');

        $components = [];
        if (!empty($options['header'])) {
            $h = $options['header'];
            $components['header_1'] = ['type' => $h['type'] ?? 'text', 'value' => $h['value'] ?? '']
                + (isset($h['filename']) ? ['filename' => $h['filename']] : []);
        }
        foreach (array_values($bodyValues) as $i => $v) {
            $components['body_' . ($i + 1)] = ['type' => 'text', 'value' => (string)$v];
        }
        foreach (array_values((array)($options['buttons'] ?? [])) as $i => $b) {
            if (empty($b['value'])) continue;
            $components['button_' . ($i + 1)] = array_filter([
                'subtype' => $b['subtype'] ?? 'url',
                'type'    => 'text',
                'value'   => (string)$b['value'],
            ]);
        }
        // to_and_components is the bulk shape; single-recipient session
        // calls use `to`. Both go to the same /bulk/ endpoint.
        return $this->send('template', [
            'messaging_product' => 'whatsapp',
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $language, 'policy' => 'deterministic'],
                'namespace' => $namespace,
                'to_and_components' => [['to' => [$to], 'components' => $components]],
            ],
        ]);
    }

    /** Fetch approved templates linked to the integrated number (best effort). */
    public function fetchTemplates(): array
    {
        if (!$this->isConfigured()) return $this->fail('MSG91 is not configured (auth key / integrated number).');
        $url = 'https://api.msg91.com/api/v5/whatsapp/whatsapp-template?integrated_number=' . urlencode($this->integratedNumber);
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => ['authkey: ' . $this->authKey, 'Accept: application/json'],
                CURLOPT_TIMEOUT => 30,
            ]);
            $raw = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch); curl_close($ch);
            if ($err) return $this->fail('CURL error: ' . $err);
            $data = json_decode((string)$raw, true);
            if ($code < 200 || $code >= 300) return $this->fail('MSG91 error [' . $code . ']: ' . substr((string)$raw, 0, 500));
            return ['ok' => true, 'message_id' => null, 'error' => null, 'response' => $data];
        } catch (\Throwable $e) {
            return $this->fail('Exception: ' . $e->getMessage());
        }
    }

    // ── Transport ────────────────────────────────────────────────
    protected function precheck(string $to, ?string &$err): bool
    {
        if (!$this->isConfigured()) { $err = 'MSG91 is not configured (Settings → WhatsApp → Auth Key + Integrated Number).'; return false; }
        if (strlen($to) < 10) { $err = 'Invalid WhatsApp number.'; return false; }
        $err = null; return true;
    }

    protected function fail(string $error): array
    {
        log_message('error', 'MSG91 WhatsApp: ' . $error);
        return ['ok' => false, 'message_id' => null, 'error' => $error, 'response' => null];
    }

    protected function send(string $contentType, array $payload): array
    {
        $body = ['integrated_number' => $this->integratedNumber, 'content_type' => $contentType, 'payload' => $payload];
        $url  = $this->baseUrl . '/bulk/';
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', 'authkey: ' . $this->authKey],
                CURLOPT_TIMEOUT => 30,
            ]);
            $raw = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch); curl_close($ch);
            if ($curlErr) return $this->fail('CURL error: ' . $curlErr);
            $data = json_decode((string)$raw, true);
            $ok = $code >= 200 && $code < 300 && (!isset($data['status']) || strtolower((string)$data['status']) !== 'error');
            $result = [
                'ok' => $ok,
                'message_id' => $data['message_id'] ?? $data['request_id'] ?? null,
                'error' => $ok ? null : ('MSG91 error [' . $code . ']: ' . substr((string)$raw, 0, 500)),
                'response' => $data ?? $raw,
            ];
            if (!$ok) log_message('error', 'MSG91 WhatsApp API Error [' . $url . ']: ' . $result['error']);
            return $result;
        } catch (\Throwable $e) {
            return $this->fail('Exception: ' . $e->getMessage());
        }
    }
}
