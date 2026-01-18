<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnit extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'fragment_mst_item_id' => 'string',
        'color' => 'string',
        'role_type' => 'string',
        'attack_range_type' => 'string',
        'unit_label' => 'string',
        'has_specific_rank_up' => 'integer',
        'mst_series_id' => 'string',
        'asset_key' => 'string',
        'rarity' => 'string',
        'sort_order' => 'integer',
        'summon_cost' => 'integer',
        'summon_cool_time' => 'integer',
        'min_hp' => 'integer',
        'max_hp' => 'integer',
        'damage_knock_back_count' => 'integer',
        'move_speed' => 'string',
        'well_distance' => 'float',
        'min_attack_power' => 'integer',
        'max_attack_power' => 'integer',
        'mst_unit_ability_id1' => 'string',
        'ability_unlock_rank1' => 'integer',
        'mst_unit_ability_id2' => 'string',
        'ability_unlock_rank2' => 'integer',
        'mst_unit_ability_id3' => 'string',
        'ability_unlock_rank3' => 'integer',
        'is_encyclopedia_special_attack_position_right' => 'integer',
        'release_key' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->fragment_mst_item_id,
            $this->color,
            $this->role_type,
            $this->attack_range_type,
            $this->unit_label,
            $this->has_specific_rank_up,
            $this->mst_series_id,
            $this->asset_key,
            $this->rarity,
            $this->sort_order,
            $this->summon_cost,
            $this->summon_cool_time,
            $this->min_hp,
            $this->max_hp,
            $this->damage_knock_back_count,
            $this->move_speed,
            $this->well_distance,
            $this->min_attack_power,
            $this->max_attack_power,
            $this->mst_unit_ability_id1,
            $this->ability_unlock_rank1,
            $this->mst_unit_ability_id2,
            $this->ability_unlock_rank2,
            $this->mst_unit_ability_id3,
            $this->ability_unlock_rank3,
            $this->is_encyclopedia_special_attack_position_right,
            $this->release_key,
        );
    }
}
