<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstOutpostEnhancementEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstOutpostEnhancement extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'mst_outpost_id' => 'string',
        'outpost_enhancement_type' => 'string',
        'asset_key' => 'string',
        'release_key' => 'integer',
    ];

    protected $fillable = [
        'id',
        'mst_outpost_id',
        'outpost_enhancement_type',
        'asset_key',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_outpost_id,
            $this->outpost_enhancement_type,
            $this->asset_key,
            $this->release_key,
        );
    }
}
