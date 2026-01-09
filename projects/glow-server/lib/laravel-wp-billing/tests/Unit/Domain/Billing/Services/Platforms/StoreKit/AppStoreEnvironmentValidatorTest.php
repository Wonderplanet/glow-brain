<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;

/**
 * AppStoreEnvironmentValidator のテスト
 */
class AppStoreEnvironmentValidatorTest extends TestCase
{
    /**
     * サンドボックス環境を正しく判定
     */
    #[Test]
    public function isSandbox_サンドボックス環境を正しく判定()
    {
        // Execute & Verify
        $this->assertTrue(AppStoreEnvironmentValidator::isSandbox(AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX));
    }

    /**
     * 本番環境を正しく判定
     */
    #[Test]
    public function isSandbox_本番環境を正しく判定()
    {
        // Execute & Verify
        $this->assertFalse(AppStoreEnvironmentValidator::isSandbox(AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION));
    }

    /**
     * 不正な環境値で例外がスローされる
     */
    #[Test]
    public function isSandbox_不正な環境値で例外()
    {
        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage("Invalid environment: 'Invalid'. Must be 'Production' or 'Sandbox'");

        AppStoreEnvironmentValidator::isSandbox('Invalid');
    }

    /**
     * 空文字列で例外がスローされる
     */
    #[Test]
    public function isSandbox_空文字列で例外()
    {
        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage("Invalid environment: ''. Must be 'Production' or 'Sandbox'");

        AppStoreEnvironmentValidator::isSandbox('');
    }

    /**
     * 環境定数が正しく定義されている
     */
    #[Test]
    public function 環境定数が正しく定義されている()
    {
        // Verify
        $this->assertEquals('Production', AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION);
        $this->assertEquals('Sandbox', AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX);
    }
}
