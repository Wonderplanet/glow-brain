<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIPlatformIdType;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * Bank KPI f001ログサービス
 */
class BankService
{
    /**
     * Fluentdタグを取得する
     * @param string $env
     * @param string $applicationId
     * @param string $format
     * @return string
     */
    public function getFluentdTag(string $env, string $applicationId, string $format): string
    {
        // 本番以外で確認する際は全てSTGに流す
        return match($env) {
            'production' => sprintf(BankKPIConstant::FLUENTD_TAG_PRD, $applicationId, $format),
            default => sprintf(BankKPIConstant::FLUENTD_TAG_STG, $applicationId, $format),
        };
    }

    /**
     * 数値のOSプラットフォームからプラットフォームIDを取得する
     * @param int $osPlatform
     * @return string
     * @throws \Exception
     */
    public function getPlatformIdByOsPlatformNum(int $osPlatform): string
    {
        return match ($osPlatform) {
            1 => BankKPIPlatformIdType::IOS->value,
            2 => BankKPIPlatformIdType::ANDROID->value,
            default => throw new \Exception('プラットフォーム指定が不正です'),
        };
    }

    /**
     * 文字列のOSプラットフォームからプラットフォームIDを取得する
     * @param string $osPlatform
     * @return string
     * @throws \Exception
     */
    public function getPlatformIdByOsPlatformString(string $osPlatform): string
    {
        return match ($osPlatform) {
            CurrencyConstants::OS_PLATFORM_IOS => BankKPIPlatformIdType::IOS->value,
            CurrencyConstants::OS_PLATFORM_ANDROID => BankKPIPlatformIdType::ANDROID->value,
            default => throw new \Exception('プラットフォーム指定が不正です'),
        };
    }

    /**
     * AD IDフォーマットチェック
     * @param string|null $adId
     * @return string
     */
    public function getFormattedOrDefaultAdId(?string $adId): string
    {
        if (empty($adId) || (preg_match(BankKPIConstant::PATTERN_AD_ID, $adId) !== 1)) {
            return BankKPIConstant::DEFAULT_AD_ID;
        }
        return $adId;
    }
}
