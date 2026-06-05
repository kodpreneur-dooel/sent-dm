<?php

namespace KodpreneurDool\SentDm;

use SentDm\Client;
use SentDm\RequestOptions;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SentDmServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sent-dm')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Client::class, function () {
            return new Client(
                apiKey: (string) config('sent-dm.api_key'),
                baseUrl: config('sent-dm.base_url'),
                requestOptions: RequestOptions::with(
                    timeout: (float) config('sent-dm.timeout', 60),
                    maxRetries: (int) config('sent-dm.max_retries', 2),
                ),
            );
        });

        $this->app->alias(Client::class, 'sent-dm.client');

        $this->app->singleton(SentDm::class, fn ($app) => new SentDm(
            client: $app->make(Client::class),
            webhookSecret: config('sent-dm.webhook_secret'),
        ));

        $this->app->alias(SentDm::class, 'sent-dm');
    }
}
