<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstMangaAnimation extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'mst_stage_id' => 'string',
        'condition_type' => 'string',
        'condition_value' => 'string',
        'animation_start_delay' => 'integer',
        'animation_speed' => 'float',
        'is_pause' => 'integer',
        'can_skip' => 'integer',
        'asset_key' => 'string',
    ];
}
