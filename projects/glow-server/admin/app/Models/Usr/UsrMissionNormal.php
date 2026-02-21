<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Constants\MissionStatus;
use App\Constants\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal as BaseUsrMissionNormal;
use Carbon\CarbonImmutable;

class UsrMissionNormal extends BaseUsrMissionNormal
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(
        string $usrUserId,
        string $mstMissionId,
        int $missionType,
        CarbonImmutable $now,
    ): self {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->mission_type = $missionType;
        $model->mst_mission_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR;
        $model->is_open = MissionUnlockStatus::LOCK;
        $model->progress = 0;
        $model->unlock_progress = 0;
        $model->latest_reset_at = $now->toDateTimeString();
        $model->cleared_at = null;
        $model->received_reward_at = null;

        return $model;
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

    public function unclear(CarbonImmutable $now): void
    {
        $this->status = MissionStatus::UNCLEAR;
        $this->cleared_at = null;
        $this->received_reward_at = null;
        $this->latest_reset_at = $now->toDateTimeString();
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

    public function updateProgress(int $progress): void
    {
        $this->progress = $progress;
    }

    public function updateIsOpen(int $is_open): void
    {
        $this->is_open = $is_open;
    }

    public function clear(CarbonImmutable $now): void
    {
        $this->status = MissionStatus::CLEAR->value;
        $this->cleared_at = $now->toDateTimeString();
    }

}
