<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\DateTimeRange;
use Carbon\CarbonImmutable;

/**
 * 時間計算関連の共通処理をまとめたサービスクラス
 */
class ClockService
{
    public function __construct(
        // Common
        private Clock $clock,
    ) {
    }

    /**
     * 指定した日数分の開始日時と終了日時を計算する
     *
     * @param CarbonImmutable $now
     * @param int $durationDays
     * @return DateTimeRange
     */
    public function calcDaysRange(CarbonImmutable $now, int $durationDays): DateTimeRange
    {
        // 期間をxx:00:00〜xx:59:59で厳密に設定する
        $startAt = $this->clock->calcDayStartDatetime($now);
        $endAt = $startAt->clone()->addDays($durationDays)->subSecond();
        return new DateTimeRange($startAt, $endAt);
    }
}
