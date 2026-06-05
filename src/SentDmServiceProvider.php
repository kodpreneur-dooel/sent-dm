<?php

namespace Codepreneur\SentDm;

use Codepreneur\SentDm\Channels\SentDmSmsChannel;
use Illuminate\Notifications\ChannelManager;
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
        $this->app->singleton(SentDmSmsChannel::class);

        $this->app->singleton(Client::class, function () {
            return new Client(
                apiKey: (string) (config('sent-dm.api_key') ?: config('services.sent_dm.api_key')),
                baseUrl: config('sent-dm.base_url') ?: config('services.sent_dm.base_url'),
                requestOptions: RequestOptions::with(
                    timeout: (float) config('sent-dm.timeout', 60),
                    maxRetries: (int) config('sent-dm.max_retries', 2),
                ),
            );
        });

        $this->app->alias(Client::class, 'sent-dm.client');

        $this->app->singleton(SentDm::class, fn ($app) => new SentDm(
            client: $app->make(Client::class),
            webhookSecret: config('sent-dm.webhook_secret') ?: config('services.sent_dm.webhook_secret'),
        ));

        $this->app->alias(SentDm::class, 'sent-dm');
    }

    public function packageBooted(): void
    {
        $app = $this->app;

        $app->afterResolving(ChannelManager::class, function (ChannelManager $manager) use ($app): void {
            $manager->extend('sms', fn () => $app->make(SentDmSmsChannel::class));
        });
    }
}
