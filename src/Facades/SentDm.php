<?php

namespace Codepreneur\SentDm\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Codepreneur\SentDm\SentDm
 *
 * @method static \SentDm\Client client()
 * @method static bool verifyWebhookSignature(string $payload, string $webhookId, string $timestamp, string $signature, ?string $secret = null, int $tolerance = 300)
 * @method static bool verifyWebhookRequest(\Illuminate\Http\Request $request, ?string $secret = null, int $tolerance = 300)
 */
class SentDm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sent-dm';
    }
}
