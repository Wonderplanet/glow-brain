<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Message\Models\Eloquent\UsrMessage as BaseUsrMessage;
use App\Dtos\RewardDto;
use App\Models\Mng\MngMessage;

class UsrMessage extends BaseUsrMessage
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mng_message()
    {
        return $this->hasOne(MngMessage::class, 'id', 'mng_message_id');
    }

    /**
     * $this->rewardにアクセスした際に呼ばれる
     * @return RewardDto
     */
    public function getRewardAttribute()
    {
        return new RewardDto(
            $this->id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
