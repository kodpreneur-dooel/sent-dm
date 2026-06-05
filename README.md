# Sent DM for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kodpreneur-dooel/sent-dm.svg?style=flat-square)](https://packagist.org/packages/kodpreneur-dooel/sent-dm)
[![GitHub Tests Action Status](https://github.com/kodpreneur-dooel/sent-dm/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kodpreneur-dooel/sent-dm/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/kodpreneur-dooel/sent-dm/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/kodpreneur-dooel/sent-dm/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kodpreneur-dooel/sent-dm.svg?style=flat-square)](https://packagist.org/packages/kodpreneur-dooel/sent-dm)

`kodpreneur-dooel/sent-dm` is a Laravel package for the official [Sent DM PHP SDK](https://docs.sent.dm/sdks/php). It
gives Laravel apps a zero-boilerplate service provider, publishable config, container binding for the Sent SDK client, a
facade, and helpers for verifying signed Sent webhooks.

The package intentionally follows the integration pattern from the Sent DM Laravel docs: configure your API key in
Laravel, inject the SDK client where you send messages, and verify webhooks against the raw request body before
processing events.

## Requirements

- PHP 8.2 or higher
- Laravel 11, 12, or 13
- Sent DM API key

## Installation

Install the package with Composer:

```bash
composer require kodpreneur-dooel/sent-dm
```

Publish the config file:

```bash
php artisan vendor:publish --tag="sent-dm-config"
```

Add your Sent credentials to `.env`:

```dotenv
SENT_DM_API_KEY=your_api_key
SENT_DM_WEBHOOK_SECRET=whsec_your_webhook_secret
SENT_DM_MAX_RETRIES=2
SENT_DM_TIMEOUT=60

SENT_DM_SMS_QUEUE=default
SENT_DM_SMS_SANDBOX=false
SENT_DM_SMS_PROFILE_ID=
SENT_DM_SMS_TEMPLATE_ID=
SENT_DM_SMS_TEMPLATE_NAME=sms_notification
SENT_DM_SMS_TEMPLATE_PARAMETER=message
```

Published config:

```php
return [
    'api_key' => env('SENT_DM_API_KEY'),
    'base_url' => env('SENT_DM_BASE_URL'),
    'webhook_secret' => env('SENT_DM_WEBHOOK_SECRET'),
    'max_retries' => env('SENT_DM_MAX_RETRIES', 2),
    'timeout' => env('SENT_DM_TIMEOUT', 60),

    'sms' => [
        'queue' => env('SENT_DM_SMS_QUEUE', 'default'),
        'sandbox' => env('SENT_DM_SMS_SANDBOX', false),
        'profile_id' => env('SENT_DM_SMS_PROFILE_ID'),

        'default_template' => [
            'id' => env('SENT_DM_SMS_TEMPLATE_ID'),
            'name' => env('SENT_DM_SMS_TEMPLATE_NAME', 'sms_notification'),
            'parameter' => env('SENT_DM_SMS_TEMPLATE_PARAMETER', 'message'),
        ],
    ],
];
```

The service provider is auto-discovered by Laravel.

## Compatibility With Sent Docs

Sent's Laravel integration guide shows a manual service provider that binds `Client::class` from
`config('services.sent_dm.api_key')`. This package does that for you automatically.

By default, the package reads `config('sent-dm.api_key')`. It also falls back to `config('services.sent_dm.api_key')`,
so existing apps that already follow the Sent docs can migrate without rewiring everything.

Note: the current Composer package autoloads the SDK namespace as `SentDm\Client`.

## SMS Notifications

The main package feature is a Laravel notification channel named `sms`. Add `sms` to any notification's `via()` method,
return a Sent DM SMS message from `toSms()`, and the package handles the rest.

```php
use Codepreneur\SentDm\Messages\SentDmSmsMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'sms'];
    }

    public function toSms(object $notifiable): SentDmSmsMessage
    {
        return SentDmSmsMessage::text('Your invoice has been paid.');
    }
}
```

Your notifiable model needs an SMS route:

```php
public function routeNotificationForSms(): ?string
{
    return $this->phone_number;
}
```

Then send it like any Laravel notification:

```php
$user->notify(new InvoicePaid);
```

### Included SmsNotification

For quick one-off sends, use the included notification:

```php
use Codepreneur\SentDm\Notifications\SmsNotification;

$user->notify(new SmsNotification('Your verification code is 123456.'));
```

You can also pass a fully configured message:

```php
use Codepreneur\SentDm\Messages\SentDmSmsMessage;
use Codepreneur\SentDm\Notifications\SmsNotification;

$user->notify(new SmsNotification(
    SentDmSmsMessage::text('Your code is 123456.')
        ->sandbox()
        ->idempotencyKey('verification-'.$user->id)
));
```

### Conditional SMS Channel

Use `InteractsWithSentDmSms` when you want cda-v2-style behavior: include SMS only when the notifiable has a phone
route, skip sending if the route is missing, and use the configured SMS queue.

```php
use Codepreneur\SentDm\Concerns\InteractsWithSentDmSms;
use Codepreneur\SentDm\Messages\SentDmSmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SmsOnlyNotification extends Notification implements ShouldQueue
{
    use InteractsWithSentDmSms;
    use Queueable;

    public function via(object $notifiable): array
    {
        return $this->smsChannelsFor($notifiable);
    }

    public function toSms(object $notifiable): SentDmSmsMessage
    {
        return SentDmSmsMessage::text('Hello from Sent DM.');
    }
}
```

The trait provides:

- `tries = 3`
- `backoff()` with `10`, `60`, and `300` second delays
- `smsChannelsFor($notifiable)`
- `shouldSend($notifiable, 'sms')`
- `viaQueues()` using `sent-dm.sms.queue`

### Message Builder

`SentDmSmsMessage` supports all common SMS options:

```php
SentDmSmsMessage::text('Hello')
    ->to('+38970123456')
    ->sandbox()
    ->idempotencyKey('order-123')
    ->xProfileId('profile_123');
```

Use an explicit Sent DM template:

```php
SentDmSmsMessage::forTemplate(
    id: '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
    name: 'welcome',
    parameters: ['name' => $notifiable->name],
);
```

Or pass the template array directly:

```php
SentDmSmsMessage::make()->templateArray([
    'id' => '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
    'name' => 'welcome',
    'parameters' => ['name' => 'John Doe'],
]);
```

By default, `SentDmSmsMessage::text()` uses the configured template name and parameter. Create a Sent DM template such
as `sms_notification` with a single `message` variable, then set:

```dotenv
SENT_DM_SMS_TEMPLATE_NAME=sms_notification
SENT_DM_SMS_TEMPLATE_PARAMETER=message
```

If your template requires an ID, also set:

```dotenv
SENT_DM_SMS_TEMPLATE_ID=7ba7b820-9dad-11d1-80b4-00c04fd430c8
```

### Routing Options

The channel sends to the notifiable route by default:

```php
public function routeNotificationForSms(): string
{
    return '+38970123456';
}
```

You can override recipients per message:

```php
public function toSms(object $notifiable): SentDmSmsMessage
{
    return SentDmSmsMessage::text('Team alert')
        ->to(['+38970123456', '+38970999888']);
}
```

## Direct SDK Usage

Inject the official SDK client anywhere in your Laravel app:

```php
use SentDm\Client;

class SendWelcomeMessage
{
    public function __construct(
        private Client $sentDm,
    ) {}

    public function handle(string $phoneNumber): string
    {
        $result = $this->sentDm->messages->send(
            to: [$phoneNumber],
            template: [
                'id' => '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
                'name' => 'welcome',
                'parameters' => [
                    'name' => 'John Doe',
                ],
            ],
            channel: ['sms', 'whatsapp', 'rcs'],
        );

        return $result->data->recipients[0]->messageID;
    }
}
```

Use sandbox mode while developing:

```php
$result = $sentDm->messages->send(
    to: ['+1234567890'],
    template: [
        'id' => '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
        'name' => 'welcome',
    ],
    sandbox: true,
);
```

## Facade

The facade gives you access to the wrapper and underlying SDK client:

```php
use Codepreneur\SentDm\Facades\SentDm;

$result = SentDm::client()->messages->send(
    to: ['+1234567890'],
    template: [
        'id' => '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
        'name' => 'welcome',
    ],
);
```

## Webhooks

Sent signs webhook requests with these headers:

- `X-Webhook-ID`
- `X-Webhook-Timestamp`
- `X-Webhook-Signature`

Verify the raw body before parsing JSON:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Codepreneur\SentDm\Facades\SentDm;

Route::post('/webhooks/sent', function (Request $request) {
    abort_unless(SentDm::verifyWebhookRequest($request), 401);

    $event = json_decode($request->getContent());

    if ($event?->field === 'message') {
        // Update your local message status or dispatch a job.
    }

    return response()->json(['received' => true]);
});
```

You can also verify manually:

```php
$valid = SentDm::verifyWebhookSignature(
    payload: $request->getContent(),
    webhookId: $request->header('X-Webhook-ID', ''),
    timestamp: $request->header('X-Webhook-Timestamp', ''),
    signature: $request->header('X-Webhook-Signature', ''),
);
```

By default, signatures older than 5 minutes are rejected to reduce replay risk.

## Laravel Boost

This package requires `laravel/boost` as a development dependency and ships Boost resources for downstream Laravel apps:

- `resources/boost/guidelines/sent-dm.blade.php`
- `resources/boost/skills/sent-dm-laravel/SKILL.md`

When an app using Laravel Boost installs package guidelines or skills, AI agents can receive Sent DM-specific Laravel
guidance for client injection, configuration, sandbox usage, and webhook verification.

## Testing

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for contribution guidelines.

## Security Vulnerabilities

Please do not report security vulnerabilities through public issues. Contact KODPRENEUR DOOEL privately at
`contact@codepreneur.mk`.

## Credits

- [KODPRENEUR DOOEL](https://github.com/kodpreneur-dooel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
