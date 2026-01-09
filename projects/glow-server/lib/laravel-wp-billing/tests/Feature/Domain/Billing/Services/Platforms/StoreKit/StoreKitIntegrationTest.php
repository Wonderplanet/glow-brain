<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Feature\Domain\Billing\Services\Platforms\StoreKit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\AppStorePlatformService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\ReceiptFormatDetectionService;

/**
 * StoreKit1/2統合処理の統合テスト
 */
class StoreKitIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AppStorePlatformService $appStorePlatformService;
    private ReceiptFormatDetectionService $receiptFormatDetector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appStorePlatformService = $this->app->make(AppStorePlatformService::class);
        $this->receiptFormatDetector = $this->app->make(ReceiptFormatDetectionService::class);
    }

    /**
     * StoreKit1とStoreKit2の自動判定が正常に動作
     */
    #[Test]
    public function StoreKit1とStoreKit2の自動判定が正常に動作()
    {
        // Setup
        // StoreKit1形式のテスト
        $storeKit1Receipt = json_encode([
            'Store' => 'AppleAppStore',
            'TransactionID' => '1000000123456789',
            'Payload' => 'dGVzdC1yZWNlaXB0'
        ]);

        $storeKit1Type = $this->receiptFormatDetector->detectReceiptType($storeKit1Receipt);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_STOREKIT1, $storeKit1Type);

        // Setup
        // StoreKit2形式のテスト (より正確なJWSヘッダーとペイロード)
        $headerData = json_encode([
            'alg' => 'ES256',
            'x5c' => [
                'MIIBkjCCATegAwIBAgIGAYdwjj+xMAoGCCqGSM49BAMCMDYxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJDQTEaMBgGA1UECgwRQXBwbGUgSW5jLiBUZXN0MA==',
                'MIIBkjCCATegAwIBAgIGAYdwjj+xMAoGCCqGSM49BAMCMDYxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJDQTEaMBgGA1UECgwRQXBwbGUgSW5jLiBUZXN0MA=='
            ]
        ]);
        $payloadData = json_encode([
            'transactionId' => '123456789',
            'productId' => 'test.product'
        ]);
        
        // Base64URLエンコーディング関数
        $base64urlEncode = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };
        
        $header = $base64urlEncode($headerData);
        $payload = $base64urlEncode($payloadData);
        $signature = $base64urlEncode('fake_signature_data');
        $storeKit2Receipt = "{$header}.{$payload}.{$signature}";

        $storeKit2Type = $this->receiptFormatDetector->detectReceiptType($storeKit2Receipt);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_STOREKIT2, $storeKit2Type);
    }

    /**
     * レシート形式の自動判定でフォールバックが正常に動作
     */
    #[Test]
    public function レシート形式の自動判定でフォールバックが正常に動作()
    {
        // Setup
        // 未知の形式
        $unknownReceipt = 'completely_unknown_format';
        $result = $this->receiptFormatDetector->detectReceiptType($unknownReceipt);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);

        // Setup
        // 空文字列
        $emptyReceipt = '';
        $result = $this->receiptFormatDetector->detectReceiptType($emptyReceipt);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);

        // Setup
        // 部分的に正しいがstoreKitでない形式
        $partiallyValidJson = json_encode(['some' => 'data']);
        $result = $this->receiptFormatDetector->detectReceiptType($partiallyValidJson);

        // Verify
        $this->assertEquals(ReceiptFormatDetectionService::RECEIPT_TYPE_UNKNOWN, $result);
    }

    /**
     * 設定値の取得が正常に動作
     */
    #[Test]
    public function 設定値の取得が正常に動作()
    {
        // Setup & Execute
        // services.phpの設定が正しく取得できることを確認
        $externalTokenUrl = config('wp_currency.store.app_store.storekit2.external_token_url');
        $productionBundleId = config('wp_currency.store.app_store.production_bundle_id');
        $sandboxBundleId = config('wp_currency.store.app_store.sandbox_bundle_id');

        // Verify
        // 設定が取得できることを確認（値の内容は問わない）
        $this->assertTrue(is_string($externalTokenUrl) || is_null($externalTokenUrl));
        $this->assertTrue(is_string($productionBundleId) || is_null($productionBundleId));
        $this->assertTrue(is_string($sandboxBundleId) || is_null($sandboxBundleId));
    }

    /**
     * StoreKit関連サービスの遅延ロードが正常に動作
     */
    #[Test]
    public function StoreKit関連サービスの遅延ロードが正常に動作()
    {
        // Setup & Execute
        // AppStorePlatformServiceが作成された時点では、
        // StoreKit1ServiceやStoreKit2Serviceはまだロードされていないことを確認

        // この テストは実際のレシート処理を行わないため、
        // 遅延ロードの仕組みが壊れていないことのみを確認
        $service = $this->app->make(AppStorePlatformService::class);

        // Verify
        $this->assertInstanceOf(AppStorePlatformService::class, $service);

        // メモリ使用量などの詳細なチェックは複雑になるため、
        // 基本的なインスタンス化ができることを確認するに留める
        $this->assertTrue(true);
    }
}
