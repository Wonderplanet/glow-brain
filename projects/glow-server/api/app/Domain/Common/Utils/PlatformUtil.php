<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

use App\Domain\Common\Constants\System;
use App\Domain\Currency\Utils\CurrencyUtility;
use App\Domain\User\Constants\UserConstant;

/**
 * プラットフォームのユーティリティクラス
 *
 * 現在、プラットフォーム関連はいくつか定義と役割が分かれていて複雑になっているので、
 * 関連する処理などは一旦このクラスにまとめたい。
 *
 * プラットフォーム定義の種類と使われ方は次のものがある
 *
 * ## クライアントヘッダから送られてくるプラットフォーム番号
 *   headderから取得する数値になる。
 *   $platform = (int)$request->header(System::HEADER_PLATFORM); で取得される。
 *   定義はUserConstants::PLATFORM_XXXを参照
 *     iOS -> 1
 *     Android -> 2
 *
 * ## プラットフォーム名
 *   プラットフォーム名は文字列で定義されている。
 *   UserConstants::PLATFORM_STRING_LIST を参照
 *     iOS -> 'iOS'
 *     Android -> 'Android'
 *
 * ## 課金基盤メソッド向けのプラットフォーム名
 *   0.4.0の経緯により、課金基盤に渡すためのプラットフォーム名が存在する
 *   System::PLATFORM_XXX を参照
 *     iOS -> 'ios'
 *     Android -> 'android'
 *
 * ## 課金基盤ライブラリ内でのプラットフォーム名
 *   0.4.0の経緯により、課金基盤ライブラリ内で定義されているOSプラットフォームおよび課金プラットフォームが存在する
 *   これは課金基盤ライブラリ外では基本的に参照されない
 *   CurrencyConstants::OS_PLATFORM_XXX を参照
 *     iOS -> 'iOS'
 *     Android -> 'Android'
 *     管理ツール -> 'AdminTool'
 *
 *   ストアプラットフォーム名
 *   CurrencyConstants::PLATFORM_XXX を参照
 *     AppStore(iOS) -> 'AppStore'
 *     GooglePlay(Android) -> 'GooglePlay'
 */
class PlatformUtil
{
    /**
     * プラットフォーム番号から課金基盤ライブラリに渡すプラットフォーム名を取得する
     * 購入時(purchased)などのメソッドに渡すプラットフォーム名を取得する
     *
     * メソッドによってはconvertPlatformToCurrencyOsPlatformを使用する必要がある。
     * 違いはSystem::PLATFORM_XXX と CurrencyConstants::OS_PLATFORM_XXX のどちらを渡す必要があるか
     *
     * @param integer $headerPlatform ヘッダに設定されたプラットフォーム番号
     * @return string
     */
    public static function convertPlatformToCurrencyPlatform(int $headerPlatform): string
    {
        switch ($headerPlatform) {
            case UserConstant::PLATFORM_IOS:
                return System::PLATFORM_IOS;
            case UserConstant::PLATFORM_ANDROID:
                return System::PLATFORM_ANDROID;
            default:
                // UserConstantに定義されていない場合はそのまま返す
                return (string) $headerPlatform;
        }
    }

    /**
     * 課金基盤ライブラリに渡すプラットフォーム名からプラットフォーム番号を取得する
     *
     * @param string $currencyPlatform ヘッダに設定されたプラットフォーム番号
     * @return int
     */
    public static function convertCurrencyPlatformToPlatform(string $currencyPlatform): int
    {
        switch ($currencyPlatform) {
            case System::PLATFORM_IOS:
                return UserConstant::PLATFORM_IOS;
            case System::PLATFORM_ANDROID:
                return UserConstant::PLATFORM_ANDROID;
            default:
                throw new \Exception('invalid platform: ' . $currencyPlatform);
        }
    }

    /**
     * プラットフォーム番号から課金基盤ライブラリ内で認識しているOSプラットフォーム名を取得する
     *
     * System::PLATFORM_XXX ではなく、CurrencuConstants::OS_PLATFORM_XXX を返す
     *
     * @param integer $headerPlatform
     * @return string
     */
    public static function convertPlatformToCurrencyOsPlatform(int $headerPlatform): string
    {
        $systemPlatform = self::convertPlatformToCurrencyPlatform($headerPlatform);
        return CurrencyUtility::getOsPlatform($systemPlatform);
    }

    /**
     * プラットフォーム番号から課金基盤ライブラリの課金プラットフォーム名を取得する
     *
     * @param integer $headerPlatform
     * @return string
     */
    public static function convertPlatformToBillingPlatform(int $headerPlatform): string
    {
        $systemPlatform = self::convertPlatformToCurrencyPlatform($headerPlatform);
        return CurrencyUtility::getBillingPlatform($systemPlatform);
    }

    /**
     * リクエストヘッダの情報から課金ライブラリに渡すOSプラットフォーム名を取得する
     *
     * @return string
     */
    public static function getCurrencyOsPlatformFromRequest(): string
    {
        $systemPlatform = request()->getPlatform();
        return CurrencyUtility::getOsPlatform($systemPlatform);
    }

    /**
     * リクエストヘッダの情報から課金ライブラリに渡す課金プラットフォーム名を取得する
     *
     * @return string
     */
    public static function getCurrencyBillingPlatformFromRequest(): string
    {
        return request()->getBillingPlatform();
    }
}
