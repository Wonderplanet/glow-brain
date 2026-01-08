<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums;

/**
 * アセットのファイル差分タイプ
 */
enum AssetDiffType: string
{
    /**
     * 新規追加
     */
    case DIFF_TYPE_ADD = 'add';

    /**
     * 削除
     */
    case DIFF_TYPE_DELETE = 'delete';

    /**
     * 変更
     */
    case DIFF_TYPE_CHANGE = 'change';
}
