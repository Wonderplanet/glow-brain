<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;

/**
 * App Store環境値のバリデーション・判定ユーティリティ
 */
class AppStoreEnvironmentValidator
{
    /**
     * 環境定数: 本番環境
     */
    public const ENVIRONMENT_PRODUCTION = 'Production';

    /**
     * 環境定数: サンドボックス環境
     */
    public const ENVIRONMENT_SANDBOX = 'Sandbox';

    /**
     * 環境がSandboxかどうかを判定
     * @param string $environment 判定対象の環境値
     * @return bool Sandboxの場合true、Productionの場合false
     * @throws WpBillingException 不正な環境値の場合
     */
    public static function isSandbox(string $environment): bool
    {
        if ($environment === self::ENVIRONMENT_SANDBOX) {
            return true;
        }
        if ($environment === self::ENVIRONMENT_PRODUCTION) {
            return false;
        }
        throw new WpBillingException(
            "Invalid environment: '{$environment}'. Must be '"
            . self::ENVIRONMENT_PRODUCTION . "' or '" . self::ENVIRONMENT_SANDBOX . "'",
            ErrorCode::INVALID_ENVIRONMENT
        );
    }
}
