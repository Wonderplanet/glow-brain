<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants;

use WonderPlanet\Domain\Common\Constants\PlatformConstant;

/**
 * マスター配信管理、アセット配信管理で使用する定数を定義
 */
class MasterAssetReleaseConstants
{
    /**
     * 全プラットフォーム指定
     */
    public const PLATFORM_ALL = 9999;

    /**
     * 管理ツール表示用
     */
    public const PLATFORM_STRING_LIST = [
        self::PLATFORM_ALL => '全プラットフォーム',
        PlatformConstant::PLATFORM_IOS => 'iOSのみ',
        PlatformConstant::PLATFORM_ANDROID => 'Androidのみ',
    ];

    /**
     * release_keyの最大値
     */
    public const MAX_RELEASE_KEY = 999999999;
}
