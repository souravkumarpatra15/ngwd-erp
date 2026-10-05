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
 * Session messages go to a DIFFERENT endpoint + envelope:
 *   POST {session_base_url}/  {"to","from","message":{"type","content"}}
 *   session_base_url default: https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message
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
    protected string $sessionBaseUrl;

    public function __construct(?array $settings = null)
    {
        $settings ??= (new SettingModel())->getAllSettings();
        $this->authKey          = trim((string)($settings['msg91_authkey'] ?? ''));
        $this->integratedNumber = preg_replace('/\D/', '', (string)($settings['msg91_integrated_number'] ?? ''));
        $this->namespace        = trim((string)($settings['msg91_namespace'] ?? ''));
        $this->language         = trim((string)($settings['msg91_language'] ?? 'en')) ?: 'en';
        $this->baseUrl          = rtrim(trim((string)($settings['msg91_base_url'] ?? '')), '/')
            ?: 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message';
        $this->sessionBaseUrl   = rtrim(trim((string)($settings['msg91_session_base_url'] ?? '')), '/')
            ?: 'https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message';
    }

    public function isConfigured(): bool
    {
        return $this->authKey !== '' && $this->integratedNumber !== '';
    }

    /**
     * Digits + country code, no plus (919876543210 / 12065551212).
     * - Strips international call prefixes (00.., 011..) and single trunk 0.
     * - Bare 10-digit numbers default to India (91) for BC — store USA/
     *   other countries WITH their country code (e.g. +1 206...).
     * - 11-digit numbers starting with 1 (NANP: USA/CA) are kept as-is.
     * - NEVER prepends a bogus "0"/"01": MSG91 + WhatsApp want pure E.164.
     */
    public function formatPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', trim($phone));
        if ($digits === '') return '';
        // 00<cc>... (INTL prefix used in IN/EU) -> <cc>...
        if (strlen($digits) > 12 && str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) > 11 && str_starts_with($digits, '011')) {
            // 011<cc>... (NANP exit code, e.g. 01191... dialled from USA) -> <cc>...
            $candidate = substr($digits, 3);
            if (strlen($candidate) >= 10 && strlen($candidate) <= 15) $digits = $candidate;
        }
        // Single trunk zero: 09876543210 -> 9876543210
        if (strlen($digits) === 11 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) === 10) $digits = '91' . $digits;
        return $digits;
    }

    // ── Session: text ────────────────────────────────────────────
    public function sendText(string $to, string $body, bool $previewUrl = true): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (trim($body) === '') return $this->fail('Message body is empty.');
        return $this->sendSession($to, 'text', ['text' => $body, 'preview_url' => $previewUrl]);
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
        $doc = ['url' => $link];
        if (trim($filename) !== '') $doc['filename'] = trim($filename);
        if (trim($caption) !== '') $doc['caption'] = trim($caption);
        return $this->sendSession($to, 'document', $doc);
    }

    public function sendAudio(string $to, string $link): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (!filter_var($link, FILTER_VALIDATE_URL)) return $this->fail('Invalid media URL.');
        return $this->sendSession($to, 'audio', ['url' => $link]);
    }

    protected function sendMedia(string $to, string $type, string $link, string $caption): array
    {
        $to = $this->formatPhone($to);
        if (!$this->precheck($to, $err)) return $this->fail($err);
        if (!filter_var($link, FILTER_VALIDATE_URL)) return $this->fail('Invalid media URL.');
        $media = ['url' => $link];
        if (trim($caption) !== '') $media['caption'] = trim($caption);
        return $this->sendSession($to, $type, $media);
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
        return $this->sendSession($to, 'interactive', $interactive);
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
        return $this->sendSession($to, 'interactive', $interactive);
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
        return $this->sendSession($to, 'interactive', $interactive);
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
        // to_and_components is the bulk shape (official MSG91 SDK uses a
        // plain string for `to`; a single-element array is accepted too and
        // is proven delivering to 91-numbers — keep it untouched).
        // NOTE: MSG91 validates synchronously but delivers asynchronously.
        // A queued (HTTP 200) request can still FAIL later in the MSG91
        // panel (OUTBOUND → failed, e.g. "blocked prefixes (1)"). That is an
        // account-side route restriction, not a payload bug.
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
        if (strlen($to) < 10 || strlen($to) > 15) { $err = 'Invalid WhatsApp number "' . $to . '" (need 10–15 digits with country code).'; return false; }
        if ($to[0] === '0') { $err = 'Invalid WhatsApp number "' . $to . '" (strip trunk 0 / 00 prefix, keep country code only).'; return false; }
        // NANP (USA/CA: 1 + 10 digits) sanity — bad area codes are rejected by carriers/MSG91.
        if (strlen($to) === 11 && $to[0] === '1' && ($to[1] < '2' || $to[1] > '9')) {
            $err = 'Invalid USA/CA number "' . $to . '" (area code after +1 must be 2–9).';
            return false;
        }
        $err = null; return true;
    }

    protected function fail(string $error): array
    {
        log_message('error', 'MSG91 WhatsApp: ' . $error);
        return ['ok' => false, 'message_id' => null, 'error' => $error, 'response' => null];
    }

    /** Bulk endpoint = templates only. */
    protected function send(string $contentType, array $payload): array
    {
        $body = ['integrated_number' => $this->integratedNumber, 'content_type' => $contentType, 'payload' => $payload];
        return $this->postJson($this->baseUrl . '/bulk/', $body);
    }

    /**
     * Session endpoint (no template, inside the 24h window).
     * Envelope: {"to","from","message":{"type","content"}}.
     */
    protected function sendSession(string $to, string $type, array $content): array
    {
        return $this->postJson($this->sessionBaseUrl . '/', [
            'to' => $to,
            'from' => $this->integratedNumber,
            'message' => ['type' => $type, 'content' => $content],
        ]);
    }

    protected function postJson(string $url, array $body): array
    {
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
            // MSG91 sometimes returns HTTP 200 with an error payload — catch it.
            if ($ok && is_array($data)) {
                $blob = strtolower(json_encode($data));
                if (str_contains($blob, 'blocked') || str_contains($blob, 'restricted') || str_contains($blob, 'not allowed') || str_contains($blob, 'prefix')) $ok = false;
            }
            $error = null;
            if (!$ok) {
                $error = 'MSG91 error [' . $code . ']: ' . substr((string)$raw, 0, 500);
                // Account-side outbound restriction (e.g. USA prefix "1" blocked):
                // make it actionable instead of a raw dump.
                if (preg_match('/blocked|restrict/i', (string)$raw)) {
                    $error .= ' — Outbound to this country/prefix is blocked on your MSG91 account. Enable international/WhatsApp outbound for it in the MSG91 panel (or check allowed prefixes), then retry.';
                }
            }
            $result = [
                'ok' => $ok,
                'message_id' => $data['message_id'] ?? $data['request_id'] ?? null,
                'error' => $error,
                'response' => $data ?? $raw,
            ];
            if (!$ok) log_message('error', 'MSG91 WhatsApp API Error [' . $url . ']: ' . $result['error'] . ' | to=' . ($body['payload']['template']['to_and_components'][0]['to'][0] ?? ($body['to'] ?? '?')));
            return $result;
        } catch (\Throwable $e) {
            return $this->fail('Exception: ' . $e->getMessage());
        }
    }
}
