<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\OprAssetReleaseEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;

class OprAssetRelease extends MstModel
{
    use HasFactory;

    public $timestamps = true;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'platform' => 'integer',
        'enabled' => 'boolean',
        'target_release_version_id' => 'string',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'release_key',
        'platform',
        'enabled',
        'target_release_version_id',
    ];

    /**
     * @return Entity
     */
    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->release_key,
            $this->platform,
            $this->enabled,
            $this->target_release_version_id,
        );
    }
}
