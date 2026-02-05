<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstEnemyOutpost extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'hp' => 'integer',
        'outpost_asset_key' => 'string',
        'artwork_asset_key' => 'string',
        'is_damage_invalidation' => 'integer',
        'release_key' => 'integer',
    ];
}
