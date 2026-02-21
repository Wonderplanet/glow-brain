<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstHomeKomaPattern extends MstModel
{
    use HasFactory;

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'asset_key' => 'string',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'asset_key',
    ];
}
