<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Constants;

/**
 * 通貨基盤で使用する定数を定義するクラス
 */
class CurrencyConstants
{
    // OSプラットフォーム定義
    /**
     * iOSで操作した場合のOSプラットフォーム
     */
    public const OS_PLATFORM_IOS = 'iOS';
    /**
     * Androidで操作した場合のOSプラットフォーム
     */
    public const OS_PLATFORM_ANDROID = 'Android';
    /**
     * 管理ツールから操作した場合のOSプラットフォーム
     */
    public const OS_PLATFORM_ADMINTOOL = 'AdminTool';

    /**
     * バッチ操作した場合のOSプラットフォーム
     */
    public const OS_PLATFORM_BATCH = 'Batch';

    /**
     * WebStore(Xsolla)から操作した場合のOSプラットフォーム
     */
    public const OS_PLATFORM_WEBSTORE = 'WebStore';

    // プラットフォーム定義
    public const PLATFORM_APPSTORE = 'AppStore';
    public const PLATFORM_GOOGLEPLAY = 'GooglePlay';
    public const PLATFORM_WEBSTORE = 'WebStore';

    // 無償一次通貨の種類
    //  FreeCurrencyType.phpにEnumとして定義されているものと同じ
    /**
     * ゲーム内・配布などで取得した無償一次通貨
     */
    public const FREE_CURRENCY_TYPE_INGAME = 'ingame';

    /**
     * ショップ販売の追加付与で取得した無償一次通貨
     * 変動単価性の場合は購入時に基盤側で自動的に付与はされない
     *
     * ※追加された場合も、通常の無償一次通貨として扱われる。
     * 　有償一次通貨として扱われる無償一次通貨ではない
     * 　いわゆる「おまけ」として配布される無償一次通貨ではない
     */
    public const FREE_CURRENCY_TYPE_BONUS = 'bonus';

    /**
     * 広告視聴などで発生する報酬から取得した無償一次通貨
     */
    public const FREE_CURRENCY_TYPE_REWARD = 'reward';

    /**
     * エクセル出力用フォーマット
     * 整数を1000桁手間でカンマ区切りにする
     */
    public const FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL = '#,##0';

    /**
     * DBから取得する際のDBのタイムゾーン指定
     */
    public const DATABASE_TZ = 'UTC';

    /**
     * DBから取得する際の出力時のタイムゾーン指定
     */
    public const OUTPUT_TZ = 'Asia/Tokyo';
}
