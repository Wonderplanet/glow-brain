<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionRewardEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstMissionReward extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'group_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'resource_amount' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'group_id',
        'resource_type',
        'resource_id',
        'resource_amount',
        'sort_order',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->group_id,
            $this->resource_type,
            $this->resource_id,
            $this->resource_amount,
            $this->sort_order,
        );
    }
}
