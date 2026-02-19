<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng;

use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease as BaseMngMasterRelease;

class MngMasterRelease extends BaseMngMasterRelease
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->connection = DBUtility::getMngConnName();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mngMasterReleaseVersion(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(
            MngMasterReleaseVersion::class,
            'id',
            'target_release_version_id'
        );
    }
}
