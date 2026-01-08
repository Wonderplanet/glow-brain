<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit2ToLegacyReceiptConverter;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;

/**
 * StoreKit2ToLegacyReceiptConverter のテスト
 */
class StoreKit2ToLegacyReceiptConverterTest extends TestCase
{
    #[Test]
    public function convert_基本的なペイロードを正しく変換()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'productId' => 'prod001',
            'bundleId' => 'com.example.app',
            'purchaseDate' => 1687693496000, // 2023-06-25T11:44:56.000Z のエポックミリ秒
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
        ];

        // Execute
        $result = StoreKit2ToLegacyReceiptConverter::convert($payload);

        // Verify
        $this->assertEquals('com.example.app', $result['receipt']['bundle_id']);
        $this->assertEquals('prod001', $result['receipt']['in_app'][0]['product_id']);
        $this->assertEquals('tx123', $result['receipt']['in_app'][0]['transaction_id']);
        $this->assertEquals('2023-06-25T11:44:56+00:00', $result['receipt']['in_app'][0]['purchase_date']);
        $this->assertEquals('1687693496000', $result['receipt']['in_app'][0]['purchase_date_ms']);
        $this->assertEquals(AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX, $result['environment']);
    }

    #[Test]
    public function convert_欠損フィールドにはデフォルト値が設定される()
    {
        // Setup
        $payload = [
            'transactionId' => 'test-transaction-123',
            'purchaseDate' => 1640995200000, // 2022-01-01T00:00:00.000Z のエポックミリ秒
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION,
        ];

        // Execute
        $result = StoreKit2ToLegacyReceiptConverter::convert($payload);

        // Verify
        $this->assertEquals('', $result['receipt']['bundle_id']);
        $this->assertEquals('', $result['receipt']['in_app'][0]['product_id']);
        $this->assertEquals('test-transaction-123', $result['receipt']['in_app'][0]['transaction_id']);
        $this->assertEquals('2022-01-01T00:00:00+00:00', $result['receipt']['in_app'][0]['purchase_date']);
        $this->assertEquals('1640995200000', $result['receipt']['in_app'][0]['purchase_date_ms']);
        $this->assertEquals(AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION, $result['environment']);
    }

    #[Test]
    public function convert_environment不足でエラー()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'purchaseDate' => 1687693496000,
            // environment が不足
        ];

        // Execute & Verify
        $this->expectException(\WonderPlanet\Domain\Billing\Exceptions\WpBillingException::class);
        $this->expectExceptionMessage('StoreKit2 payloadにenvironmentが存在しません');
        StoreKit2ToLegacyReceiptConverter::convert($payload);
    }

    #[Test]
    public function convert_purchaseDate不足でエラー()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            // purchaseDate が不足
        ];

        // Execute & Verify
        $this->expectException(\WonderPlanet\Domain\Billing\Exceptions\WpBillingException::class);
        $this->expectExceptionMessage('StoreKit2 payloadにpurchaseDateが存在しません');
        StoreKit2ToLegacyReceiptConverter::convert($payload);
    }

    #[Test]
    public function convert_purchaseDate型不正でエラー()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'purchaseDate' => 123.45, // 浮動小数点数型（不正）
        ];

        // Execute & Verify
        $this->expectException(\WonderPlanet\Domain\Billing\Exceptions\WpBillingException::class);
        $this->expectExceptionMessage('StoreKit2 purchaseDateは数値型またはISO8601文字列である必要があります');
        StoreKit2ToLegacyReceiptConverter::convert($payload);
    }

    #[Test]
    public function convert_purchaseDateがISO8601文字列で正常動作()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'productId' => 'premium_pack',
            'bundleId' => 'com.test.app',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'purchaseDate' => '2021-07-01T00:00:00+00:00', // ISO8601文字列
        ];

        // Execute
        $result = StoreKit2ToLegacyReceiptConverter::convert($payload);

        // Verify
        $this->assertEquals('com.test.app', $result['receipt']['bundle_id']);
        $this->assertEquals('tx123', $result['receipt']['in_app'][0]['transaction_id']);
        $this->assertEquals('premium_pack', $result['receipt']['in_app'][0]['product_id']);
        $this->assertEquals('2021-07-01T00:00:00+00:00', $result['receipt']['in_app'][0]['purchase_date']);
        $this->assertEquals('1625097600000', $result['receipt']['in_app'][0]['purchase_date_ms']);
        $this->assertEquals(AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX, $result['environment']);
    }

    #[Test]
    public function convert_purchaseDateが不正なISO8601文字列でエラー()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'purchaseDate' => 'invalid-date-string', // 不正なISO8601文字列
        ];

        // Execute & Verify
        $this->expectException(\WonderPlanet\Domain\Billing\Exceptions\WpBillingException::class);
        $this->expectExceptionMessage('StoreKit2 purchaseDateのISO8601形式が不正です');
        StoreKit2ToLegacyReceiptConverter::convert($payload);
    }

    #[Test]
    public function convert_purchaseDateが空文字列でエラー()
    {
        // Setup
        $payload = [
            'transactionId' => 'tx123',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'purchaseDate' => '', // 空文字列（不正）
        ];

        // Execute & Verify
        $this->expectException(\WonderPlanet\Domain\Billing\Exceptions\WpBillingException::class);
        $this->expectExceptionMessage('StoreKit2 purchaseDateは数値型またはISO8601文字列である必要があります');
        StoreKit2ToLegacyReceiptConverter::convert($payload);
    }
}
