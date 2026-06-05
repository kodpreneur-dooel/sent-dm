# Sent DM for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kodpreneur-dooel/sent-dm.svg?style=flat-square)](https://packagist.org/packages/kodpreneur-dooel/sent-dm)
[![GitHub Tests Action Status](https://github.com/kodpreneur-dooel/sent-dm/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kodpreneur-dooel/sent-dm/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/kodpreneur-dooel/sent-dm/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/kodpreneur-dooel/sent-dm/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kodpreneur-dooel/sent-dm.svg?style=flat-square)](https://packagist.org/packages/kodpreneur-dooel/sent-dm)

`kodpreneur-dooel/sent-dm` is a Laravel package for the official [Sent DM PHP SDK](https://docs.sent.dm/sdks/php). It gives Laravel apps a zero-boilerplate service provider, publishable config, container binding for the Sent SDK client, a facade, and helpers for verifying signed Sent webhooks.

The package intentionally follows the integration pattern from the Sent DM Laravel docs: configure your API key in Laravel, inject the SDK client where you send messages, and verify webhooks against the raw request body before processing events.

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
```

Published config:

```php
return [
    'api_key' => env('SENT_DM_API_KEY'),
    'base_url' => env('SENT_DM_BASE_URL'),
    'webhook_secret' => env('SENT_DM_WEBHOOK_SECRET'),
    'max_retries' => env('SENT_DM_MAX_RETRIES', 2),
    'timeout' => env('SENT_DM_TIMEOUT', 60),
];
```

The service provider is auto-discovered by Laravel.

## Compatibility With Sent Docs

Sent's Laravel integration guide shows a manual service provider that binds `Client::class` from `config('services.sent_dm.api_key')`. This package does that for you automatically.

By default, the package reads `config('sent-dm.api_key')`. It also falls back to `config('services.sent_dm.api_key')`, so existing apps that already follow the Sent docs can migrate without rewiring everything.

Note: the current Composer package autoloads the SDK namespace as `SentDm\Client`.

## Usage

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

When an app using Laravel Boost installs package guidelines or skills, AI agents can receive Sent DM-specific Laravel guidance for client injection, configuration, sandbox usage, and webhook verification.

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

Please do not report security vulnerabilities through public issues. Contact KODPRENEUR DOOEL privately at `contact@codepreneur.mk`.

## Credits

- [KODPRENEUR DOOEL](https://github.com/kodpreneur-dooel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
