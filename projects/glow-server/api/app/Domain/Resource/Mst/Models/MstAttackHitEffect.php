<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstAttackHitEffect extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'onomatopoeia1_asset_key' => 'string',
        'onomatopoeia2_asset_key' => 'string',
        'onomatopoeia3_asset_key' => 'string',
        'sound_effect_asset_key' => 'string',
        'killer_sound_effect_asset_key' => 'string',
    ];
}
