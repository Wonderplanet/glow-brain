<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Facades;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Common\Facades\BaseFacade;

/**
 * マスターリリースバージョン機構のファサード
 *
 * MasterReleaseDelegatorのメソッドを呼び出す
 *
 * @method static mixed getApplyMasterReleaseVersionEntityByClientVersion($clientVersion, CarbonImmutable $now)
 * @method static mixed getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection(CarbonImmutable $now)
 *
 * @see \WonderPlanet\Domain\MasterAssetRelease\Delegators\MasterReleaseDelegator
 */
class MasterReleaseVersion extends BaseFacade
{
    public const FACADE_ACCESSOR = 'laravel-wp-master-asset-release-master-release-version';
}
