<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstUnitRoleBonus extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'role_type' => 'string',
        'color_advantage_attack_bonus' => 'float',
        'color_advantage_defense_bonus' => 'float',
    ];
}
