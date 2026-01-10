<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Utils;

use Illuminate\Support\Facades\Config;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * 各ストアのレシートに関するメソッドをまとめたUtility
 */
class StoreUtility
{
    /**
     * AppleStore設定からプロダクション向けバンドルIDを取得する
     *
     * @return string
     */
    public static function getProductionBundleId(): string
    {
        return Config::get('wp_currency.store.app_store.production_bundle_id', '');
    }

    /**
     * AppleStore設定からサンドボックス向けバンドルIDを取得する
     *
     * @return string
     */
    public static function getSandboxBundleId(): string
    {
        return Config::get('wp_currency.store.app_store.sandbox_bundle_id', '');
    }

    /**
     * GooglePlay設定からパッケージ名を取得する
     *  本番/sandboxで共通
     *
     * @return string
     */
    public static function getPackageName(): string
    {
        return Config::get('wp_currency.store.googleplay_store.package_name', '');
    }

    /**
     * sandbox情報と購入ストア情報からバンドルIDまたはパッケージ名を取得する
     *
     * @param bool $isSandbox
     * @param string $billingPlatform
     * @return string
     */
    public static function getBundleIdOrPackageName(bool $isSandbox, string $billingPlatform): string
    {
        if ($billingPlatform === CurrencyConstants::PLATFORM_GOOGLEPLAY) {
            // GooglePlayならパッケージ名を返す
            return self::getPackageName();
        }

        // 以降はAppleStore
        if ($isSandbox) {
            // sandbox環境用のバンドルIDを返す
            return self::getSandboxBundleId();
        }

        // 本番用のバンドルIDを返す
        return self::getProductionBundleId();
    }
}
