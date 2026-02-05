<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprStepUpGachaStepRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprStepUpGachaStepReward extends MstModel
{
    use HasFactory;

    protected $table = "opr_stepup_gacha_step_rewards";

    protected $guarded = [];

    protected $casts = [
        'id' => 'integer',
        'release_key' => 'integer',
        'opr_gacha_id' => 'string',
        'step_number' => 'integer',
        'loop_count_target' => 'integer',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->opr_gacha_id,
            $this->step_number,
            $this->loop_count_target,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
