<?php

namespace App\Models\Mng;

use App\Domain\Resource\Mng\Models\MngMessageReward as BaseMngMessageReward;
use App\Dtos\RewardDto;

class MngMessageReward extends BaseMngMessageReward
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mngMessage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MngMessage::class);
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
