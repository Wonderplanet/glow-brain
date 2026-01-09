<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStageClearTimeRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstStageClearTimeReward extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'mst_stage_id' => 'string',
        'upper_clear_time_ms' => 'integer',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_stage_id,
            $this->upper_clear_time_ms,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->release_key,
        );
    }
}
