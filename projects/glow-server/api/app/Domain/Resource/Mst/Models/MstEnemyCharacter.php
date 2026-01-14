<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstEnemyCharacterEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstEnemyCharacter extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_series_id' => 'string',
        'asset_key' => 'string',
        'is_phantomized' => 'integer',
        'is_displayed_encyclopedia' => 'integer',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_series_id,
        );
    }
}
