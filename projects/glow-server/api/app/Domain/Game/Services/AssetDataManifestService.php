<?php

declare(strict_types=1);

namespace App\Domain\Game\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngAssetReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpAssetReleaseApplyNotFoundException;
use WonderPlanet\Domain\MasterAssetRelease\Facades\AssetReleaseVersion;

readonly class AssetDataManifestService
{
    private const PLATFORM_IOS = "ios";
    private const PLATFORM_ANDROID = "android";

    /**
     * 現在有効なアセットのマニフェスト取得
     *
     * @param int $platform
     * @param string $clientVersion
     * @param CarbonImmutable $now
     *
     * @return array<string, string>
     *
     * @throws GameException
     */
    public function getCurrentActiveManifest(int $platform, string $clientVersion, CarbonImmutable $now): array
    {
        $assetVersion = $this->getCurrentActiveAsset($platform, $clientVersion, $now);
        if (is_null($assetVersion)) {
            throw new GameException(ErrorCode::AVAILABLE_ASSET_VERSION_NOT_FOUND);
        }

        return [
            'catalog_data_path' => $this->createCatalogDataPath($assetVersion),
            'asset_hash' => $assetVersion->getCatalogHash(),
        ];
    }

    /**
     * 現在有効なアセット情報を取得
     *
     * @param int $platform
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return MngAssetReleaseVersionEntity|null
     * @throws GameException
     */
    public function getCurrentActiveAsset(
        int $platform,
        string $clientVersion,
        CarbonImmutable $now,
    ): ?MngAssetReleaseVersionEntity {
        try {
            // 現在有効なアセットバージョン情報取得
            /** @var MngAssetReleaseVersionEntity $assetVersion */
            $assetVersion = AssetReleaseVersion::getCurrentActiveAsset($platform, $clientVersion, $now);

            // phpstanでExceptionが投げられないというエラーが出てしまうため、無視するように設定
            // @phpstan-ignore-next-line
        } catch (WpAssetReleaseApplyNotFoundException $applyNotFoundException) {
            throw new GameException(
                ErrorCode::NOT_FOUND_APPLY_ASSET_RELEASE,
                $applyNotFoundException->getMessage()
            );
        }

        return $assetVersion;
    }

    /**
     * プラットフォーム名取得
     *
     * @param int $platform
     *
     * @return string
     */
    private function getPlatformName(int $platform): string
    {
        return match ($platform) {
            PlatformConstant::PLATFORM_IOS => self::PLATFORM_IOS,
            PlatformConstant::PLATFORM_ANDROID => self::PLATFORM_ANDROID,
            // デフォルトはIOSを返す
            default => self::PLATFORM_IOS
        };
    }

    /**
     * catalog_dataのパス生成
     *
     * @param MngAssetReleaseVersionEntity $assetVersion
     *
     * @return string
     */
    private function createCatalogDataPath(MngAssetReleaseVersionEntity $assetVersion): string
    {
        return sprintf(
            'assetbundles/%s/%s/%s',
            $this->getPlatformName($assetVersion->getPlatform()),
            $assetVersion->getCatalogHash(),
            $assetVersion->getCatalogFileName()
        );
    }
}
