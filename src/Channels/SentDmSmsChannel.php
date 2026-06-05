<?php

namespace Codepreneur\SentDm\Channels;

use Codepreneur\SentDm\Messages\SentDmSmsMessage;
use Illuminate\Notifications\Notification;
use InvalidArgumentException;
use SentDm\Client;

class SentDmSmsChannel
{
    public function __construct(protected Client $client) {}

    public function send(object $notifiable, Notification $notification): mixed
    {
        $message = $this->getMessage($notifiable, $notification);
        $to = $message->to ?: $this->routeNotificationForSms($notifiable, $notification);

        if (blank($to)) {
            return null;
        }

        return $this->client->messages->send(
            channel: $message->channels,
            sandbox: $message->sandbox ?? (bool) config('sent-dm.sms.sandbox', false),
            template: $message->toTemplatePayload($message->text),
            to: is_array($to) ? array_values($to) : [$to],
            idempotencyKey: $message->idempotencyKey,
            xProfileID: $message->xProfileId ?? config('sent-dm.sms.profile_id'),
            requestOptions: $message->requestOptions,
        );
    }

    protected function getMessage(object $notifiable, Notification $notification): SentDmSmsMessage
    {
        if (method_exists($notification, 'toSms')) {
            return SentDmSmsMessage::make($notification->toSms($notifiable));
        }

        if (method_exists($notification, 'toSentDmSms')) {
            return SentDmSmsMessage::make($notification->toSentDmSms($notifiable));
        }

        if (property_exists($notification, 'message') && is_string($notification->message)) {
            return SentDmSmsMessage::make($notification->message);
        }

        throw new InvalidArgumentException(sprintf(
            'Notification [%s] must define a toSms() or toSentDmSms() method for the sms channel.',
            $notification::class,
        ));
    }

    protected function routeNotificationForSms(object $notifiable, Notification $notification): mixed
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            return $notifiable->routeNotificationFor('sms', $notification);
        }

        if (method_exists($notifiable, 'routeNotificationForSms')) {
            return $notifiable->routeNotificationForSms($notification);
        }

        return null;
    }
}
