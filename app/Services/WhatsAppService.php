<?php

namespace App\Services;

use App\Models\SettingModel;

class WhatsAppService
{
    protected string $apiUrl = 'https://backend.aisensy.com/campaign/t1/api/v2';

    protected string $apiKey;
    protected string $defaultSource;

    public function __construct()
    {
        $settings = (new SettingModel())->getAllSettings();

        $this->apiKey = $settings['whatsapp_api_key'] ?? 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjZhYTlhMWUwNmJiYjUyMTE1ZWU4MmQzNSIsIm5hbWUiOiJOR1dlYkQgRVJQIiwiYXBwTmFtZSI6IkFpU2Vuc3kiLCJjbGllbnRJZCI6IjZhYTk5ZjAyOWUwMzQ3YjUzYTIwNjAzMCIsImFjdGl2ZVBsYW4iOiJGUkVFX0ZPUkVWRVIiLCJpYXQiOjE3ODk1MDIxMjN9.JfWxDJoobIbaGWjRoIoFanbqYa0wXCubrUDwnKAV_AY';
        $this->defaultSource = $settings['whatsapp_source'] ?? 'NGWebD ERP';
    }

    /**
     * Send WhatsApp template through AiSensy API Campaign.
     *
     * @param string $phone
     * @param string $campaignName
     * @param string $userName
     * @param array $templateParams
     * @param string|null $source
     * @param array $tags
     * @param array $attributes
     */
    public function sendTemplate(
        string $phone,
        string $campaignName,
        string $userName,
        array $templateParams = [],
        ?string $source = null,
        array $tags = [],
        array $attributes = []
    ): bool {
        if (!$this->apiKey) {
            log_message('error', 'AiSensy API key is not configured.');
            return false;
        }

        $phone = $this->formatPhone($phone);

        if (!$phone) {
            log_message('error', 'Invalid WhatsApp phone number.');
            return false;
        }

        $payload = [
            'apiKey' => $this->apiKey,
            'campaignName' => $campaignName,
            'destination' => $phone,
            'userName' => $userName,
            'source' => $source ?: $this->defaultSource,
            'templateParams' => array_map(
                fn($value) => (string) $value,
                $templateParams
            ),
        ];

        if (!empty($tags)) {
            $payload['tags'] = array_values($tags);
        }

        if (!empty($attributes)) {
            $payload['attributes'] = $attributes;
        }

        return $this->post($payload);
    }

    /**
     * Format Indian / international WhatsApp number.
     */
    protected function formatPhone(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with($phone, '+')) {
            return '+' . preg_replace('/[^0-9]/', '', substr($phone, 1));
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            return '+91' . $phone;
        }

        if (strlen($phone) >= 11) {
            return '+' . $phone;
        }

        return '';
    }

    /**
     * Send request to AiSensy.
     */
    protected function post(array $payload): bool
    {
        try {
            $ch = curl_init($this->apiUrl);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            if ($curlError) {
                log_message(
                    'error',
                    'AiSensy CURL Error: ' . $curlError
                );

                return false;
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                log_message(
                    'error',
                    'AiSensy API Error [' . $httpCode . ']: ' . $response
                );

                return false;
            }

            return true;

        } catch (\Throwable $e) {
            log_message(
                'error',
                'AiSensy Exception: ' . $e->getMessage()
            );

            return false;
        }
    }
}