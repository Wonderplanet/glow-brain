<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstTutorialTipI18n extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_tutorial_id' => 'string',
        'language' => 'string',
        'sort_order' => 'integer',
        'title' => 'string',
        'asset_key' => 'string',
    ];
}
