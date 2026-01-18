<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Mng;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Database\Factories\MngAssetReleaseVersionFactory as BaseMngAssetReleaseVersionFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngAssetReleaseVersion>
 */
class MngAssetReleaseVersionFactory extends BaseMngAssetReleaseVersionFactory
{
    protected $model = MngAssetReleaseVersion::class;
}
