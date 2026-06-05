<?php

namespace Codepreneur\SentDm\Concerns;

trait InteractsWithSentDmSms
{
    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    /**
     * @return array<int, string>
     */
    protected function smsChannelsFor(object $notifiable): array
    {
        if (! filled($this->resolveSmsNumber($notifiable))) {
            return [];
        }

        return ['sms'];
    }

    protected function resolveSmsNumber(object $notifiable): mixed
    {
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $number = $notifiable->routeNotificationFor('sms', $this);

            if (filled($number)) {
                return $number;
            }
        }

        if (method_exists($notifiable, 'routeNotificationForSms')) {
            $number = $notifiable->routeNotificationForSms($this);

            if (filled($number)) {
                return $number;
            }
        }

        return null;
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        if ($channel !== 'sms') {
            return true;
        }

        return filled($this->resolveSmsNumber($notifiable));
    }

    /**
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'sms' => config('sent-dm.sms.queue', 'default'),
        ];
    }
}
