<?php

use Codepreneur\SentDm\Channels\SentDmSmsChannel;
use Codepreneur\SentDm\Messages\SentDmSmsMessage;
use Codepreneur\SentDm\Notifications\SmsNotification;
use GuzzleHttp\Psr7\Response;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\RoutesNotifications;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use SentDm\Client;
use SentDm\RequestOptions;

it('registers sms as a notification channel driver', function () {
    expect(app(ChannelManager::class)->driver('sms'))->toBeInstanceOf(SentDmSmsChannel::class);
});

it('includes sms channel when a notification has an sms route', function () {
    $notifiable = new class
    {
        use RoutesNotifications;

        public function routeNotificationForSms(): string
        {
            return '+38970123456';
        }
    };

    expect((new SmsNotification('Test message'))->via($notifiable))->toBe(['sms']);
});

it('excludes sms channel when a notification has no sms route', function () {
    $notifiable = new class
    {
        use RoutesNotifications;
    };

    expect((new SmsNotification('Test message'))->via($notifiable))->toBeEmpty();
});

it('builds default template payloads for text messages', function () {
    config()->set('sent-dm.sms.default_template.name', 'plain_sms');
    config()->set('sent-dm.sms.default_template.parameter', 'body');

    expect(SentDmSmsMessage::text('Hello')->toTemplatePayload())->toBe([
        'name' => 'plain_sms',
        'parameters' => ['body' => 'Hello'],
    ]);
});

it('sends sms notifications through the Sent DM SDK', function () {
    config()->set('sent-dm.sms.default_template.name', 'plain_sms');
    config()->set('sent-dm.sms.default_template.parameter', 'body');

    $transporter = new class implements ClientInterface
    {
        public ?RequestInterface $request = null;

        public function sendRequest(RequestInterface $request): Response
        {
            $this->request = $request;

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => [
                    'status' => 'QUEUED',
                    'template_name' => 'plain_sms',
                    'recipients' => [
                        [
                            'message_id' => 'msg_123',
                            'channel' => 'sms',
                            'to' => '+38970123456',
                        ],
                    ],
                ],
            ]));
        }
    };

    $client = new Client(
        apiKey: 'sent_test_key',
        requestOptions: RequestOptions::with(transporter: $transporter),
    );

    $notifiable = new class
    {
        use RoutesNotifications;

        public function routeNotificationForSms(): string
        {
            return '+38970123456';
        }
    };

    $response = (new SentDmSmsChannel($client))->send($notifiable, new SmsNotification('Hello'));

    $payload = json_decode((string) $transporter->request?->getBody(), true);

    expect($response?->success)->toBeTrue()
        ->and($payload['channel'])->toBe(['sms'])
        ->and($payload['to'])->toBe(['+38970123456'])
        ->and($payload['template'])->toBe([
            'name' => 'plain_sms',
            'parameters' => ['body' => 'Hello'],
        ]);
});

it('lets applications append sms to their own notifications', function () {
    $notifiable = new class
    {
        use RoutesNotifications;

        public function routeNotificationForSms(): string
        {
            return '+38970123456';
        }
    };

    $notification = new class extends Notification
    {
        public function via(object $notifiable): array
        {
            return ['mail', 'sms'];
        }

        public function toSms(object $notifiable): SentDmSmsMessage
        {
            return SentDmSmsMessage::text('Custom notification message');
        }
    };

    expect($notification->via($notifiable))->toContain('sms')
        ->and($notification->toSms($notifiable)->toTemplatePayload())->toBe([
            'name' => config('sent-dm.sms.default_template.name'),
            'parameters' => [
                config('sent-dm.sms.default_template.parameter') => 'Custom notification message',
            ],
        ]);
});
