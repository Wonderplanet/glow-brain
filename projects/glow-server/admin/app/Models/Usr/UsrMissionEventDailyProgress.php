<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Mission\Models\UsrMissionEventDailyProgress as BaseUsrMissionEventDailyProgress;
use Carbon\CarbonImmutable;

class UsrMissionEventDailyProgress extends BaseUsrMissionEventDailyProgress
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(
        string $usrUserId,
        string $criterionKey,
        CarbonImmutable $now,
    ): self {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->criterion_key = $criterionKey;
        $model->progress = 0;
        $model->latest_update_at = $now->toDateTimeString();

        return $model;
    }
}
