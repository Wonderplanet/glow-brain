<?php

declare(strict_types=1);

namespace App\Domain\Currency\Utils;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Exceptions\GameException;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * 購入処理に関するユーティリティクラス
 */
class CurrencyUtility
{
    /**
     * billing/currencyライブラリで使用する購入プラットフォーム名を取得する
     *
     * TODO: platformとbilling_platformは別々になる想定なので、
     * クライアントからbilling_platformが取得できるようになったらこのメソッドは削除する
     *
     * 本来、OSプラットフォームと課金プラットフォームは別物だが、
     * 開発中の互換性のためにiOSとAndroidをGooglePlayとAppStoreに変換する
     *
     * TODO:
     *  ※このメソッドは一時的な互換性のためにあります。プラットフォーム部分のリファクタが終わったら消してください。
     *   本来、OSプラットフォームと課金プラットフォームは別物となります。
     *   実装場の都合でiOS=App Store、Android=Google Playとしている部分があるため、このメソッドを用意しています。
     *   課金プラットフォームが必要な場合、BaseRequest::billingPlatform()を使用してください。
     *
     * @deprecated
     * @param string $platform
     * @return string
     */
    public static function getBillingPlatform(string $platform): string
    {
        return match ($platform) {
            System::PLATFORM_ANDROID => CurrencyConstants::PLATFORM_GOOGLEPLAY,
            System::PLATFORM_IOS => CurrencyConstants::PLATFORM_APPSTORE,
            default => throw new GameException(
                ErrorCode::INVALID_PLATFORM,
                "invalid platform platform:{$platform} from:getBillingPlatform"
            ),
        };
    }

    /**
     * プラットフォーム名から課金基盤向けのOSプラットフォームを取得
     *
     * 基盤ライブラリ側で定義されている値に変換する
     *
     * @param string $platform
     * @return string
     */
    public static function getOsPlatform(string $platform): string
    {
        return match ($platform) {
            System::PLATFORM_ANDROID => CurrencyConstants::OS_PLATFORM_ANDROID,
            System::PLATFORM_IOS => CurrencyConstants::OS_PLATFORM_IOS,
            System::PLATFORM_WEBSTORE => CurrencyConstants::OS_PLATFORM_WEBSTORE,
            default => throw new GameException(
                ErrorCode::INVALID_PLATFORM,
                "invalid platform platform:{$platform} from:getOsPlatform"
            ),
        };
    }

    /**
     * 課金プラットフォーム名からOSプラットフォームを取得
     *
     * 本来、OSプラットフォームと課金プラットフォームは別物だが、
     * 開発中の互換性のためにiOSとAndroidをGooglePlayとAppStoreに変換する
     *
     * TODO:
     *  ※このメソッドは一時的な互換性のためにあります。プラットフォーム部分のリファクタが終わったら消してください。
     *   本来、OSプラットフォームと課金プラットフォームは別物となります。
     *   実装場の都合でiOS=App Store、Android=Google Playとしている部分があるため、このメソッドを用意しています。
     *   課金プラットフォームが必要な場合、BaseRequest::billingPlatform()を使用してください。
     *
     * @deprecated
     * @param string $platform
     * @return string
     */
    public static function getOsPlatformByBillingPlatform(string $platform): string
    {
        return match ($platform) {
            CurrencyConstants::PLATFORM_APPSTORE => CurrencyConstants::OS_PLATFORM_IOS,
            CurrencyConstants::PLATFORM_GOOGLEPLAY => CurrencyConstants::OS_PLATFORM_ANDROID,
            default => throw new GameException(
                ErrorCode::INVALID_PLATFORM,
                "invalid platform platform:{$platform} from:getOsPlatformByBillingPlatform"
            ),
        };
    }
}
