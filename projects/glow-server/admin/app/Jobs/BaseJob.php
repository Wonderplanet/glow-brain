<?php

namespace App\Jobs;

use App\Constants\SystemConstants;
use App\Models\Adm\AdmUser;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 各ジョブクラスのベースクラス
 */
class BaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // ジョブ実行のタイムアウト設定
    public int $timeout = SystemConstants::MAX_TIME_LIMIT;

    // ジョブがタイムアウトで失敗したとマークするかを指定
    public bool $failOnTimeout = true;

    /** 継承先で定義する変数群 **/
    // 管理ユーザーへの通知用
    public ?string $admUserId;

    // 集計レポートなどで使用。使用しない場合はnullになる
    public ?string $fileName = null;

    // 実行クラス名を格納する。エラー時のログで使用
    public string $className;

    // エラー時のデータベース通知メッセージを格納
    public string $failedErrorMsg;

    /**
     * エラー発生時に実行する処理を定義
     *
     * @param ?Throwable $exception
     * @return void
     */
    public function failed(?Throwable $exception): void
    {
        // メモリ最大使用量をログに残す(エラーになった場合の参考用)
        $maxMemorySize = floor((memory_get_peak_usage()) / 1024 / 1024);
        // データベース通知で失敗を送信
        $this->notification(
            'danger',
            $this->failedErrorMsg
        );
        Log::error("[queue]{$this->className} Error maxMemorySize:{$maxMemorySize}MB ", [$exception]);
    }

    /**
     * 管理者ユーザーへの通知を送信する
     *
     * @param string $status
     * @param string $message
     * @return void
     */
    public function notification(string $status, string $message): void
    {
        return;
        if (is_null($this->admUserId)) {
            // もし管理ユーザーIDが取得できなかった場合は通知せず終了する
            Log::warning("{$this->className} undefined admUserId fileName:{$this->fileName}");
            return;
        }

        // 送信対象の管理者ユーザー取得
        /** @var AdmUser $admUser */
        $admUser = AdmUser::query()->find($this->admUserId);

        // データベース通知作成
        $admUser->notifyNow(
            Notification::make()
                ->title($message)
                ->status($status)
                ->toDatabase()
        );
    }
}
