---
name: sent-dm-laravel
description: Build Laravel features that send Sent DM messages, inject the Sent SDK client, and verify Sent webhook signatures.
---

# Sent DM Laravel

Use this skill when adding Sent DM messaging or webhook handling to a Laravel app that has `kodpreneur-dooel/sent-dm`
installed.

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
SENT_DM_SMS_QUEUE=default
SENT_DM_SMS_SANDBOX=false
SENT_DM_SMS_PROFILE_ID=
SENT_DM_SMS_TEMPLATE_ID=
SENT_DM_SMS_TEMPLATE_NAME=sms_notification
SENT_DM_SMS_TEMPLATE_PARAMETER=message
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

## Notification SMS Channel

Use `sms` in any notification `via()` method. Existing app notifications can append `sms` beside mail, database,
broadcast, or other channels:

```php
public function via(object $notifiable): array
{
    return ['mail', 'sms'];
}
```

Build SMS payloads with `Codepreneur\SentDm\Messages\SentDmSmsMessage`:

```php
public function toSms(object $notifiable): SentDmSmsMessage
{
    return SentDmSmsMessage::text('Your verification code is 123456.');
}
```

Use explicit Sent DM templates when needed:

```php
public function toSms(object $notifiable): SentDmSmsMessage
{
    return SentDmSmsMessage::forTemplate(
        id: '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
        name: 'welcome',
        parameters: ['name' => $notifiable->name],
    );
}
```

Notifiable models should implement:

```php
public function routeNotificationForSms(): ?string
{
    return $this->phone_number;
}
```

For one-off SMS sends, use:

```php
use Codepreneur\SentDm\Notifications\SmsNotification;

$user->notify(new SmsNotification('Your verification code is 123456.'));
```

Use `Codepreneur\SentDm\Concerns\InteractsWithSentDmSms` when a notification should include the `sms` channel only when
a route exists, use retry backoff, and send SMS work to the configured queue.

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
