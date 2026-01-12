<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng;

use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetRelease as BaseMngAssetRelease;

class MngAssetRelease extends BaseMngAssetRelease
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->connection = DBUtility::getMngConnName();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mngAssetReleaseVersion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(
            MngAssetReleaseVersion::class,
            'id',
            'target_release_version_id'
        );
    }
}
