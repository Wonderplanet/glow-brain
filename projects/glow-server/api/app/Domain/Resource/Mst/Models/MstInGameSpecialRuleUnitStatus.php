<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstInGameSpecialRuleUnitStatus extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'group_id' => 'string',
        'target_type' => 'string',
        'target_value' => 'string',
        'status_parameter_type' => 'string',
        'effect_value' => 'integer',
    ];
}
