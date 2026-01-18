<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstEmblemEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class MstEmblem extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'emblem_type' => 'string',
        'mst_series_id' => 'string',
        'asset_key' => 'string',
        'release_key' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'emblem_type',
        'mst_series_id',
        'asset_key',
        'release_key',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->emblem_type,
            $this->mst_series_id,
            $this->asset_key,
        );
    }
}
