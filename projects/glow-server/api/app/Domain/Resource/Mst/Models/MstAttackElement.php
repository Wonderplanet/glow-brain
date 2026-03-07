<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstAttackElement extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    // すべてのプロパティの一括代入を可能とする
    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_attack_id' => 'string',
        'sort_order' => 'integer',
        'attack_delay' => 'integer',
        'attack_type' => 'string',
        'range_start_type' => 'string',
        'range_start_parameter' => 'float',
        'range_end_type' => 'string',
        'range_end_parameter' => 'float',
        'max_target_count' => 'integer',
        'target' => 'string',
        'target_type' => 'string',
        'target_colors' => 'string',
        'target_roles' => 'string',
        'target_mst_series_ids' => 'string',
        'target_mst_character_ids' => 'string',
        'damage_type' => 'string',
        'hit_type' => 'string',
        'hit_parameter1' => 'integer',
        'hit_parameter2' => 'integer',
        'hit_effect_id' => 'string',
        'is_hit_stop' => 'integer',
        'probability' => 'integer',
        'power_parameter_type' => 'string',
        'power_parameter' => 'integer',
        'effect_type' => 'string',
        'effective_count' => 'integer',
        'effective_duration' => 'integer',
        'effect_parameter' => 'float',
        'effect_value' => 'string',
        'effect_trigger_roles' => 'string',
        'effect_trigger_colors' => 'string',
    ];
}
