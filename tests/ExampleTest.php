<?php

use KodpreneurDool\SentDm\Facades\SentDm as SentDmFacade;
use KodpreneurDool\SentDm\SentDm;
use SentDm\Client;

it('registers the Sent DM client', function () {
    expect(app(Client::class))->toBeInstanceOf(Client::class)
        ->and(app('sent-dm.client'))->toBe(app(Client::class));
});

it('registers the Sent DM wrapper', function () {
    expect(app(SentDm::class))->toBeInstanceOf(SentDm::class)
        ->and(SentDmFacade::client())->toBe(app(Client::class));
});

it('verifies webhook signatures', function () {
    $payload = '{"field":"message"}';
    $webhookId = 'evt_123';
    $timestamp = (string) time();
    $key = 'sent-webhook-secret';
    $signature = 'v1,'.base64_encode(hash_hmac('sha256', "{$webhookId}.{$timestamp}.{$payload}", $key, true));

    expect(app(SentDm::class)->verifyWebhookSignature(
        payload: $payload,
        webhookId: $webhookId,
        timestamp: $timestamp,
        signature: $signature,
    ))->toBeTrue();
});

it('rejects invalid webhook signatures', function () {
    expect(app(SentDm::class)->verifyWebhookSignature(
        payload: '{}',
        webhookId: 'evt_123',
        timestamp: (string) time(),
        signature: 'v1,invalid',
    ))->toBeFalse();
});
