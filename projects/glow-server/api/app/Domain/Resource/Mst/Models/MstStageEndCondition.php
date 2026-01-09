<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Traits\HasFactory;

class MstStageEndCondition extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_stage_id' => 'string',
        'stage_end_type' => 'string',
        'condition_type' => 'string',
        'condition_value1' => 'string',
        'condition_value2' => 'string',
    ];
}
