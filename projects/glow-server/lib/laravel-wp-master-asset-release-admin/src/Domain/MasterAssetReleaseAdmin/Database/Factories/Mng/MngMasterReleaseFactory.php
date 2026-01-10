<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Database\Factories\Mng;

use WonderPlanet\Domain\MasterAssetRelease\Database\Factories\MngMasterReleaseFactory as BaseMngMasterReleaseFactory;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MngMasterRelease>
 */
class MngMasterReleaseFactory extends BaseMngMasterReleaseFactory
{
    protected $model = MngMasterRelease::class;
}
