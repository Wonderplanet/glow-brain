<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitSpecificRankUpEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnitSpecificRankUp extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_unit_id' => 'string',
        'rank' => 'integer',
        'amount' => 'integer',
        'unit_memory_amount' => 'integer',
        'require_level' => 'integer',
        'sr_memory_fragment_amount' => 'integer',
        'ssr_memory_fragment_amount' => 'integer',
        'ur_memory_fragment_amount' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_unit_id,
            $this->rank,
            $this->amount,
            $this->unit_memory_amount,
            $this->require_level,
            $this->sr_memory_fragment_amount,
            $this->ssr_memory_fragment_amount,
            $this->ur_memory_fragment_amount,
        );
    }
}
