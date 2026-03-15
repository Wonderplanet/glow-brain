<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

/**
 * 課金・通貨基盤のユーティリティクラス
 *
 * 設定の読み込みなどをまとめる
 */
class DBUtility
{
    /**
     * mst DBの接続名を取得する
     *
     * @return string
     */
    public static function getMstConnName(): string
    {
        return Config::get('wp_currency.connections.mst');
    }

    /**
     * usr DBの接続名を取得する
     *
     * @return string
     */
    public static function getUsrConnName(): string
    {
        return Config::get('wp_currency.connections.usr');
    }

    /**
     * admin DBの接続名を取得する
     *
     * @return string
     */
    public static function getAdminConnName(): string
    {
        return Config::get('wp_currency.connections.admin');
    }

    /**
     * configからDBのテーブル名を取得する
     *
     * @param string $tablename
     * @return string
     */
    public static function getTableName(string $tablename): string
    {
        $configTablenames = Config::get('wp_currency.tablenames');
        return $configTablenames[$tablename] ?? '';
    }

    /**
     * キー用のUUIDを生成する
     *
     * laravelではuuidを生成するためにStr::uuid()を使用できるが、
     * TiDBのホットスポット問題が懸念されるため、完全にランダムなUUID生成方法を使用している
     *
     * 参考: https://docs.pingcap.com/ja/tidb/v6.1/high-concurrency-best-practices#hotspot-causes
     * @return string
     */
    public static function generateUUID(): string
    {
        // uuidはbb470186-640f-4e2c-a828-3f5a39ff9c6dのような形式で生成される
        return UUid::uuid4()->toString();
    }
}
