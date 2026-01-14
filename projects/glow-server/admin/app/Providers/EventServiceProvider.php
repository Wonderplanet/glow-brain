<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommandExecutionLogger();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    /**
     * コマンド実行のログ出力を登録
     * @return void
     */
    private function registerCommandExecutionLogger(): void
    {
        // コマンド実行開始ログ
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (!$this->shouldLogCommand($event->command)) {
                return;
            }

            Log::info("コマンド実行開始", [
                'command' => $event->command,
                'start_time' => CarbonImmutable::now()->format('Y-m-d H:i:s'),
            ]);
        });

        // コマンド実行完了ログ
        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            if (!$this->shouldLogCommand($event->command)) {
                return;
            }

            $message = $event->exitCode === 0 ? "コマンド実行成功" : "コマンド実行失敗";
            Log::info($message, [
                'command' => $event->command,
                'end_time' => CarbonImmutable::now()->format('Y-m-d H:i:s'),
                'exit_code' => $event->exitCode,
            ]);
        });
    }

    /**
     * コマンドをログ出力対象とするかどうかを判定
     *
     * 全てのコマンドでログ出力すると、ECS環境でタスク起動時の処理でlaravel.logがroot所有者で作成されてしまい
     * それ以降の処理でpermissionエラーになってしまったので、ログ出力するコマンドを絞る
     *
     * @param string $command
     * @return bool
     */
    private function shouldLogCommand(string $command): bool
    {
        return str_contains($command, 'app:');
    }
}
