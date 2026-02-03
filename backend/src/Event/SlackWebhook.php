<?php

namespace Karolina\Event;

class SlackWebhook
{
    private string $webhookUrl;

    public function __construct(string $webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function sendMessage(string $text, array $additionalData = []): bool
    {
        $payload = array_merge([
            'text' => $text
        ], $additionalData);

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public function sendRichMessage(array $blocks, string $fallbackText = ''): bool
    {
        $payload = [
            'blocks' => $blocks
        ];

        if ($fallbackText) {
            $payload['text'] = $fallbackText;
        }

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $result = curl_exec($ch);
        
        if ($result === false) {
            $error = curl_error($ch);
            error_log("Slack webhook curl error: " . $error);
            curl_close($ch);
            return false;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Slack webhook failed with HTTP code: " . $httpCode . ", Response: " . $result);
            return false;
        }

        return true;
    }
}