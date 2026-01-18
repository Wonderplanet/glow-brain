<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstEventDisplayReward extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_event_id' => 'string',
        'resource_type' => 'string',
        'resource_id' => 'string',
        'sort_order' => 'integer',
    ];
}
