<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstHomeKomaPatternI18n extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'mst_home_koma_pattern_id' => 'string',
        'release_key' => 'integer',
        'language' => 'string',
        'name' => 'string',
    ];

    protected $fillable = [
        'id',
        'mst_home_koma_pattern_id',
        'release_key',
        'language',
        'name',
    ];
}
