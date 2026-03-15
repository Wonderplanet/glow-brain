<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Mission\Models\UsrMissionEventProgress as BaseUsrMissionEventProgress;

class UsrMissionEventProgress extends BaseUsrMissionEventProgress
{
    protected $connection = Database::TIDB_CONNECTION;

    public static function createAndInit(
        string $usrUserId,
        string $criterionKey,
    ): self {
        $model = new self();

        $model->usr_user_id = $usrUserId;
        $model->criterion_key = $criterionKey;
        $model->progress = 0;

        return $model;
    }
}
