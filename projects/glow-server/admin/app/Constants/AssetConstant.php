<?php

declare(strict_types=1);

namespace App\Constants;

class AssetConstant
{
    // Git関連
    public const GIT_ORIGIN = 'origin';
    public const ASSET_IMPORT_MAX_EXECUTION_TIME = 900; // 15分

    // sparse-checkout対象パス
    public const ASSET_PATHS = [
        self::PATH_ASSET_BUNDLES,
        self::PATH_AUDIOS_BGM,
        self::PATH_GRAPHICS_UI_COMPONENTS,
    ];

    // アセットディレクトリパス（個別）
    public const PATH_ASSET_BUNDLES = 'Assets/GLOW/AssetBundles';
    public const PATH_AUDIOS_BGM = 'Assets/GLOW/Audios/Data/BGM';
    public const PATH_GRAPHICS_UI_COMPONENTS = 'Assets/GLOW/Graphics/UI/Components';

    // ストレージパス（storage_path()と組み合わせて使用）
    public const STORAGE_ASSET_BUNDLES_PATH = 'app/glow_client/Assets/GLOW/AssetBundles';

    // Web表示用アセットパス
    public const WEB_ASSET_BUNDLES_PATH = 'asset/glow_client_asset/AssetBundles';
    public const WEB_AUDIOS_BGM_PATH = 'asset/glow_client_asset/Audios/Data/BGM';
    public const WEB_GRAPHICS_UI_COMPONENTS_PATH = 'asset/glow_client_asset/Graphics/UI/Components';

    // ブランチフィルタリング
    public const RELEASE_BRANCH_PREFIX = 'release';

}
