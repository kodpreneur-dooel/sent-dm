<?php

namespace Codepreneur\SentDm\Tests;

use Codepreneur\SentDm\SentDmServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SentDmServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('sent-dm.api_key', 'sent_test_key');
        config()->set('sent-dm.webhook_secret', 'whsec_'.base64_encode('sent-webhook-secret'));
    }
}
