<?php

namespace Codepreneur\SentDm\Notifications;

use Codepreneur\SentDm\Concerns\InteractsWithSentDmSms;
use Codepreneur\SentDm\Messages\SentDmSmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SmsNotification extends Notification implements ShouldQueue
{
    use InteractsWithSentDmSms;
    use Queueable;

    public function __construct(public string|SentDmSmsMessage $message) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->smsChannelsFor($notifiable);
    }

    public function toSms(object $notifiable): SentDmSmsMessage
    {
        return SentDmSmsMessage::make($this->message);
    }
}
