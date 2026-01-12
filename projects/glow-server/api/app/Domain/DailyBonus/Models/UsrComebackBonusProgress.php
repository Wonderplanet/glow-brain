<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $mst_comeback_bonus_schedule_id
 * @property int $start_count
 * @property int $progress
 * @property null|string $latest_update_at
 * @property string $start_at
 * @property string $end_at
 */
class UsrComebackBonusProgress extends UsrEloquentModel implements UsrComebackBonusProgressInterface
{
    use HasFactory;

    protected $table = 'usr_comeback_bonus_progresses';

    protected $guarded = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_comeback_bonus_schedule_id;
    }

    public function getMstScheduleId(): string
    {
        return $this->getMstComebackBonusScheduleId();
    }

    public function getMstComebackBonusScheduleId(): string
    {
        return $this->mst_comeback_bonus_schedule_id;
    }

    public function getStartCount(): int
    {
        return $this->start_count;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getLatestUpdateAt(): ?string
    {
        return $this->latest_update_at;
    }

    public function getStartAt(): string
    {
        return $this->start_at;
    }

    public function getEndAt(): string
    {
        return $this->end_at;
    }

    public function resetTerm(CarbonImmutable $startAt, CarbonImmutable $endAt): void
    {
        $this->start_at = $startAt->toDateTimeString();
        $this->end_at = $endAt->toDateTimeString();
    }

    public function incrementProgress(CarbonImmutable $now): void
    {
        $this->progress++;
        $this->latest_update_at = $now->toDateTimeString();
    }

    public function resetProgress(CarbonImmutable $now): void
    {
        $this->progress = 0;
        $this->start_count++;
        $this->latest_update_at = $now->toDateTimeString();
    }
}
