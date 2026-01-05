<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Mng;

use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetRelease\Database\Factories\MngAssetReleaseFactory as BaseMngAssetReleaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngAssetRelease>
 */
class MngAssetReleaseFactory extends BaseMngAssetReleaseFactory
{
    protected $model = MngAssetRelease::class;
}
