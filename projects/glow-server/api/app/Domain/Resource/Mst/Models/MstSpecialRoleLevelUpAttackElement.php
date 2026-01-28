<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstSpecialRoleLevelUpAttackElement extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_attack_element_id' => 'string',
        'min_effective_count' => 'integer',
        'max_effective_count' => 'integer',
        'min_effective_duration' => 'integer',
        'max_effective_duration' => 'integer',
        'min_effect_parameter' => 'float',
        'max_effect_parameter' => 'float',
    ];
}
