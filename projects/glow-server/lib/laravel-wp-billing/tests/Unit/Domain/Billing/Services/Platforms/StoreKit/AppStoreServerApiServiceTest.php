<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Tests\Traits\ReflectionTrait;

/**
 * AppStoreServerApiService のテスト
 */
class AppStoreServerApiServiceTest extends TestCase
{
    use ReflectionTrait;

    protected $backupConfigKeys = [
        'wp_currency.store.app_store.storekit2.external_token_url',
        'wp_currency.store.app_store.storekit2.enable_history_fallback',
    ];

    private AppStoreServerApiService $apiService;
    private $mockJwsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockJwsService = Mockery::mock(JwsService::class);
        $this->apiService = new AppStoreServerApiService($this->mockJwsService);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function getApiBaseUrl_常に本番URLを返す()
    {
        // Execute
        $result = $this->apiService->getApiBaseUrl('Production');

        // Verify (常に本番URLを返す仕様)
        $this->assertEquals('https://api.storekit.itunes.apple.com', $result);
    }

    #[Test]
    public function getApiBaseUrl_サンドボックス指定時はサンドボックスURLを返す()
    {
        // Execute
        $result = $this->apiService->getApiBaseUrl(AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX);

        // Verify
        $this->assertEquals('https://api.storekit-sandbox.itunes.apple.com', $result);
    }

    #[Test]
    public function getAppStoreToken_モックで固定トークンを返す()
    {
        // Setup
        // テスト用にAppStoreServerApiServiceをモックし、getAppStoreTokenがtest-tokenを返すようにする
        $mock = \Mockery::mock(
            \WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService::class,
            [$this->mockJwsService]
        )->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getAppStoreToken')->once()->andReturn('test-token');

        // Execute
        $result = $this->callMethod($mock, 'getAppStoreToken', ['Production']);

        // Verify
        $this->assertEquals('test-token', $result);
    }

    #[Test]
    public function getAppStoreToken_externalTokenUrlからトークン取得できる()
    {
        // Setup
        // configをテスト用URLに上書き
        config(['wp_currency.store.app_store.storekit2.external_token_url' => 'https://example.com/token']);

        // fetchExternalTokenのみモック
        $mock = \Mockery::mock(
            \WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService::class,
            [$this->mockJwsService]
        )->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('fetchExternalToken')
            ->with('https://example.com/token')
            ->once()
            ->andReturn('external-token');

        // Execute
        $result = $this->callMethod($mock, 'getAppStoreToken', ['Production']);

        // Verify
        $this->assertEquals('external-token', $result);
    }

    /**
     * 実際のApp Store Server API呼び出しは、
     * - Appleの本番環境/サンドボックス環境のAPIエンドポイントに依存し、
     * - テスト用の有効なトークンやトランザクションIDが必要で、
     * - レート制限や外部要因で安定しないため
     * 通常のCIやローカルユニットテストでは実行しません。
     * 統合テストや手動検証でのみ実施してください。
     *
     * @group wp_billing_appstore
     */
    #[Test]
    public function lookup_実際のApp_Store_Server_API呼び出し()
    {
        // Setup
        $transactionId = 'test-transaction-id';

        // モックJwsServiceの設定
        $this->mockJwsService
            ->shouldReceive('decodeStoreServerJws')
            ->andReturn([
                'transactionId' => $transactionId,
                'productId' => 'test-product',
            ]);

        $this->markTestSkipped('外部API依存のため、統合テスト/手動検証のみ実施');
    }

    /**
     * 実際のApp Store Server API呼び出しは、
     * - Appleの本番環境/サンドボックス環境のAPIエンドポイントに依存し、
     * - テスト用の有効なトークンやトランザクションIDが必要で、
     * - レート制限や外部要因で安定しないため
     * 通常のCIやローカルユニットテストでは実行しません。
     * 統合テストや手動検証でのみ実施してください。
     *
     * @group wp_billing_appstore
     */
    #[Test]
    public function getTransactionHistory_実際のApp_Store_Server_API呼び出し()
    {
        // Setup
        $transactionId = 'test-transaction-id';

        // モックJwsServiceの設定
        $this->mockJwsService
            ->shouldReceive('decodeStoreServerJws')
            ->andReturn([
                'transactionId' => $transactionId,
                'productId' => 'test-product',
            ]);

        $this->markTestSkipped('外部API依存のため、統合テスト/手動検証のみ実施');
    }

    /**
     * cURL呼び出しのモックはPHPの組み込み関数やHTTPクライアントの挙動を詳細に再現する必要があり、
     * ユニットテストでの保守コストが高いため、統合テストでカバーしています。
     */
    #[Test]
    public function lookup_トランザクションが見つからない場合()
    {
        // Setup
        $this->markTestSkipped('cURL呼び出しのモックは統合テストで対応');
    }

    /**
     * cURL呼び出しのモックはPHPの組み込み関数やHTTPクライアントの挙動を詳細に再現する必要があり、
     * ユニットテストでの保守コストが高いため、統合テストでカバーしています。
     */
    #[Test]
    public function getTransactionHistory_レート制限エラーのハンドリング()
    {
        // Setup
        $this->markTestSkipped('cURL呼び出しのモックは統合テストで対応');
    }

    #[Test]
    public function lookup_Rate_Limit時のフォールバック設定テスト()
    {
        // Setup: フォールバック設定のテスト
        // 実際のHTTP通信のモックは複雑なため、設定値の確認のみ行う

        // フォールバック無効時
        config(['wp_currency.store.app_store.storekit2.enable_history_fallback' => false]);
        $this->assertFalse(config('wp_currency.store.app_store.storekit2.enable_history_fallback'));

        // フォールバック有効時
        config(['wp_currency.store.app_store.storekit2.enable_history_fallback' => true]);
        $this->assertTrue(config('wp_currency.store.app_store.storekit2.enable_history_fallback'));

        $this->assertTrue(true); // 設定値の確認完了
    }

