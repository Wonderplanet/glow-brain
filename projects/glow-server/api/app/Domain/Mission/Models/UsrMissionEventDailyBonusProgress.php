<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;
use Carbon\CarbonImmutable;

/**
 * @property string $mst_mission_event_daily_bonus_schedule_id
 * @property int $progress
 * @property null|string $latest_update_at
 */
class UsrMissionEventDailyBonusProgress extends UsrEloquentModel implements UsrMissionEventDailyBonusProgressInterface
{
    use HasFactory;

    protected $table = 'usr_mission_event_daily_bonus_progresses';

    protected $guarded = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_mission_event_daily_bonus_schedule_id;
    }

    public function getMstMissionEventDailyBonusScheduleId(): string
    {
        return $this->mst_mission_event_daily_bonus_schedule_id;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getLatestUpdateAt(): ?string
    {
        return $this->latest_update_at;
    }

    public function incrementProgress(CarbonImmutable $now): void
    {
        $this->progress++;
        $this->latest_update_at = $now->toDateTimeString();
    }
}
