<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstPartyUnitCountEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstPartyUnitCount extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'id' => 'string',
        'mst_stage_id' => 'string',
        'max_count' => 'integer',
        'release_key' => 'integer',
    ];

    protected $guarded = [];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_stage_id,
            $this->max_count,
        );
    }
}
