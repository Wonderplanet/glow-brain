<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Constants\MissionStatus;
use App\Domain\Mission\Models\UsrMissionEventDailyBonus as BaseUsrMissionEventDailyBonus;
use App\Models\Mst\MstMissionEventDailyBonus;
use Carbon\CarbonImmutable;

class UsrMissionEventDailyBonus extends BaseUsrMissionEventDailyBonus
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(
        string $usrUserId,
        string $mstMissionId,
    ): self {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->mst_mission_event_daily_bonus_id = $mstMissionId;
        $model->status = MissionStatus::UNCLEAR;
        $model->cleared_at = null;
        $model->received_reward_at = null;

        return $model;
    }

    public function mst_mission()
    {
        return $this->hasOne(MstMissionEventDailyBonus::class, 'id', 'mst_mission_event_daily_bonus_id');
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
    }

    public function receiveReward(CarbonImmutable $now): void
    {
        parent::receiveReward($now);

        if ($this->cleared_at === null) {
            $this->cleared_at = $now->toDateTimeString();
        }
    }
}
