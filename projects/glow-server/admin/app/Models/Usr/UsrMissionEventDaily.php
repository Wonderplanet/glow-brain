<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Constants\MissionStatus;
use App\Domain\Mission\Models\UsrMissionEventDaily as BaseUsrMissionEventDaily;
use App\Models\Mst\MstMissionEventDaily;
use Carbon\CarbonImmutable;

class UsrMissionEventDaily extends BaseUsrMissionEventDaily
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(
        string $usrUserId,
        string $mstMissionId,
        CarbonImmutable $now,
    ): self {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->mst_mission_event_daily_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR;
        $model->cleared_at = null;
        $model->received_reward_at = null;
        $model->latest_update_at = $now->toDateTimeString();

        return $model;
    }

    public function mst_mission()
    {
        return $this->hasOne(MstMissionEventDaily::class, 'id', 'mst_mission_event_daily_id');
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
        $this->latest_update_at = $now->toDateTimeString();
    }

    public function receiveReward(CarbonImmutable $now): void
    {
        parent::receiveReward($now);

        if ($this->cleared_at === null) {
            $this->cleared_at = $now->toDateTimeString();
        }
    }
}
