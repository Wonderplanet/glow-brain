<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStageRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstStageReward extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'mst_stage_id' => 'string',
        'reward_category' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'percentage' => 'integer',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_stage_id,
            $this->reward_category,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->percentage,
            $this->release_key,
        );
    }
}
