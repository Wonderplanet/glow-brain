<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Facades;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Common\Facades\BaseFacade;

/**
 * アセットリリースバージョン機構のファサード
 *
 * AssetReleaseDelegatorのメソッドを呼び出す
 *
 * @method static mixed getCurrentActiveAsset(int $platform, string $clientVersion, CarbonImmutable $now)
 *
 * @see \WonderPlanet\Domain\MasterAssetRelease\Delegators\AssetReleaseDelegator
 */
class AssetReleaseVersion extends BaseFacade
{
    public const FACADE_ACCESSOR = 'laravel-wp-master-asset-api-asset-release-version';
}
