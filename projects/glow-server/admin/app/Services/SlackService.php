<?php

namespace App\Services;

use Illuminate\Notifications\Notifiable;
use App\Notifications\SlackNotification;

class SlackService
{
    use Notifiable;

    public static function send($message = null): void
    {
        if (env('SLACK_WEBHOOK_URL') === null || !env('SLACK_ACTIVE', false)) {
            return;
        }
        $service = new self();
        $service->notify(new SlackNotification($message));
    }

    protected function routeNotificationForSlack()
    {
        // glow_ocarina-wpチャンネルのWebhook URL
        return env('SLACK_WEBHOOK_URL');
    }
}
