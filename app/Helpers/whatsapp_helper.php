<?php
/**
 * WhatsApp template + message helpers (MSG91).
 *
 * Build approved-template payloads without hand-writing component arrays:
 *
 *   wa_send_template('919876543210', wa_template('order_update', wa_body($name, $orderId)));
 *   wa_send_template($to, wa_template('promo_offer', wa_body($name), ['language' => 'hi',
 *       'header' => wa_header_media('image', $link),
 *       'buttons' => [wa_button_url(1, $suffix)]]));
 *
 * Session shortcuts: wa_send_text(), wa_send_image(), wa_send_video(),
 * wa_send_link(), wa_send_buttons().
 */

if (!function_exists('wa_body')) {
    /** Ordered {{1}}, {{2}}... body values. */
    function wa_body(...$values): array
    {
        return array_values(array_map(static fn($v) => (string)$v, $values));
    }
}

if (!function_exists('wa_header_text')) {
    function wa_header_text(string $text): array
    {
        return ['type' => 'text', 'value' => $text];
    }
}

if (!function_exists('wa_header_media')) {
    /** $type: image|video|document. $filename used for documents. */
    function wa_header_media(string $type, string $link, string $filename = ''): array
    {
        $h = ['type' => strtolower($type), 'value' => $link];
        if (trim($filename) !== '') $h['filename'] = $filename;
        return $h;
    }
}

if (!function_exists('wa_button_url')) {
    /** Dynamic suffix appended to the template's URL button (button_N, 1-based). */
    function wa_button_url(int $index, string $suffix): array
    {
        return ['index' => $index, 'subtype' => 'url', 'value' => $suffix];
    }
}

if (!function_exists('wa_button_payload')) {
    function wa_button_payload(int $index, string $payload): array
    {
        return ['index' => $index, 'subtype' => 'quick_reply', 'value' => $payload];
    }
}

if (!function_exists('wa_template')) {
    /**
     * @param string $name   Approved template name.
     * @param array  $body   wa_body(...) values.
     * @param array  $opts   ['language'=>..,'namespace'=>..,'header'=>wa_header_*(),
     *                        'buttons'=>[wa_button_*(),...]]
     */
    function wa_template(string $name, array $body = [], array $opts = []): array
    {
        return ['name' => $name, 'body' => $body, 'options' => $opts];
    }
}

if (!function_exists('wa_buttons_reply')) {
    /** Session quick-reply buttons: wa_buttons_reply(['yes'=>'Yes','no'=>'No']). */
    function wa_buttons_reply(array $idToTitle): array
    {
        $out = [];
        foreach ($idToTitle as $id => $title) $out[] = ['id' => (string)$id, 'title' => (string)$title];
        return array_slice($out, 0, 3);
    }
}

if (!function_exists('wa_buttons_cta')) {
    /** Session CTA buttons: wa_buttons_cta([['url','Pay Now','https://..'],['phone','Call','919..']]). */
    function wa_buttons_cta(array $buttons): array
    {
        $out = [];
        foreach ($buttons as $b) {
            $out[] = ['kind' => $b[0] ?? 'url', 'title' => $b[1] ?? '', 'value' => $b[2] ?? ''];
        }
        return $out;
    }
}

if (!function_exists('wa_to')) {
    /**
     * Normalise to digits + country code (no plus).
     * Bare 10-digit numbers default to India (91) — store USA/others
     * WITH country code (e.g. +1...). Strips 00/011/0 prefixes.
     * +919593026451, 919593026451, 09593026451, 9593026451 all resolve
     * to the same conversation key.
     */
    function wa_to(string $phone): string
    {
        $digits = preg_replace('/\D/', '', trim($phone));
        if ($digits === '') return '';
        if (strlen($digits) > 12 && str_starts_with($digits, '00')) $digits = substr($digits, 2);
        elseif (strlen($digits) > 11 && str_starts_with($digits, '011')) {
            $c = substr($digits, 3);
            if (strlen($c) >= 10 && strlen($c) <= 15) $digits = $c;
        }
        if (strlen($digits) === 11 && $digits[0] === '0') $digits = substr($digits, 1);
        if (strlen($digits) === 10) $digits = '91' . $digits;
        return $digits;
    }
}

if (!function_exists('normalizeWhatsAppPhone')) {
    /** Canonical phone key used by clients, leads, inbound, outbound and lookup. */
    function normalizeWhatsAppPhone(?string $phone): string
    {
        return wa_to((string) $phone);
    }
}

if (!function_exists('maskPhone')) {
    /** Privacy-safe display: 919593026451 → 91******6451. */
    function maskPhone(?string $phone): string
    {
        $d = preg_replace('/\D/', '', (string) $phone);
        if (strlen($d) < 7) return $d;
        return substr($d, 0, 2) . str_repeat('*', strlen($d) - 6) . substr($d, -4);
    }
}