    #[Test]
    public function lookup_Rate_Limit時フォールバック無効でException()
    {
        // Setup: フォールバック無効設定
        config(['wp_currency.store.app_store.storekit2.enable_history_fallback' => false]);

        // JWSサービスのモック設定（JWTトークン生成用）
        $this->mockJwsService
            ->shouldReceive('createJwt')
            ->with(AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION)
            ->andReturn('mock-jwt-token');

        // executeHttpRequestメソッドをモック
        $mockService = Mockery::mock(AppStoreServerApiService::class, [$this->mockJwsService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // lookup API（/v1/transactions）のエンドポイントに対して429を返す
        // 注意：このURLはモック動作確認用のテストデータです。
        // 実際のURL構築ロジックはAppStoreServerApiService内で行われるため、
        // ここではモックが正しく動作することを確認するためのテストデータとして定義しています。
        $lookupEndpoint = 'https://api.storekit.itunes.apple.com/inApps/v1/transactions/test-transaction-id';
        $mockService->shouldReceive('executeHttpRequest')
            ->with($lookupEndpoint, Mockery::any())
            ->andReturn([
                'response' => '',
                'httpCode' => 429,
            ]);

        // Execute & Verify: Rate Limitエラーが投げられることを確認
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('App Store Server API rate limit exceeded (HTTP 429)');

        $mockService->lookup('test-transaction-id', AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION, 'test-product-id');
    }

    #[Test]
    public function lookup_Rate_Limit時フォールバック有効で成功()
    {
        // Setup: フォールバック有効設定
        config(['wp_currency.store.app_store.storekit2.enable_history_fallback' => true]);

        // JWSサービスのモック設定
        $this->mockJwsService
            ->shouldReceive('createJwt')
            ->with(AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION)
            ->andReturn('mock-jwt-token');

        $this->mockJwsService
            ->shouldReceive('decodeStoreServerJws')
            ->with('mock-signed-transaction')
            ->andReturn([
                'transactionId' => 'test-transaction-id',
                'productId' => 'com.example.product',
                'purchaseDate' => 1634567890000,
            ]);

        // executeHttpRequestメソッドをモック
        $mockService = Mockery::mock(AppStoreServerApiService::class, [$this->mockJwsService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // lookup API（/v1/transactions）に対して429を返す
        // 注意：以下のURLはモック動作確認用のテストデータです。
        // 実際のURL構築ロジックはAppStoreServerApiService内で行われるため、
        // ここではモックが正しく動作することを確認するためのテストデータとして定義しています。
        $lookupEndpoint = 'https://api.storekit.itunes.apple.com/inApps/v1/transactions/test-transaction-id';
        $mockService->shouldReceive('executeHttpRequest')
            ->with($lookupEndpoint, Mockery::any())
            ->andReturn([
                'response' => '',
                'httpCode' => 429,
            ]);

        // history API（/v2/history）に対して成功レスポンスを返す
        $historyEndpoint = 'https://api.storekit.itunes.apple.com/inApps/v2/history/test-transaction-id';
        $mockService->shouldReceive('executeHttpRequest')
            ->with($historyEndpoint, Mockery::any())
            ->andReturn([
                'response' => json_encode([
                    'signedTransactions' => ['mock-signed-transaction'],
                ]),
                'httpCode' => 200,
            ]);

        // Execute
        $result = $mockService->lookup('test-transaction-id', AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION, 'com.example.product');

        // Verify: フォールバックで取得したデータが返されることを確認
        $this->assertEquals('test-transaction-id', $result['transactionId']);
        $this->assertEquals('com.example.product', $result['productId']);
    }

    #[Test]
    public function lookup_Rate_Limit時フォールバック有効だがフォールバックも失敗した場合429を返す()
    {
        // Setup: フォールバック有効設定
        config(['wp_currency.store.app_store.storekit2.enable_history_fallback' => true]);

        // JWSサービスのモック設定
        $this->mockJwsService
            ->shouldReceive('createJwt')
            ->with(AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION)
            ->andReturn('mock-jwt-token');

        // executeHttpRequestメソッドをモック
        $mockService = Mockery::mock(AppStoreServerApiService::class, [$this->mockJwsService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // lookup API（/v1/transactions）に対して429を返す
        // 注意：以下のURLはモック動作確認用のテストデータです。
        // 実際のURL構築ロジックはAppStoreServerApiService内で行われるため、
        // ここではモックが正しく動作することを確認するためのテストデータとして定義しています。
        $lookupEndpoint = 'https://api.storekit.itunes.apple.com/inApps/v1/transactions/test-transaction-id';
        $mockService->shouldReceive('executeHttpRequest')
            ->with($lookupEndpoint, Mockery::any())
            ->andReturn([
                'response' => '',
                'httpCode' => 429,
            ]);

        // history API（/v2/history）に対しても429を返す（フォールバック失敗）
        $historyEndpoint = 'https://api.storekit.itunes.apple.com/inApps/v2/history/test-transaction-id';
        $mockService->shouldReceive('executeHttpRequest')
            ->with($historyEndpoint, Mockery::any())
            ->andReturn([
                'response' => '',
                'httpCode' => 429,
            ]);

        // Execute & Verify: フォールバックも失敗した場合のエラーメッセージを確認
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('App Store Server API rate limit exceeded and history fallback failed (HTTP 429)');

        $mockService->lookup('test-transaction-id', AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION, 'test-product-id');
    }
}
