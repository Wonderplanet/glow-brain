<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Mng;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Database\Factories\MngMasterReleaseVersionFactory as BaseMngMasterReleaseVersionFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngMasterReleaseVersion>
 */
class MngMasterReleaseVersionFactory extends BaseMngMasterReleaseVersionFactory
{
    protected $model = MngMasterReleaseVersion::class;
}