if (!function_exists('normalizeInboundWhatsAppMessage')) {
    /**
     * Normalize an MSG91 WhatsApp webhook payload (inbound message or
     * outbound status report) into one canonical shape.
     *
     * Handles MSG91's documented quirks:
     *  - direction "0" = inbound (customer → business)
     *  - sender in customerNumber; business number in integratedNumber
     *  - content may be an array OR a JSON-encoded string
     *  - messages may be a JSON-encoded array (first entry wins)
     *  - status reports carry ids (uuid/requestId) but no content
     *
     * Never throws on malformed input — returns direction=inbound with
     * empty phone/text so callers can acknowledge-and-ignore safely.
     */
    function normalizeInboundWhatsAppMessage(array $in): array
    {
        $data = $in['data'] ?? $in;

        // messages: stringified JSON array → first entry merged under data.
        if (isset($data['messages']) && is_string($data['messages'])) {
            $arr = json_decode($data['messages'], true);
            if (is_array($arr) && isset($arr[0]) && is_array($arr[0])) {
                $data = array_merge($data, $arr[0]);
            }
        }

        // content: array | JSON string | plain string.
        $content = $data['content'] ?? null;
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $content = is_array($decoded) ? $decoded : ['text' => $content];
        }
        if (! is_array($content)) $content = [];

        $type = strtolower((string) (
            $data['contentType'] ?? $data['messageType'] ?? $data['message_type']
            ?? $content['type'] ?? 'text'
        ));
        $text = trim((string) (
            $content['text'] ?? $content['body'] ?? $content['caption'] ??
            $data['text'] ?? $data['body'] ?? $data['caption'] ?? ''
        ));
        $media = trim((string) (
            $content['url'] ?? $content['link'] ?? $content['media_url'] ??
            $data['url'] ?? ''
        ));
        $providerId = (string) (
            $data['uuid'] ?? $data['requestId'] ?? $data['message_id'] ?? $data['messageId']
            ?? $content['id'] ?? $content['message_id'] ?? ''
        );
        // MSG91 direction "0" = inbound. Anything else with content is
        // still stored inbound; content-less payloads are status reports.
        $direction = (string) ($data['direction'] ?? '0');
        $isInbound = $direction === '0' || strtolower($direction) === 'inbound' || strtolower($direction) === 'mo';

        return [
            'provider_message_id' => $providerId,
            'phone_number'        => wa_to((string) (
                $data['customerNumber'] ?? $data['customer_number'] ?? $data['from']
                ?? $data['sender'] ?? $data['phone'] ?? $data['mobile'] ?? ''
            )),
            'contact_name'      => trim((string) ($data['customerName'] ?? $data['customer_name'] ?? '')),
            'integrated_number' => wa_to((string) ($data['integratedNumber'] ?? $data['integrated_number'] ?? '')),
            'message_type'      => $type !== '' ? $type : 'text',
            'message_text'      => $text,
            'media_url'         => $media,
            'media_caption'     => trim((string) ($content['caption'] ?? $data['caption'] ?? '')),
            'event'             => (string) ($data['eventName'] ?? $data['event'] ?? ''),
            'status_hint'       => strtolower((string) ($data['status'] ?? $data['reason'] ?? '')),
            'occurred_at'       => (string) ($data['ts'] ?? ''),
            'raw_payload'       => $in,
            'direction'         => $isInbound ? 'inbound' : 'outbound',
            'is_report'         => $text === '' && $media === '' && $providerId !== '',
        ];
    }
}

if (!function_exists('wa_send_template')) {
    function wa_send_template(string $to, array $template): array
    {
        $svc = new \App\Services\Msg91WhatsAppService();
        return $svc->sendTemplate($to, $template['name'] ?? '', $template['body'] ?? [], $template['options'] ?? []);
    }
}

if (!function_exists('wa_send_text')) {
    function wa_send_text(string $to, string $body, bool $preview = true): array
    {
        return (new \App\Services\Msg91WhatsAppService())->sendText($to, $body, $preview);
    }
}

if (!function_exists('wa_send_image')) {
    function wa_send_image(string $to, string $link, string $caption = ''): array
    {
        return (new \App\Services\Msg91WhatsAppService())->sendImage($to, $link, $caption);
    }
}

if (!function_exists('wa_send_video')) {
    function wa_send_video(string $to, string $link, string $caption = ''): array
    {
        return (new \App\Services\Msg91WhatsAppService())->sendVideo($to, $link, $caption);
    }
}

if (!function_exists('wa_send_link')) {
    function wa_send_link(string $to, string $url, string $caption = ''): array
    {
        return (new \App\Services\Msg91WhatsAppService())->sendLink($to, $url, $caption);
    }
}

if (!function_exists('wa_send_buttons')) {
    function wa_send_buttons(string $to, string $body, array $buttons, ?array $header = null, string $footer = ''): array
    {
        return (new \App\Services\Msg91WhatsAppService())->sendReplyButtons($to, $body, $buttons, $header, $footer);
    }
}
