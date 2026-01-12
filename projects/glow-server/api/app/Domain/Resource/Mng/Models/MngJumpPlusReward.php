<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Models;

use App\Domain\Resource\Mng\Entities\MngJumpPlusRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MngJumpPlusReward extends MngModel
{
    use HasFactory;

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'group_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->group_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
        );
    }
}
