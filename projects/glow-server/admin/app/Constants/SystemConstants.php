<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Common\Constants\System as ApiSystem;

/**
 * システム共通の定数を定義する
 *
 * 大まかな分類ができない共通のものはここに定義する
 */
class SystemConstants
{
    /**
     * Adminアプリケーションのバージョン
     */
    public const ADMIN_VERSION = 'v1.5.1';

    /**
     * APIアプリケーションのバージョン
     */
    public const API_VERSION = ApiSystem::API_VERSION;

    /**
     * フォームなどから受け取る日時文字列を解釈するタイムゾーン
     *
     * たとえば、フォームのStartDateTimeフィールドに「2021-01-01 00:00:00」と入力された場合、
     * これはAsia/Tokyo(JST)の2021年1月1日0時0分0秒を表す。
     * そのためCarbonオブジェクトを作る際には、
     * new Carbon('2021-01-01 00:00:00', 'Asia/Tokyo') とする必要がある。
     *
     * この定数は、そのインプット時の文字列がどのタイムゾーンであるかを表す。
     *
     * @var string
     */
    public const FORM_INPUT_TIMEZONE = 'Asia/Tokyo';

    /**
     * 画面表示に使用するタイムゾーン
     */
    public const VIEW_TIMEZONE = 'Asia/Tokyo';

    public const VIEW_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * DB検索に使用するタイムゾーン
     *
     * DBではUTCで管理しているため、フォームから受け取った日時文字列をUTCに変換して検索する必要がある。
     */
    public const DB_TIMEZONE = 'UTC';

    public const DATETIME_ISO_FORMAT = 'Y-m-d\TH:i:s';

    public const TIMEZONE_UTC = 'UTC';

    /**
     * メンテナンス設定を保存するDynamoDBテーブルのPK
     */
    public const MAINTENANCE_DYNAMODB_TABLE_PK = '#maintenance';

    /**
     * 集計処理実行などで指定するmemory_limit
     */
    public const MAX_MEMORY_LIMIT = '4096M';

    /**
     * 集計処理実行などで指定するタイムアウト設定時間(秒)
     *  nginxのタイムアウト設定(fastcgi_read_timeout)も同じ値で変更が必要
     */
    public const MAX_TIME_LIMIT = 1800;
}
