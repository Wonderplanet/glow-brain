<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstUnitRankUpEntity;
use App\Domain\Resource\Traits\HasFactory;

class MstUnitRankUp extends MstModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'unit_label' => 'string',
        'rank' => 'integer',
        'amount' => 'integer',
        'require_level' => 'integer',
        'sr_memory_fragment_amount' => 'integer',
        'ssr_memory_fragment_amount' => 'integer',
        'ur_memory_fragment_amount' => 'integer',
    ];

    public function toEntity(): MstUnitRankUpEntity
    {
        return new MstUnitRankUpEntity(
            $this->id,
            $this->unit_label,
            $this->rank,
            $this->amount,
            $this->require_level,
            $this->sr_memory_fragment_amount,
            $this->ssr_memory_fragment_amount,
            $this->ur_memory_fragment_amount,
        );
    }
}
