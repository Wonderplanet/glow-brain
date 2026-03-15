<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums;

/**
 * アセットおよびマスタの配信ステータス
 */
enum ReleaseStatus: int
{
    /**
     * 配信中
     */
    case RELEASE_STATUS_APPLYING = 1;

    /**
     * 準備中
     */
    case RELEASE_STATUS_PENDING = 2;

    /**
     * 終了
     */
    case RELEASE_STATUS_END = 3;
}
