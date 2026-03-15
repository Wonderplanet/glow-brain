<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionAchievementEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstMissionAchievement extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'criterion_type' => 'string',
        'criterion_value' => 'string',
        'criterion_count' => 'integer',
        'unlock_criterion_type' => 'string',
        'unlock_criterion_value' => 'string',
        'unlock_criterion_count' => 'integer',
        'group_key' => 'string',
        'mst_mission_reward_group_id' => 'string',
        'sort_order' => 'integer',
        'destination_scene' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->criterion_type,
            $this->criterion_value,
            $this->criterion_count,
            $this->unlock_criterion_type,
            $this->unlock_criterion_value,
            $this->unlock_criterion_count,
            $this->group_key,
            $this->mst_mission_reward_group_id,
            $this->sort_order,
            $this->destination_scene,
        );
    }
}
