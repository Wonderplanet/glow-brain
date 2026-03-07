<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstOutpostEnhancementLevelEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstOutpostEnhancementLevel extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_outpost_enhancement_id' => 'string',
        'level' => 'integer',
        'cost_coin' => 'integer',
        'enhancement_value' => 'float',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_outpost_enhancement_id',
        'level',
        'cost_coin',
        'enhancement_value',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_outpost_enhancement_id,
            $this->level,
            $this->cost_coin,
            $this->enhancement_value,
            $this->release_key,
        );
    }
}
