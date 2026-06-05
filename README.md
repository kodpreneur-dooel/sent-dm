# Laravel integration for the Sent DM PHP SDK.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kodpreneur-dool/sent-dm.svg?style=flat-square)](https://packagist.org/packages/kodpreneur-dool/sent-dm)
[![GitHub Tests Action Status](https://github.com/kodpreneur-dool/sent-dm/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kodpreneur-dool/sent-dm/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/kodpreneur-dool/sent-dm/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/kodpreneur-dool/sent-dm/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kodpreneur-dool/sent-dm.svg?style=flat-square)](https://packagist.org/packages/kodpreneur-dool/sent-dm)

Laravel package for the official [Sent DM PHP SDK](https://docs.sent.dm/sdks/php). It registers the SDK client in Laravel's container, publishes a small config file, and includes webhook signature verification.

## Installation

You can install the package via composer:

```bash
composer require kodpreneur-dool/sent-dm
```

Publish the config file:

```bash
php artisan vendor:publish --tag="sent-dm-config"
```

Add your Sent credentials:

```dotenv
SENT_DM_API_KEY=your_api_key
SENT_DM_WEBHOOK_SECRET=whsec_your_webhook_secret
SENT_DM_MAX_RETRIES=2
SENT_DM_TIMEOUT=60
```

This is the contents of the published config file:

```php
return [
    'api_key' => env('SENT_DM_API_KEY'),
    'base_url' => env('SENT_DM_BASE_URL'),
    'webhook_secret' => env('SENT_DM_WEBHOOK_SECRET'),
    'max_retries' => env('SENT_DM_MAX_RETRIES', 2),
    'timeout' => env('SENT_DM_TIMEOUT', 60),
];
```

## Usage

Inject the official SDK client anywhere in your app:

```php
use SentDm\Client;

class MessageController
{
    public function __construct(
        protected Client $sentDm,
    ) {}

    public function __invoke()
    {
        return $this->sentDm->messages->send(
            to: ['+1234567890'],
            template: [
                'id' => '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
                'name' => 'welcome',
                'parameters' => [
                    'name' => 'John Doe',
                ],
            ],
            channel: ['sms', 'whatsapp', 'rcs'],
        );
    }
}
```

Or access the SDK client through the facade:

```php
use KodpreneurDool\SentDm\Facades\SentDm;

$result = SentDm::client()->messages->send(
    to: ['+1234567890'],
    template: [
        'id' => '7ba7b820-9dad-11d1-80b4-00c04fd430c8',
        'name' => 'welcome',
    ],
    sandbox: true,
);
```

## Webhooks

Use the helper to verify Sent webhook requests:

```php
use Illuminate\Http\Request;
use KodpreneurDool\SentDm\Facades\SentDm;

Route::post('/webhooks/sent', function (Request $request) {
    $valid = SentDm::verifyWebhookSignature(
        payload: $request->getContent(),
        webhookId: $request->header('X-Webhook-ID', ''),
        timestamp: $request->header('X-Webhook-Timestamp', ''),
        signature: $request->header('X-Webhook-Signature', ''),
    );

    abort_unless($valid, 401);

    $event = json_decode($request->getContent());

    return response()->json(['received' => true]);
});
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [KODPRENEUR DOOEL](https://github.com/kodpreneur-dool)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
