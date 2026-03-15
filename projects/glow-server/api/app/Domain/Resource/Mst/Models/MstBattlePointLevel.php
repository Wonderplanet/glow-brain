<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstBattlePointLevel extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'level' => 'integer',
        'required_level_up_battle_point' => 'integer',
        'max_battle_point' => 'integer',
        'charge_amount' => 'integer',
        'charge_interval' => 'integer',
    ];
}
