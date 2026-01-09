<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit2Service;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKitApiCacheService;

/**
 * StoreKit2Service のテスト
 */
class StoreKit2ServiceTest extends TestCase
{
    protected $backupConfigKeys = [
        'wp_currency.store.app_store.sandbox_bundle_id',
        'wp_currency.store.app_store.production_bundle_id'
    ];

    private StoreKit2Service $storeKit2Service;
    private $mockJwsService;
    private $mockAppStoreServerApiService;
    private $mockCacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockJwsService = Mockery::mock(JwsService::class);
        $this->mockAppStoreServerApiService = Mockery::mock(AppStoreServerApiService::class);
        $this->mockCacheService = Mockery::mock(StoreKitApiCacheService::class);

        // デフォルトでキャッシュミスを設定（個別テストで上書き可能）
        $this->mockCacheService
            ->shouldReceive('getCachedResponse')
            ->andReturn(null)
            ->byDefault();
        $this->mockCacheService
            ->shouldReceive('cacheResponse')
            ->andReturnNull()
            ->byDefault();

        // StoreKit2Serviceは常にキャッシュサービスが必要
        $this->storeKit2Service = new StoreKit2Service(
            $this->mockJwsService,
            $this->mockAppStoreServerApiService,
            $this->mockCacheService
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function verifyTransaction_正常なStoreKit2トランザクション検証()
    {
        // Setup
        config(['wp_currency.store.app_store.sandbox_bundle_id' => 'com.test.app']);
        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'productId' => 'test-product',
            'bundleId' => 'com.test.app',
            'purchaseDate' => 1625097600000,
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'price' => 990,
            'currency' => 'JPY'
        ];

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);
        $this->mockAppStoreServerApiService
            ->shouldReceive('lookup')
            ->once()
            ->with($mockPayload['transactionId'], $mockPayload['environment'], $mockPayload['productId'])
            ->andReturn($mockPayload);

        // Execute
        $result = $this->storeKit2Service->verifyTransaction($signedTransactionInfo);

