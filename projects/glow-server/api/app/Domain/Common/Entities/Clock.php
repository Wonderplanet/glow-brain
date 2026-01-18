<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

use Carbon\CarbonImmutable;

/**
 * 全メソッドの引数はUTCで、returnもUTCで統一する。
 */
class Clock
{
    public const DEFAULT_TIMEZONE = 'UTC';

    // 日跨ぎ計算を行うタイムゾーン
    public const LOGIC_TIMEZONE = 'Asia/Tokyo';

    // ゲーム内の日跨ぎ時間
    public const BORDER_TIME_DAY_START = '04:00:00';

    // 現実の日跨ぎ時間
    public const REAL_BORDER_TIME_DAY_START = '00:00:00';

    public const WEEK_START_DAY = CarbonImmutable::MONDAY;

    public const DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const DAY_START_FORMAT = 'Y-m-d ' . self::BORDER_TIME_DAY_START;

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    public function setDefaultTimezone(CarbonImmutable $targetAt): CarbonImmutable
    {
        return $targetAt->setTimezone(self::DEFAULT_TIMEZONE);
    }

    public function setLogicTimezone(CarbonImmutable $targetAt): CarbonImmutable
    {
        return $targetAt->setTimezone(self::LOGIC_TIMEZONE);
    }

    /**
     * 1日の始まりの日時を返す
     */
    public function getDayStartDatetime(): CarbonImmutable
    {
        return $this->calcDayStartDatetime($this->now());
    }

    /**
     * 1日の始まりの日時を返す
     */
    public function calcDayStartDatetime(CarbonImmutable $targetAt): CarbonImmutable
    {
        $targetAt = $this->setLogicTimezone($targetAt);
        if ($targetAt->format(self::DAY_START_FORMAT) > $targetAt->format(self::DATETIME_FORMAT)) {
            $targetAt = $targetAt->subDay();
        }

        return $this->setDefaultTimezone(
            $targetAt->setTimeFromTimeString(self::BORDER_TIME_DAY_START)
        );
    }

    /**
     * 日跨ぎ判定
     * true: 日跨ぎしている、false: 日跨ぎしていない
     */
    public function isFirstToday(string $targetAt): bool
    {
        return CarbonImmutable::parse($targetAt) < $this->getDayStartDatetime();
    }

    /**
     * 週跨ぎ判定
     * true: 週跨ぎしている、false: 週跨ぎしていない
     *
     * @param string $targetAt
     * @return bool
     */
    public function isFirstWeek(string $targetAt): bool
    {
        return CarbonImmutable::parse($targetAt) < $this->getWeekStartDatetime();
    }

    /**
     * 月跨ぎ判定
     * true: 月跨ぎしている、false: 月跨ぎしていない
     *
     * @param string $targetAt
     * @return bool
     */
    public function isFirstMonth(string $targetAt): bool
    {
        return CarbonImmutable::parse($targetAt) < $this->getMonthStartDatetime();
    }

    /**
     * 週の始まりの日時を返す
     */
    public function getWeekStartDatetime(): CarbonImmutable
    {
        return $this->calcWeekStartDatetime($this->now());
    }

    /**
     * 週の始まりの日時を計算する
     */
    public function calcWeekStartDatetime(CarbonImmutable $targetAt): CarbonImmutable
    {
        return $this->setDefaultTimezone(
            $this->setLogicTimezone($this->calcDayStartDatetime($targetAt))
                ->startOfWeek(self::WEEK_START_DAY)
                ->setTimeFromTimeString(self::BORDER_TIME_DAY_START)
        );
    }

    /**
     * 月の始まりの日時を返す
     */
    public function getMonthStartDatetime(): CarbonImmutable
    {
        return $this->calcMonthStartDatetime($this->now());
    }

    /**
     * 月の始まりの日時を計算する
     */
    public function calcMonthStartDatetime(
        CarbonImmutable $targetAt,
    ): CarbonImmutable {
        return $this->setDefaultTimezone(
            $this->setLogicTimezone($this->calcDayStartDatetime($targetAt))
                ->startOfMonth()
                ->setTimeFromTimeString(self::BORDER_TIME_DAY_START)
        );
    }

    /**
     * 指定日時と現在日時で何日差があるかを返す
     */
    public function diffDays(string $targetAt, bool $absolute = true): int
    {
        return (int) $this->getDayStartDatetime()
            ->diffInDays(
                $this->calcDayStartDatetime(CarbonImmutable::parse($targetAt)),
                $absolute,
            );
    }

    /**
     * 何日差があるかを返す
     *
     * @param bool $absolute true: 絶対値を返す、false: 差分を返す(dateTime2 - dateTime1)
     */
    public function diffDaysBetween(CarbonImmutable $dateTime1, CarbonImmutable $dateTime2, bool $absolute = true): int
    {
        return (int) $this->calcDayStartDatetime($dateTime1)
            ->diffInDays(
                $this->calcDayStartDatetime($dateTime2),
                $absolute,
            );
    }

    /**
     * 指定日時と現在日時を比較して、連続日ログインしているかどうかを返す
     * true: 連続日ログインしている、false: 連続日ログインしていない
     */
    public function isContinuousLogin(string $beforeLoginAt): bool
    {
        return $this->diffDays($beforeLoginAt) === 1;
    }

    /**
     * 現在日時から指定された経過日数分前の日の開始日時を返す
     *
     * 例：現在日時が2024/04/10 12:00:00で、引数が2の場合、2024/04/08 00:00:00を返す (1日の始まりが00:00:00の例)
     */
    public function calcDayStartAtFromElapsedDays(int $elapsedDays): CarbonImmutable
    {
        return $this->calcDayStartDatetime($this->now())->subDays($elapsedDays);
    }

    /**
     * afterAtの日時がbeforeAtの日時と比較して、翌日以降の日時かどうかを返す
     */
    public function isAfterDay(string $beforeAt, string $afterAt): bool
    {
        return $this->isAfterAt(
            $this->calcDayStartDatetime(CarbonImmutable::parse($beforeAt))->format(self::DATETIME_FORMAT),
            $this->calcDayStartDatetime(CarbonImmutable::parse($afterAt))->format(self::DATETIME_FORMAT),
        );
    }

    /**
     * 「$beforeAt < $afterAt」となっているかどうかを返す
     */
    public function isAfterAt(string $beforeAt, string $afterAt): bool
    {
        return CarbonImmutable::parse($afterAt)->gt(CarbonImmutable::parse($beforeAt));
    }
}
