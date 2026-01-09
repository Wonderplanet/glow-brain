<?php

declare(strict_types=1);

namespace App\Utils;

use App\Constants\AssetConstant;
use App\Constants\RarityType;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\MasterAssetReleaseConstants;

class AssetUtil
{
    public static function makeClientAssetBundlePath(string $path): string
    {
        return StringUtil::joinPath(
            asset(AssetConstant::WEB_ASSET_BUNDLES_PATH),
            $path,
        );
    }

    public static function makeClientBgmPath(string $path): string
    {
        return StringUtil::joinPath(
            asset(AssetConstant::WEB_AUDIOS_BGM_PATH),
            $path,
        );
    }

    public static function makeBgItemIconFramePathByRarity(?string $rarity = null): string
    {
        if ($rarity === null) {
            $rarity = RarityType::SR->value;
        }

        $rarity = strtolower($rarity);

        return StringUtil::joinPath(
            asset(AssetConstant::WEB_GRAPHICS_UI_COMPONENTS_PATH),
            sprintf('BgItemIconFrame_%s.png', $rarity),
        );
    }

    public static function makeDiamondIconPath(): string
    {
        return self::makeClientAssetBundlePath(
            'player_resource_icon/player_resource_icon_diamond.png',
        );
    }

    public static function makeDiamondBgPath(): string
    {
        return self::makeBgItemIconFramePathByRarity(RarityType::UR->value);
    }

    public static function makeCoinIconPath(): string
    {
        return self::makeClientAssetBundlePath(
            'player_resource_icon/player_resource_icon_coin.png',
        );
    }

    public static function makeCoinBgPath(): string
    {
        return self::makeBgItemIconFramePathByRarity(RarityType::R->value);
    }

    public static function makeExpIconPath(): string
    {
        return self::makeClientAssetBundlePath(
            'player_resource_icon/player_resource_icon_user_exp.png',
        );
    }

    public static function makeExpBgPath(): string
    {
        return self::makeBgItemIconFramePathByRarity(RarityType::R->value);
    }

    public static function makeS3BannerUrl(string $key): string
    {
        // TODO: utilがconfiggetServiceに依存するわけはいかないのでどうしよう...
        return StringUtil::joinPath(
            config('filesystems.disks.s3_banner.url', ''),
            $key,
        );
    }

    /**
     * S3バナーURLにキャッシュバスティング用のクエリパラメータを付与して返す
     *
     * @param string $key S3オブジェクトのキー
     * @param string|null $version キャッシュバスティング用の値（etag、timestamp等）
     * @return string キャッシュバスティング付きのURL
     */
    public static function makeS3BannerUrlWithCacheBusting(string $key, ?string $version = null): string
    {
        $url = self::makeS3BannerUrl($key);

        if (StringUtil::isSpecified($version)) {
            // ダブルクォートがあれば除去（etagの場合）
            $version = trim($version, '"');
            $url .= '?v=' . $version;
        }

        return $url;
    }

    /**
     * 複数のパス候補から存在するファイルを検索してパスを返す
     *
     * @param array<string> $pathCandidates パス候補の配列（優先順位順）
     * @return string|null 最初に見つかったファイルのWeb表示用パス、見つからない場合はnull
     */
    public static function findExistingAssetPath(array $pathCandidates): ?string
    {
        $basePath = storage_path(AssetConstant::STORAGE_ASSET_BUNDLES_PATH);

        foreach ($pathCandidates as $candidate) {
            $fullPath = StringUtil::joinPath($basePath, $candidate);

            if (file_exists($fullPath)) {
                // 見つかったらWeb表示用のURLを返す
                return self::makeClientAssetBundlePath($candidate);
            }
        }

        // 見つからなかった場合はnullを返す
        return null;
    }

    /**
     * パステンプレートからrelease_key対応のパス候補を自動生成して検索
     *
     * モデル側は単にパステンプレートを渡すだけで、release_keyの展開は自動的に行われる
     * テンプレート内の{release_key}プレースホルダーが実際のrelease_key値に置き換えられる
     *
     * @param array<string> $pathTemplates パステンプレート配列（例: ['artwork_a/artwork_a!{release_key}/artwork_{asset_key}a.png']）
     * @param string $assetKey アセットキー
     * @param int|string|null $releaseKey リリースキー
     * @return string|null 見つかったファイルのWeb表示用パス
     */
    public static function findAssetPathFromTemplates(
        array $pathTemplates,
        string $assetKey,
        int|string|null $releaseKey = null
    ): ?string {
        $candidates = [];

        $releaseKey = (string)$releaseKey;

        foreach ($pathTemplates as $template) {
            // {asset_key}プレースホルダーを実際の値に置換
            $path = str_replace('{asset_key}', $assetKey, $template);

            // {release_key}が含まれる場合、release_key版とMAX_RELEASE_KEY版、標準版を生成
            if (str_contains($path, '{release_key}')) {
                // release_keyが有効な場合、それを使ったパスを最優先
                if (StringUtil::isSpecified($releaseKey)) {
                    $candidates[] = str_replace('{release_key}', $releaseKey, $path);
                }

                // MAX_RELEASE_KEYでのフォールバック
                $candidates[] = str_replace('{release_key}', (string)MasterAssetReleaseConstants::MAX_RELEASE_KEY, $path);

                // {release_key}を削除した標準パス（!も削除）
                $standardPath = str_replace('!{release_key}', '', $path);
                $candidates[] = $standardPath;
            } else {
                // {release_key}がない場合はそのまま追加
                $candidates[] = $path;
            }
        }

        // テンプレートから生成した候補パスをチェック
        return self::findExistingAssetPath($candidates);
    }
}
