<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Models;

use WonderPlanet\Domain\MasterAssetRelease\Entities\MngAssetReleaseVersionEntity;

class MngAssetReleaseVersion extends BaseMngModel
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'git_revision' => 'string',
        'git_branch' => 'string',
        'catalog_hash' => 'string',
        'platform' => 'integer',
        'build_client_version' => 'string',
        'asset_total_byte_size' => 'integer',
        'catalog_byte_size' => 'integer',
        'catalog_file_name' => 'string',
        'catalog_hash_file_name',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'release_key',
        'git_revision',
        'git_branch',
        'catalog_hash',
        'platform',
        'build_client_version',
        'asset_total_byte_size',
        'catalog_byte_size',
        'catalog_file_name',
        'catalog_hash_file_name',
    ];

    /**
     * @return MngAssetReleaseVersionEntity
     */
    public function toEntity(): MngAssetReleaseVersionEntity
    {
        return new MngAssetReleaseVersionEntity(
            $this->id,
            $this->release_key,
            $this->git_revision,
            $this->git_branch,
            $this->catalog_hash,
            $this->platform,
            $this->build_client_version,
            $this->asset_total_byte_size,
            $this->catalog_byte_size,
            $this->catalog_file_name,
            $this->catalog_hash_file_name,
        );
    }
}
