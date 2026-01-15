<?php

declare(strict_types=1);

namespace App\Services\Datalake;

use App\Constants\DatalakeConstant;
use App\Constants\DatalakeStatus;
use App\Models\Adm\AdmDatalakeLog;
use App\Notifications\DatalakeSlackNotification;
use App\Repositories\Adm\AdmDatalakeLogRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

/**
 * データレイクSlack通知サービス
 */
class DatalakeSlackNotificationService
{
    public function __construct(
        private AdmDatalakeLogRepository $admDatalakeLogRepository,
    ) { }

    /**
     * 対象日の転送状態をチェックして通知
     * @param CarbonImmutable $targetDate
     * @return void
     */
    public function notifySlackForTargetDate(CarbonImmutable $targetDate): void
    {
        $dateNum = (int)$targetDate->format('Ymd');
        $admDatalakeLog = $this->admDatalakeLogRepository->getByDate($dateNum);
        $notifyMessage = $this->createNotifyMessage($admDatalakeLog);

        Notification::route('slack', config('services.slack.datalake_webhook_url'))
            ->notify(new DatalakeSlackNotification($notifyMessage));
    }

    /**
     * ログの通知メッセージを生成
     * @param AdmDatalakeLog|null $admDatalakeLog
     * @return string
     */
    public function createNotifyMessage(?AdmDatalakeLog $admDatalakeLog): string
    {
        $dateText = null;
        if ($admDatalakeLog === null) {
            // 転送ログが存在しない場合
            $message = DatalakeConstant::SLACK_MESSAGE_UNKNOWN;
        } else {
            $tryText = match($admDatalakeLog->getTryCount()) {
                1 => '基本転送機構',
                2 => '定期自動実行(再送)',
                3 => '手動実行',
                default => "{$admDatalakeLog->getTryCount()}回目の転送",
            };
            $dateText = CarbonImmutable::createFromFormat('Ymd', (string)$admDatalakeLog->getDate())->format('Y/m/d');
            if ($admDatalakeLog->getIsTransfer()) {
                // 転送中の場合
                $message = DatalakeConstant::SLACK_MESSAGE_RUNNING;
            } else {
                $status = DatalakeStatus::tryFrom($admDatalakeLog->getStatus());
                if ($status === null) {
                    $message = "不明な転送進捗({$admDatalakeLog->getStatus()})";
                } else {
                    $message = match($status) {
                        DatalakeStatus::COMPLETED => '完了',
                        default => "未完了({$status->label()})",
                    };

                    // 再送転送時が未完了の時のみアラートを付け足す
                    if ($status !== DatalakeStatus::COMPLETED && $admDatalakeLog->getTryCount() === 2) {
                        $message .= "\n" . DatalakeConstant::SLACK_MESSAGE_ALERT
                            . "\n" . sprintf(DatalakeConstant::SLACK_MESSAGE_URL, config('app.url'));
                    }
                }
            }
        }
        return sprintf(DatalakeConstant::SLACK_MESSAGE_BASE,
            $dateText ?? '',
            $tryText ?? '',
            $message,
        );
    }
}
