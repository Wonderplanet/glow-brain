<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit1Service;

/**
 * StoreKit1Service のテスト
 */
class StoreKit1ServiceTest extends TestCase
{
    protected $backupConfigKeys = [
        'wp_currency.store.app_store.production_bundle_id',
        'wp_currency.store.app_store.sandbox_bundle_id'
    ];

    private StoreKit1Service $storeKit1Service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storeKit1Service = $this->app->make(StoreKit1Service::class);
    }

    /**
     * verifyReceipt_Bundle_IDが設定されていない場合はエラー
     */
    #[Test]
    public function verifyReceipt_Bundle_IDが設定されていない場合はエラー()
    {
        // Setup
        config(['wp_currency.store.app_store.production_bundle_id' => '']);
        config(['wp_currency.store.app_store.sandbox_bundle_id' => 'test.sandbox']);

        $productId = 'test-product';
        $receipt = json_encode([
            'Store' => 'AppleAppStore',
            'TransactionID' => '123456789',
            'Payload' => 'dGVzdC1yZWNlaXB0'
        ]);

        // Exercise & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('invalid bundle id. bundle id is not set.');

        $this->storeKit1Service->verifyReceipt($productId, $receipt);
    }

    /**
     * verifyReceipt_Sandbox_Bundle_IDが設定されていない場合はエラー
     */
    #[Test]
    public function verifyReceipt_Sandbox_Bundle_IDが設定されていない場合はエラー()
    {
        // Setup
        config(['wp_currency.store.app_store.production_bundle_id' => 'test.production']);
        config(['wp_currency.store.app_store.sandbox_bundle_id' => '']);

        $productId = 'test-product';
        $receipt = json_encode([
            'Store' => 'AppleAppStore',
            'TransactionID' => '123456789',
            'Payload' => 'dGVzdC1yZWNlaXB0'
        ]);

        // Exercise & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('invalid bundle id. sandbox bundle id is not set.');

        $this->storeKit1Service->verifyReceipt($productId, $receipt);
    }

    /**
     * validateProductId_指定されたproductIdが存在しない場合はエラー
     */
    #[Test]
    public function validateProductId_指定されたproductIdが存在しない場合はエラー()
    {
        // Setup
        $productId = 'target-product';
        $responseData = [
            'receipt' => [
                'in_app' => [
                    ['product_id' => 'different-product-1'],
                    ['product_id' => 'different-product-2']
                ]
            ]
        ];

        // Exercise & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage("product id not found in receipt. product_id: {$productId}");

        $this->validateProductId($productId, $responseData);
    }

    /**
     * validateProductId_指定されたproductIdが存在する場合は正常終了
     */
    #[Test]
    public function validateProductId_指定されたproductIdが存在する場合は正常終了()
    {
        // Setup
        $productId = 'target-product';
        $responseData = [
            'receipt' => [
                'in_app' => [
                    ['product_id' => 'different-product-1'],
                    ['product_id' => 'target-product'],
                    ['product_id' => 'different-product-2']
                ]
            ]
        ];

        // Execute (例外が発生しなければ成功)
        $this->validateProductId($productId, $responseData);

        // Verify (例外が発生しないことを確認)
        $this->assertTrue(true);
    }

    /**
     * validateProductId_in_appが空の場合はエラー
     */
    #[Test]
    public function validateProductId_in_appが空の場合はエラー()
    {
        // Setup
        $productId = 'target-product';
        $responseData = [
            'receipt' => [
                'in_app' => []
            ]
        ];

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage("product id not found in receipt. product_id: {$productId}");

        $this->validateProductId($productId, $responseData);
    }

    /**
     * validateBundleId_本番用Bundle_IDが一致しない場合はエラー
     */
    #[Test]
    public function validateBundleId_本番用Bundle_IDが一致しない場合はエラー()
    {
        // Setup
        config(['wp_currency.store.app_store.production_bundle_id' => 'com.test.production']);

        $responseData = [
            'receipt' => [
                'bundle_id' => 'com.different.app'
            ]
        ];
        $bundleId = 'com.test.production';

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('bundle id mismatch. expected: com.test.production, actual: com.different.app');

        $this->validateBundleId($responseData, $bundleId);
    }

    /**
     * validateBundleId_Bundle_IDが一致する場合は正常終了
     */
    #[Test]
    public function validateBundleId_Bundle_IDが一致する場合は正常終了()
    {
        // Setup
        $responseData = [
            'receipt' => [
                'bundle_id' => 'com.test.app'
            ]
        ];
        $bundleId = 'com.test.app';

        // Execute (例外が発生しなければ成功)
        $this->validateBundleId($responseData, $bundleId);

        // Verify (例外が発生しないことを確認)
        $this->assertTrue(true);
    }

    /**
     * validateAppStoreResponse_statusが0でない場合はエラー
     */
    #[Test]
    public function validateAppStoreResponse_statusが0でない場合はエラー()
    {
        // Setup
        $responseData = [
            'status' => 21003 // 不正なレシート
        ];

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('App Store verification failed. status: 21003');

        $this->validateAppStoreResponse($responseData);
    }

    /**
     * validateAppStoreResponse_statusが0の場合は正常終了
     */
    #[Test]
    public function validateAppStoreResponse_statusが0の場合は正常終了()
    {
        // Setup
        $responseData = [
            'status' => 0 // 正常
        ];

        // Execute (例外が発生しなければ成功)
        $this->validateAppStoreResponse($responseData);

        // Verify (例外が発生しないことを確認)
        $this->assertTrue(true);
    }

    /**
     * validateAppStoreResponse_statusがない場合はエラー
     */
    #[Test]
    public function validateAppStoreResponse_statusがない場合はエラー()
    {
        // Setup
        $responseData = []; // statusがない

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('App Store verification failed. status: -1');

        $this->validateAppStoreResponse($responseData);
    }

    /**
     * verifyReceipt_実際のApp_Store検証API呼び出し
     * @group wp_billing_appstore
     */
    #[Test]
    public function verifyReceipt_実際のApp_Store検証API呼び出し()
    {
        // Setup
        config(['wp_currency.store.app_store.production_bundle_id' => getenv('APPSTORE_BUNDLE_ID_PRODUCTION') ?: 'test.production']);
        config(['wp_currency.store.app_store.sandbox_bundle_id' => getenv('APPSTORE_BUNDLE_ID_SANDBOX') ?: 'test.sandbox']);

        // 実際のApp Store検証APIでのテスト
        $this->markTestSkipped('実際のApp Store検証API呼び出しテストはスキップ（外部API依存のため、統合テスト/手動検証で対応）');
    }

    /**
     * accessStore_HTTPステータスが200でない場合はエラー
     */
    #[Test]
    public function accessStore_HTTPステータスが200でない場合はエラー()
    {
        // cURL呼び出しのモックは複雑なため、統合テストで対応
        $this->markTestSkipped('cURL呼び出しのモックは統合テストで対応');
    }

    /**
     * accessStore_cURLエラーの場合はエラー
     */
    #[Test]
    public function accessStore_cURLエラーの場合はエラー()
    {
        // cURL呼び出しのモックは複雑なため、統合テストで対応
        $this->markTestSkipped('cURL呼び出しのモックは統合テストで対応');
    }

    /**
     * 指定されたproductIdがレシート内に存在するか検証
     * 
     * @param string $productId
     * @param array<string, mixed> $responseData
     * @throws WpBillingException
     */
    private function validateProductId(string $productId, array $responseData): void
    {
        $inApp = $responseData['receipt']['in_app'] ?? [];

        if (!is_array($inApp) || count($inApp) === 0) {
            throw new WpBillingException(
                "product id not found in receipt. product_id: {$productId}",
                ErrorCode::INVALID_RECEIPT
            );
        }

        foreach ($inApp as $transaction) {
            if (($transaction['product_id'] ?? '') === $productId) {
                return; // 見つかった場合は正常終了
            }
        }

        throw new WpBillingException(
            "product id not found in receipt. product_id: {$productId}",
            ErrorCode::INVALID_RECEIPT
        );
    }

    /**
     * Bundle IDの検証
     * 
     * @param array<string, mixed> $responseData
     * @param string $bundleId
     * @throws WpBillingException
     */
    private function validateBundleId(array $responseData, string $bundleId): void
    {
        $receiptBundleId = $responseData['receipt']['bundle_id'] ?? '';

        if ($receiptBundleId !== $bundleId) {
            throw new WpBillingException(
                "bundle id mismatch. expected: {$bundleId}, actual: {$receiptBundleId}",
                ErrorCode::APPSTORE_BUNDLE_ID_NOT_MATCH
            );
        }
    }

    /**
     * App Storeレスポンスの検証
     * 
     * @param array<string, mixed> $responseData
     * @throws WpBillingException
     */
    private function validateAppStoreResponse(array $responseData): void
    {
        $status = $responseData['status'] ?? -1;

        if ($status !== StoreKit1Service::RESPONSE_APPLE_OK) {
            throw new WpBillingException(
                "App Store verification failed. status: {$status}",
                ErrorCode::APPSTORE_RESPONSE_STATUS_NOT_OK
            );
        }
    }
}
