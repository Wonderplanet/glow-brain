<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionBeginnerEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstMissionBeginner extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'criterion_type' => 'string',
        'criterion_value' => 'string',
        'criterion_count' => 'integer',
        'unlock_day' => 'integer',
        'group_key' => 'string',
        'bonus_point' => 'integer',
        'mst_mission_reward_group_id' => 'string',
        'sort_order' => 'integer',
        'destination_scene' => 'string',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->criterion_type,
            $this->criterion_value,
            $this->criterion_count,
            $this->unlock_day,
            $this->group_key,
            $this->bonus_point,
            $this->mst_mission_reward_group_id,
            $this->sort_order,
        );
    }
}
