<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstAdventBattleI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_advent_battle_id' => 'string',
        'language' => 'string',
        'name' => 'string',
    ];
}
