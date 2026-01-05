<?php

declare(strict_types=1);

namespace App\Domain\Common\Constants;

class System
{
    /** @var string アクセストークンヘッダー */
    public const HEADER_ACCESS_TOKEN = "Access-Token";

    /** @var string Androidプラットフォーム */
    public const PLATFORM_ANDROID = 'android';
    /** @var string iOSプラットフォーム */
    public const PLATFORM_IOS = 'ios';

    public const HEADER_PLATFORM = "Platform";

    public const HEADER_LANGUAGE = "Language";
    public const CLIENT_VERSION = "Client-Version";

    public const HEADER_ASSET_VERSION = "Asset-Version";

    public const HEADER_ASSET_HASH = "Asset-Hash";

    public const HEADER_MASTER_HASH = "Mst-Hash";

    public const HEADER_MASTER_I18N_HASH = "Mst-I18n-Hash";

    public const HEADER_OPERATION_HASH = "Opr-Hash";

    public const HEADER_OPERATION_I18N_HASH = "Opr-I18n-Hash";

    public const HEADER_USER_AGENT = "User-Agent";

    /**
     * 課金用プラットフォームヘッダー
     */
    public const HEADER_BILLING_PLATFORM = 'Billing-Platform';

    // デバッグ用ヘッダー
    /**
     * ヘッダに指定されたエラーコードを発生させる
     */
    public const HEADER_ERROR_CODE = 'Error-Code';

    /**
     * HEADER_ERROR_CODEが指定された場合に、このヘッダに設定されているエラーメッセージを返す
     */
    public const HEADER_ERROR_MESSAGE = 'Error-Message';

    /**
     * 日付跨ぎチェックしないAPI
     *
     * 配列処理高速化のため、issetで判定できるように、キーにAPIパス、値に1(特に意味はない)を設定
     */
    public const CROSS_DAY_CHECK_THROUGH_API = [
        'api/game/server_time' => 1,
        'api/game/version' => 1,
        'api/game/update_and_fetch' => 1,
        'api/user/info' => 1,
        'api/stage/end' => 1,
        'api/tutorial/stage_end' => 1,
        'api/advent_battle/end' => 1,
        'api/pvp/end' => 1,
        'api/party/save' => 1,
        'api/shop/trade_pack' => 1,
        'api/shop/purchase_pass' => 1,
        'api/shop/purchase' => 1,
        'api/game/fetch' => 1,
    ];

    /**
     * マスターデータチェックしないAPI
     *
     * 配列処理高速化のため、issetで判定できるように、キーにAPIパス、値に1(特に意味はない)を設定
     */
    public const MASTER_CHECK_THROUGH_API = [
        'api/shop/set_store_info' => 1,
        'api/stage/end' => 1,
        'api/tutorial/stage_end' => 1,
        'api/advent_battle/end' => 1,
        'api/pvp/end' => 1,
    ];

    /**
     * アセットデータチェックしないAPI
     *
     * 配列処理高速化のため、issetで判定できるように、キーにAPIパス、値に1(特に意味はない)を設定
     */
    public const ASSET_CHECK_THROUGH_API = [
        'api/shop/set_store_info' => 1,
    ];

    /**
     * BANユーザーチェックしないAPI（UserStatusCheckミドルウェアの処理対象外API）
     *
     * 配列処理高速化のため、issetで判定できるように、キーにAPIパス、値に1(特に意味はない)を設定
     */
    public const USER_STATUS_CHECK_THROUGH_API = [
        'api/user/info' => 1,
    ];
}
