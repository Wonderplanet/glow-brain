<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Constants\MissionStatus;
use App\Constants\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionLimitedTerm as BaseUsrMissionLimitedTerm;
use Carbon\CarbonImmutable;

class UsrMissionLimitedTerm extends BaseUsrMissionLimitedTerm
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(string $usrUserId, string $mstMissionId): self
    {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->mst_mission_limited_term_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR;
        $model->progress = 0;
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

    public function unclear(): void
    {
        $this->status = MissionStatus::UNCLEAR;
        $this->cleared_at = null;
        $this->received_reward_at = null;
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

    public function updateProgress(int $progress): void
    {
        $this->progress = $progress;
    }

    public function clear(): void
    {
        $this->status = MissionStatus::CLEAR;
        $this->cleared_at = null;
        $this->latest_reset_at = now();
    }

    public function updateIsOpen(int $is_open): void
    {
        $this->is_open = $is_open;
    }
}
