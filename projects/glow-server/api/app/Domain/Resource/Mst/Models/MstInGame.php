<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstInGame extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_auto_player_sequence_id' => 'string',
        'mst_auto_player_sequence_set_id' => 'string',
        'bgm_asset_key' => 'string',
        'boss_bgm_asset_key' => 'string',
        'loop_background_asset_key' => 'string',
        'player_outpost_asset_key' => 'string',
        'mst_page_id' => 'string',
        'mst_enemy_outpost_id' => 'string',
        'mst_defense_target_id' => 'string',
        'boss_mst_enemy_stage_parameter_id' => 'string',
        'boss_count' => 'integer',
        'normal_enemy_hp_coef' => 'float',
        'normal_enemy_attack_coef' => 'float',
        'normal_enemy_speed_coef' => 'float',
        'boss_enemy_hp_coef' => 'float',
        'boss_enemy_attack_coef' => 'float',
        'boss_enemy_speed_coef' => 'float',
    ];
}
