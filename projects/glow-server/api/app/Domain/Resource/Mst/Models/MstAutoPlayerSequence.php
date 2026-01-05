<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstAutoPlayerSequence extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'sequence_set_id' => 'string',
        'sequence_element_id' => 'string',
        'sequence_group_id' => 'string',
        'priority_sequence_element_id' => 'string',
        'condition_type' => 'string',
        'condition_value' => 'string',
        'action_type' => 'string',
        'action_value' => 'string',
        'action_value2' => 'string',
        'summon_animation_type' => 'string',
        'summon_count' => 'integer',
        'summon_interval' => 'integer',
        'action_delay' => 'integer',
        'summon_position' => 'float',
        'move_start_condition_type' => 'string',
        'move_start_condition_value' => 'integer',
        'move_stop_condition_type' => 'string',
        'move_stop_condition_value' => 'integer',
        'move_restart_condition_type' => 'string',
        'move_restart_condition_value' => 'integer',
        'move_loop_count' => 'integer',
        'last_boss_trigger' => 'integer',
        'aura_type' => 'string',
        'death_type' => 'string',
        'override_drop_battle_point' => 'integer',
        'defeated_score' => 'integer',
        'enemy_hp_coef' => 'float',
        'enemy_attack_coef' => 'float',
        'enemy_speed_coef' => 'float',
        'deactivation_condition_type' => 'string',
        'deactivation_condition_value' => 'string',
        'is_summon_unit_outpost_damage_invalidation' => 'integer',
        'release_key' => 'integer',
    ];
}
