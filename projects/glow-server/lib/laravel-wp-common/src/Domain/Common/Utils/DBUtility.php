<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Utils;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

/**
 * 共通基盤のDBユーティリティクラス
 *
 * 設定の読み込みなどをまとめる
 */
class DBUtility
{
    /**
     * database名の変更が入らないmst DBの接続名を取得する
     *
     * @return string
     */
    public static function getDefaultMstConnName(): string
    {
        return Config::get('wp_common.connections.default_mst');
    }

    /**
     * mst DBの接続名を取得する
     *
     * @return string
     */
    public static function getMstConnName(): string
    {
        return Config::get('wp_common.connections.mst');
    }

    /**
     * mng DBの接続名を取得する
     *
     * @return string
     */
    public static function getMngConnName(): string
    {
        // // TODO mng系テーブルは管理ツールからデータが投入されるので、usrとは異なるコネクションを用意した方がよい
        // //  mng用のコネクション対応を別途対応するまでは、usrと同じコネクションを使用
        // return self::getUsrConnName();

        return Config::get('wp_common.connections.mng');
    }

    /**
     * usr DBの接続名を取得する
     *
     * @return string
     */
    public static function getUsrConnName(): string
    {
        return Config::get('wp_common.connections.usr');
    }

    /**
     * log DBの接続名を取得する
     *
     * @return string
     */
    public static function getLogConnName(): string
    {
        return Config::get('wp_common.connections.log');
    }

    /**
     * admin DBの接続名を取得する
     *
     * @return string
     */
    public static function getAdminConnName(): string
    {
        return Config::get('wp_common.connections.admin');
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

    /**
     * configからDBのテーブル名を取得する
     *
     * @param string $tableName
     * @return string
     */
    public static function getTableName(string $tableName): string
    {
        $configTablenames = Config::get('wp_common.tablenames');
        return $configTablenames[$tableName] ?? '';
    }

    /**
     * configからデータソースタイムゾーンを取得する
     *
     * @return string
     */
    public static function getDatasourceTimezone(): string
    {
        return Config::get('wp_common.datasource_timezone');
    }
}
