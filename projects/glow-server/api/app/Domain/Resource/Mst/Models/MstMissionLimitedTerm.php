<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int    $release_key
 * @property string $progress_group_key
 * @property string $criterion_type
 * @property string $criterion_value
 * @property int    $criterion_count
 * @property string $mission_category
 * @property string $mst_mission_reward_group_id
 * @property int    $sort_order
 * @property string $destination_scene
 * @property string $start_at
 * @property string $end_at
 */
class MstMissionLimitedTerm extends MstModel
{
    use HasFactory;

    public $table = 'mst_mission_limited_terms';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'progress_group_key' => 'string',
        'criterion_type' => 'string',
        'criterion_value' => 'string',
        'criterion_count' => 'integer',
        'mission_category' => 'string',
        'mst_mission_reward_group_id' => 'string',
        'sort_order' => 'integer',
        'destination_scene' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->progress_group_key,
            $this->criterion_type,
            $this->criterion_value,
            $this->criterion_count,
            $this->mission_category,
            $this->mst_mission_reward_group_id,
            $this->sort_order,
            $this->destination_scene,
            $this->start_at,
            $this->end_at,
        );
    }
}
