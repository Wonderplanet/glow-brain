<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Constants\MissionStatus;
use App\Constants\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent as BaseUsrMissionEvent;
use App\Models\Mst\MstMissionEvent;
use Carbon\CarbonImmutable;

class UsrMissionEvent extends BaseUsrMissionEvent
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(
        string $usrUserId,
        string $mstMissionId,
        int $missionType,
    ): self {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->mission_type = $missionType;
        $model->mst_mission_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR;
        $model->progress = 0;
        $model->cleared_at = null;
        $model->latest_reset_at = now();

        return $model;
    }

    public function mst_mission()
    {
        return $this->hasOne(MstMissionEvent::class, 'id', 'mst_mission_id');
    }

    public function getStatusLabelAttribute(): string
    {
        $enum = MissionStatus::from($this->status);

        return $enum?->label() ?? '';
    }

    public function getStatusBadgeAttribute(): string
    {
        $enum = MissionStatus::from($this->status);

        return $enum?->badge() ?? '';
    }

    public function unclear(): void
    {
        $this->status = MissionStatus::UNCLEAR;
        $this->cleared_at = null;
        $this->latest_reset_at = now();
    }

    public function clear(): void
    {
        $this->status = MissionStatus::CLEAR;
        $this->cleared_at = null;
        $this->latest_reset_at = now();
    }

    public function receiveReward(CarbonImmutable $now): void
    {
        $this->status = MissionStatus::RECEIVED_REWARD;
        $this->is_open = MissionUnlockStatus::OPEN;
        $this->received_reward_at = $now;
        if ($this->cleared_at === null) {
            $this->cleared_at = $now->toDateTimeString();
        }
    }

    public function updateUnlockProgress(int $unlock_progress): void
    {
        $this->unlock_progress = $unlock_progress;
    }

    public function updateIsOpen(int $is_open): void
    {
        $this->is_open = $is_open;
    }

    public function updateProgress(int $progress): void
    {
        $this->progress = $progress;
    }

}
