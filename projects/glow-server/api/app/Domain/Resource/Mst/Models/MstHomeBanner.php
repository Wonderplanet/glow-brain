<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstHomeBanner extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'destination' => 'string',
        'destination_path' => 'string',
        'asset_key' => 'string',
        'sort_order' => 'integer',
        'start_at' => 'string',
        'end_at' => 'string',
    ];
}
