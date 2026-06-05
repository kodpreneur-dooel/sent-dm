<?php

namespace KodpreneurDool\SentDm;

use SentDm\Client;

class SentDm
{
    public function __construct(
        protected Client $client,
        protected ?string $webhookSecret = null,
    ) {}

    public function client(): Client
    {
        return $this->client;
    }

    public function verifyWebhookSignature(
        string $payload,
        string $webhookId,
        string $timestamp,
        string $signature,
        ?string $secret = null,
        int $tolerance = 300,
    ): bool {
        $secret ??= $this->webhookSecret;

        if (blank($secret) || blank($webhookId) || blank($timestamp) || blank($signature)) {
            return false;
        }

        if ($tolerance > 0 && abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $keyBase64 = str_starts_with($secret, 'whsec_')
            ? substr($secret, 6)
            : $secret;

        $key = base64_decode($keyBase64, true);

        if ($key === false) {
            return false;
        }

        $signed = "{$webhookId}.{$timestamp}.{$payload}";
        $expected = 'v1,'.base64_encode(hash_hmac('sha256', $signed, $key, true));

        return hash_equals($expected, $signature);
    }
}