        // Verify
        $this->assertEquals($mockPayload, $result);
    }

    #[Test]
    public function verifyTransaction_transactionIdが空の場合はエラー()
    {
        // Setup
        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => '', // 空のtransactionId
            'productId' => 'test-product'
        ];

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('transactionId is required in JWS payload');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function verifyTransaction_transactionIdがnullの場合はエラー()
    {
        // Setup
        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => null, // nullのtransactionId
            'productId' => 'test-product'
        ];

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('transactionId is required in JWS payload');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function verifyTransaction_JWS検証失敗時はエラー()
    {
        // Setup
        $signedTransactionInfo = 'invalid.jws.token';

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andThrow(new Exception('Invalid JWS signature'));

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('JWS signature verification failed');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function extractReceiptInfo_ペイロードから適切な情報を抽出()
    {
        // Setup
        $payload = [
            'transactionId' => 'test-transaction-123',
            'productId' => 'premium_pack',
            'bundleId' => 'com.test.app',
            'purchaseDate' => 1625097600000,
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION,
            'price' => 1200,
            'currency' => 'USD'
        ];

        // Execute
        $result = $this->storeKit2Service->extractReceiptInfo($payload);

        // Verify
        $expected = [
            'receipt' => [
                'bundle_id' => 'com.test.app',
                'in_app' => [[
                    'product_id' => 'premium_pack',
                    'transaction_id' => 'test-transaction-123',
                    'purchase_date' => '2021-07-01T00:00:00+00:00', // ISO8601形式の日付文字列
                    'purchase_date_ms' => '1625097600000',
                ]],
            ],
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION,
            // price, currencyはStoreKit2ToLegacyReceiptConverterでは返さない
        ];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function extractReceiptInfo_欠損データにはデフォルト値が設定される()
    {
        // Setup
        $payload = [
            'transactionId' => 'test-transaction-123',
            'purchaseDate' => 1625097600000, // purchaseDateは必須のため追加
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION // environmentは必須
            // 他のフィールド（productId, bundleId）は意図的に省略
        ];

        // Execute
        $result = $this->storeKit2Service->extractReceiptInfo($payload);

        // Verify
        $expected = [
            'receipt' => [
                'bundle_id' => '', // bundleIdが省略されているため空文字列
                'in_app' => [[
                    'product_id' => '', // productIdが省略されているため空文字列
                    'transaction_id' => 'test-transaction-123',
                    'purchase_date' => '2021-07-01T00:00:00+00:00', // ISO8601形式の日付文字列
                    'purchase_date_ms' => '1625097600000',
                ]],
            ],
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * @group wp_billing_appstore
     */
    #[Test]
    public function verifyTransaction_本番環境でのApp_Store_Server_API連携()
    {
        // Setup
        // 本番環境でのテストはmockを使わず実際のAPIを呼び出す
        // 実際の環境でテストする場合のみ有効化
        $this->markTestSkipped('実際のApp Store Server API連携テストはスキップ');
    }

    #[Test]
    public function verifyTransaction_bundleIdが空の場合はエラー()
    {
        // Setup
        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'productId' => 'test-product',
            'bundleId' => '', // 空のbundleId
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
        ];

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('bundleId is required in JWS payload');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function verifyTransaction_bundleIdが不一致の場合はエラー()
    {
        // Setup - 期待されるbundle_idを設定
        config(['wp_currency.store.app_store.sandbox_bundle_id' => 'com.expected.app']);

        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'productId' => 'test-product',
            'bundleId' => 'com.wrong.app', // 不正なbundleId
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
        ];

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Bundle ID mismatch. Expected: com.expected.app, Received: com.wrong.app');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function verifyTransaction_bundleIdが一致する場合は正常終了()
    {
        // Setup - テスト環境設定
        config(['wp_currency.store.app_store.sandbox_bundle_id' => 'com.expected.app']);

        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'productId' => 'test-product',
            'bundleId' => 'com.expected.app', // 正しいbundleId
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'purchaseDate' => 1625097600000,
            'price' => 990,
            'currency' => 'JPY'
        ];

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);
        $this->mockAppStoreServerApiService
            ->shouldReceive('lookup')
            ->once()
            ->with($mockPayload['transactionId'], $mockPayload['environment'], $mockPayload['productId'])
            ->andReturn($mockPayload);

        // Execute
        $result = $this->storeKit2Service->verifyTransaction($signedTransactionInfo);

        // Verify
        $this->assertEquals($mockPayload, $result);
    }

    #[Test]
    public function verifyTransaction_JWS署名検証エラーは適切なエラーコードでスロー()
    {
        // Setup
        $signedTransactionInfo = 'invalid.jws.token';

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andThrow(new Exception('JWS signature verification failed'));

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('JWS signature verification failed');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function verifyTransaction_API通信エラーは適切なエラーコードでスロー()
    {
        // Setup
        $signedTransactionInfo = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.test.signature';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'productId' => 'test-product',
            'bundleId' => 'com.test.app',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION,
        ];
        // bundle_id設定
        config(['wp_currency.store.app_store.production_bundle_id' => 'com.test.app']);

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        $this->mockAppStoreServerApiService
            ->shouldReceive('lookup')
            ->once()
            ->with('test-transaction-id-123', AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION, 'test-product')
            ->andThrow(new Exception('App Store Server API connection failed'));

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('App Store Server API verification failed');

        $this->storeKit2Service->verifyTransaction($signedTransactionInfo);
    }

    #[Test]
    public function isSandboxEnvironmentFromJws_不正なJWS形式では例外()
    {
        // Setup
        $invalidJws = 'invalid.format'; // 2つのパートしかない

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Invalid JWS format: expected 3 parts separated by dots');

        $this->storeKit2Service->isSandboxEnvironmentFromJws($invalidJws);
    }

    #[Test]
    public function verifyTransaction_キャッシュヒット時はAPIコールをスキップしてキャッシュ結果を返す()
    {
        // Setup
        $signedTransactionInfo = 'test.jws.token';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'bundleId' => 'com.test.app',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
        ];
        $cachedResponse = ['cached' => 'response', 'transactionId' => 'test-transaction-id-123'];

        // bundle_id設定
        config(['wp_currency.store.app_store.production_bundle_id' => 'com.test.app']);

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        $this->mockCacheService
            ->shouldReceive('getCachedResponse')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($cachedResponse);

        // Execute
        $result = $this->storeKit2Service->verifyTransaction($signedTransactionInfo);

        // Verify
        $this->assertEquals($cachedResponse, $result, 'キャッシュヒット時はキャッシュされた結果が返される');
    }

    #[Test]
    public function verifyTransaction_キャッシュミス時はAPIコールを実行してキャッシュに保存()
    {
        // Setup
        $signedTransactionInfo = 'test.jws.token';
        $mockPayload = [
            'transactionId' => 'test-transaction-id-123',
            'productId' => 'test.product.id',
            'bundleId' => 'com.test.app',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
        ];
        $apiResponse = ['api' => 'response'];

        // bundle_id設定
        config(['wp_currency.store.app_store.production_bundle_id' => 'com.test.app']);

        $this->mockJwsService
            ->shouldReceive('verify')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn($mockPayload);

        $this->mockCacheService
            ->shouldReceive('getCachedResponse')
            ->once()
            ->with($signedTransactionInfo)
            ->andReturn(null); // キャッシュミス

        $this->mockAppStoreServerApiService
            ->shouldReceive('lookup')
            ->once()
            ->with('test-transaction-id-123', AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION, 'test.product.id')
            ->andReturn($apiResponse);

        $this->mockCacheService
            ->shouldReceive('cacheResponse')
            ->once()
            ->with(
                $signedTransactionInfo,
                'test-transaction-id-123',
                AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION,
                $apiResponse
            );

        // Execute
        $result = $this->storeKit2Service->verifyTransaction($signedTransactionInfo);

        // Verify
        $this->assertEquals($mockPayload, $result, 'キャッシュミス時は正常なペイロードが返される');
    }


}
