<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\JumpPlus\Models\UsrJumpPlusReward as BaseUsrJumpPlusReward;
use App\Models\Mng\MngJumpPlusRewardSchedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsrJumpPlusReward extends BaseUsrJumpPlusReward
{
    protected $connection = Database::TIDB_CONNECTION;
    public $timestamps = true;

    public function mng_jump_plus_reward_schedules(): BelongsTo
    {
        return $this->belongsTo(MngJumpPlusRewardSchedule::class, 'mng_jump_plus_reward_schedule_id', 'id');
    }
}
