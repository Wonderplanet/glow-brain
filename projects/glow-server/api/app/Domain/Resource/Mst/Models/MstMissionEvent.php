<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionEventEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int    $release_key
 * @property string $mst_event_id
 * @property string $criterion_type
 * @property string $criterion_value
 * @property int    $criterion_count
 * @property string $unlock_criterion_type
 * @property string $unlock_criterion_value
 * @property int    $unlock_criterion_count
 * @property string $group_key
 * @property string $mst_mission_reward_group_id
 * @property string $event_category
 * @property int    $sort_order
 * @property string $destination_scene
 */
class MstMissionEvent extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    public $table = 'mst_mission_events';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_event_id' => 'string',
        'criterion_type' => 'string',
        'criterion_value' => 'string',
        'criterion_count' => 'integer',
        'unlock_criterion_type' => 'string',
        'unlock_criterion_value' => 'string',
        'unlock_criterion_count' => 'integer',
        'group_key' => 'string',
        'mst_mission_reward_group_id' => 'string',
        'event_category' => 'string',
        'sort_order' => 'integer',
        'destination_scene' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->mst_event_id,
            $this->criterion_type,
            $this->criterion_value,
            $this->criterion_count,
            $this->unlock_criterion_type,
            $this->unlock_criterion_value,
            $this->unlock_criterion_count,
            $this->group_key,
            $this->mst_mission_reward_group_id,
            $this->event_category,
            $this->sort_order,
            $this->destination_scene,
        );
    }
}
