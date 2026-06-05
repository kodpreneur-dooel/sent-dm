---
name: sent-dm-laravel
description: Build Laravel features that send Sent DM messages, inject the Sent SDK client, and verify Sent webhook signatures.
---

# Sent DM Laravel

Use this skill when adding Sent DM messaging or webhook handling to a Laravel app that has `kodpreneur-dooel/sent-dm` installed.

## Setup

Publish configuration:

```bash
php artisan vendor:publish --tag="sent-dm-config"
```

Use environment variables:

```dotenv
SENT_DM_API_KEY=
SENT_DM_WEBHOOK_SECRET=
SENT_DM_MAX_RETRIES=2
SENT_DM_TIMEOUT=60
```

## Client Access

Prefer dependency injection:

```php
use SentDm\Client;

public function __construct(private Client $sentDm) {}
```

Facade access is available for small integration points:

```php
use Codepreneur\SentDm\Facades\SentDm;

$client = SentDm::client();
```

## Webhooks

Read the raw request body before parsing JSON. Verify headers using the package helper:

```php
$valid = SentDm::verifyWebhookSignature(
    payload: $request->getContent(),
    webhookId: $request->header('X-Webhook-ID', ''),
    timestamp: $request->header('X-Webhook-Timestamp', ''),
    signature: $request->header('X-Webhook-Signature', ''),
);
```

Abort with `401` if verification fails. Process valid webhook payloads quickly or dispatch a queued job.
