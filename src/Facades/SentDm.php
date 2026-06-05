<?php

namespace KodpreneurDool\SentDm\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \KodpreneurDool\SentDm\SentDm
 *
 * @method static \SentDm\Client client()
 * @method static bool verifyWebhookSignature(string $payload, string $webhookId, string $timestamp, string $signature, ?string $secret = null, int $tolerance = 300)
 */
class SentDm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sent-dm';
    }
}
