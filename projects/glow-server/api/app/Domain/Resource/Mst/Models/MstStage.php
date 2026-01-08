<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstStageEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstStage extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_quest_id' => 'string',
        'cost_stamina' => 'integer',
        'exp' => 'integer',
        'coin' => 'integer',
        'mst_artwork_fragment_drop_group_id' => 'string',
        'prev_mst_stage_id' => 'string',
        'auto_lap_type' => 'string',
        'max_auto_lap_count' => 'integer',
        'sort_order' => 'integer',
        'player_outpost_asset_key' => 'string',
        'release_key' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_quest_id,
            $this->cost_stamina,
            $this->exp,
            $this->coin,
            $this->mst_artwork_fragment_drop_group_id,
            $this->prev_mst_stage_id,
            $this->auto_lap_type,
            $this->max_auto_lap_count,
            $this->sort_order,
            $this->release_key,
            $this->start_at,
            $this->end_at,
        );
    }
}
