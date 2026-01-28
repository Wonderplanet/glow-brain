<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstUnitAbility extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_ability_id' => 'string',
        'ability_parameter1' => 'string',
        'ability_parameter2' => 'string',
        'ability_parameter3' => 'string',
    ];
}
