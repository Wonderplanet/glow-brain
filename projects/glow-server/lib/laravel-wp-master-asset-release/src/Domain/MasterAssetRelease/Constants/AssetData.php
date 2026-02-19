<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Constants;

/**
 * アセットデータインポートなどで使用する値を定義している
 */
class AssetData
{
    /**
     * 配信として扱うリリース情報の件数
     * 下記条件に当てはまるデータを配信中ステータスとみなしており、最下部の条件に使用します
     *  ・enabled=1である
     *  ・target_release_version_idにnull以外が設定されている
     *  ・最新のrelease_key2つまで
     */
    public const ASSET_RELEASE_APPLY_LIMIT = 2;
}
