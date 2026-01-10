<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng;

use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion as BaseMngAssetReleaseVersion;

class MngAssetReleaseVersion extends BaseMngAssetReleaseVersion
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = DBUtility::getMngConnName();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mngAssetRelease(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MngAssetRelease::class);
    }
}
