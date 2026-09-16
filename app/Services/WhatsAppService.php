<?php

namespace App\Services;

/**
 * WhatsApp facade — now backed by MSG91.
 *
 * Keeps the historic method names/signatures used across controllers and
 * cron commands (all return bool), while exposing the full MSG91 session
 * API (text, link, image, video, document, audio, buttons, CTA, list,
 * templates) via msg91().
 *
 *   (new WhatsAppService())->sendMessage($phone, $text);      // session text
 *   (new WhatsAppService())->msg91()->sendImage($to, $link, $caption);
 *   (new WhatsAppService())->msg91()->sendReplyButtons($to, $body, $buttons);
 */
class WhatsAppService
{
    protected Msg91WhatsAppService $msg91;

    public function __construct(?array $settings = null)
    {
        $this->msg91 = new Msg91WhatsAppService($settings);
    }

    public function msg91(): Msg91WhatsAppService
    {
        return $this->msg91;
    }

    public function isConfigured(): bool
    {
        return $this->msg91->isConfigured();
    }

    /**
     * Plain session text message. (This method was missing entirely —
     * every existing caller fatals without it.)
     */
    public function sendMessage(string $phone, string $message): bool
    {
        return $this->msg91->sendText($phone, $message)['ok'];
    }

    /**
     * Legacy signature from the AiSensy era, now mapped to an MSG91
     * approved template: $campaignName = template name,
     * $templateParams = ordered {{1}}, {{2}}... body values.
     */
    public function sendTemplate(
        string $phone,
        string $campaignName,
        string $userName = '',
        array $templateParams = [],
        ?string $source = null,
        array $tags = [],
        array $attributes = []
    ): bool {
        return $this->msg91->sendTemplate($phone, $campaignName, array_values($templateParams))['ok'];
    }

    /** Session message with a clickable link preview card. */
    public function sendLink(string $phone, string $url, string $caption = ''): bool
    {
        return $this->msg91->sendLink($phone, $url, $caption)['ok'];
    }

    public function sendImage(string $phone, string $link, string $caption = ''): bool
    {
        return $this->msg91->sendImage($phone, $link, $caption)['ok'];
    }

    public function sendVideo(string $phone, string $link, string $caption = ''): bool
    {
        return $this->msg91->sendVideo($phone, $link, $caption)['ok'];
    }

    public function sendDocument(string $phone, string $link, string $filename = '', string $caption = ''): bool
    {
        return $this->msg91->sendDocument($phone, $link, $filename, $caption)['ok'];
    }

    /**
     * Quick-reply buttons. $buttons = [['id'=>..,'title'=>..], ...] (max 3).
     */
    public function sendButtons(string $phone, string $body, array $buttons, ?array $header = null, string $footer = ''): bool
    {
        return $this->msg91->sendReplyButtons($phone, $body, $buttons, $header, $footer)['ok'];
    }
}
