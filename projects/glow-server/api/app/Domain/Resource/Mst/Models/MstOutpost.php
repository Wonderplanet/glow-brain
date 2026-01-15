<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstOutpostEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $id
 * @property int $release_key
 * @property string $asset_key
 * @property string $start_at
 * @property string $end_at
 */
class MstOutpost extends MstModel
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'asset_key' => 'string',
        'start_at' => 'string',
        'end_at' => 'string',
    ];

    protected $fillable = [
        'id',
        'release_key',
        'asset_key',
        'start_at',
        'end_at',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->asset_key,
            $this->start_at,
            $this->end_at,
            $this->release_key,
        );
    }
}
