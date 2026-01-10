<?php

declare(strict_types=1);

namespace App\Services\Datalake;

use Carbon\CarbonImmutable;

/**
 * データレイクSlack通知コマンドサービス
 */
class DatalakeSlackNotificationCommandService
{
    public function __construct(
        private DatalakeSlackNotificationService $slackNotificationService,
    ) { }

    public function exec(CarbonImmutable $executionTime): void
    {
        // 集計対象となる日付を取得
        $targetDate = $executionTime->subDay()->startOfDay();

        $this->slackNotificationService->notifySlackForTargetDate($targetDate);
    }
}
