<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstKomaLine extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_page_id' => 'string',
        'row' => 'integer',
        'height' => 'float',
        'koma_line_layout_asset_key' => 'string',
        'koma1_asset_key' => 'string',
        'koma1_width' => 'float',
        'koma1_back_ground_offset' => 'float',
        'koma1_effect_type' => 'string',
        'koma1_effect_parameter1' => 'string',
        'koma1_effect_parameter2' => 'string',
        'koma1_effect_target_side' => 'string',
        'koma1_effect_target_colors' => 'string',
        'koma1_effect_target_roles' => 'string',
        'koma2_asset_key' => 'string',
        'koma2_width' => 'float',
        'koma2_back_ground_offset' => 'float',
        'koma2_effect_type' => 'string',
        'koma2_effect_parameter1' => 'string',
        'koma2_effect_parameter2' => 'string',
        'koma2_effect_target_side' => 'string',
        'koma2_effect_target_colors' => 'string',
        'koma2_effect_target_roles' => 'string',
        'koma3_asset_key' => 'string',
        'koma3_width' => 'float',
        'koma3_back_ground_offset' => 'float',
        'koma3_effect_type' => 'string',
        'koma3_effect_parameter1' => 'string',
        'koma3_effect_parameter2' => 'string',
        'koma3_effect_target_side' => 'string',
        'koma3_effect_target_colors' => 'string',
        'koma3_effect_target_roles' => 'string',
        'koma4_asset_key' => 'string',
        'koma4_width' => 'float',
        'koma4_back_ground_offset' => 'float',
        'koma4_effect_type' => 'string',
        'koma4_effect_parameter1' => 'string',
        'koma4_effect_parameter2' => 'string',
        'koma4_effect_target_side' => 'string',
        'koma4_effect_target_colors' => 'string',
        'koma4_effect_target_roles' => 'string',
    ];
}
