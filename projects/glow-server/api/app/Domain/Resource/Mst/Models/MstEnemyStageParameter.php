<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstEnemyStageParameter extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_enemy_character_id' => 'string',
        'character_unit_kind' => 'string',
        'role_type' => 'string',
        'color' => 'string',
        'sort_order' => 'integer',
        'hp' => 'integer',
        'damage_knock_back_count' => 'integer',
        'move_speed' => 'integer',
        'well_distance' => 'float',
        'attack_power' => 'integer',
        'attack_combo_cycle' => 'integer',
        'mst_unit_ability_id1' => 'string',
        'drop_battle_point' => 'integer',
        'mst_transformation_enemy_stage_parameter_id' => 'string',
        'transformation_condition_type' => 'string',
        'transformation_condition_value' => 'string',
    ];
}
