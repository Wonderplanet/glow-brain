<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Models;

use WonderPlanet\Domain\MasterAssetRelease\Entities\MngAssetReleaseEntity;

class MngAssetRelease extends BaseMngModel
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'platform' => 'integer',
        'enabled' => 'boolean',
        'target_release_version_id' => 'string',
        'client_compatibility_version' => 'string',
        'description' => 'string',
        'start_at' => 'datetime:Y-m-d H:i:s',
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
        'client_compatibility_version',
        'description',
        'start_at',
    ];

    /**
     * @return MngAssetReleaseEntity
     */
    public function toEntity(): MngAssetReleaseEntity
    {
        return new MngAssetReleaseEntity(
            $this->id,
            $this->release_key,
            $this->platform,
            $this->enabled,
            $this->target_release_version_id,
            $this->client_compatibility_version,
            $this->description,
            $this->start_at?->toImmutable(),
        );
    }
}
