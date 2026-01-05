<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class OprGachaDisplayUnitI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'opr_gacha_id' => 'string',
        'mst_unit_id' => 'string',
        'language' => 'string',
        'sort_order' => 'integer',
        'description' => 'string',
    ];
}
