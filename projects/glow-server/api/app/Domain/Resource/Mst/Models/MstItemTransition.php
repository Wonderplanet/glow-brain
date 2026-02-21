<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstItemTransition extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mst_item_transitions';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_item_id' => 'string',
        'transition1' => 'string',
        'transition1_mst_id' => 'string',
        'transition2' => 'string',
        'transition2_mst_id' => 'string',
    ];
}
