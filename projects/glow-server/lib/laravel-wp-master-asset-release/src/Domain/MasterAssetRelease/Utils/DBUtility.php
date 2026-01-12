<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Utils;

use Illuminate\Support\Facades\Config;

/**
 * マスターアセット管理基盤のDBユーティリティクラス
 *
 * 設定の読み込みなどをまとめる
 */
class DBUtility
{
    /**
     * configからDBのテーブル名を取得する
     *
     * @param string $tableName
     * @return string
     */
    public static function getTableName(string $tableName): string
    {
        $configTablenames = Config::get('wp_master_asset_release.tablenames');
        return $configTablenames[$tableName] ?? '';
    }
}
