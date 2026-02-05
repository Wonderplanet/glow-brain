<?php

declare(strict_types=1);

namespace App\Traits;
use Filament\Actions\Concerns\CanNotify;
use Filament\Notifications\Notification;
use Filament\Support\Concerns\EvaluatesClosures;

trait NotificationTrait
{
    use CanNotify;
    use EvaluatesClosures;

    public function sendDangerNotification(string $title, string $body): void
    {
        $this->failureNotification(
            Notification::make()
                ->danger()
                ->title($title)
                ->body($body)
                ->persistent() // ページ更新されるまで表示し続ける
            );
        $this->sendFailureNotification();
    }

    public function sendProcessCompletedNotification(string $title, string $body): void
    {
        $this->successNotification(
            Notification::make()
                ->success()
                ->title($title)
                ->body($body)
                ->persistent() // ページ更新されるまで表示し続ける
        );
        $this->sendSuccessNotification();
    }
}
