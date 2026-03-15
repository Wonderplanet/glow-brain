<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit2Service;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKitApiCacheService;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use Mockery;

/**
 * StoreKit2Service のサンドボックス判定テスト
 */
class StoreKit2ServiceSandboxTest extends TestCase
{
    private StoreKit2Service $storeKit2Service;

    protected function setUp(): void
    {
        parent::setUp();

        $mockJwsService = Mockery::mock(JwsService::class);
        $mockAppStoreServerApiService = Mockery::mock(AppStoreServerApiService::class);
        $mockCacheService = Mockery::mock(StoreKitApiCacheService::class);

        $this->storeKit2Service = new StoreKit2Service(
            $mockJwsService,
            $mockAppStoreServerApiService,
            $mockCacheService
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function isSandboxEnvironmentFromJws_サンドボックス環境を正しく判定()
    {
        // Setup - サンドボックス環境のJWSトークンをシミュレート
        $sandboxPayload = [
            'transactionId' => 'test-transaction-123',
            'productId' => 'test.product',
            'environment' => 'Sandbox',
            'bundleId' => 'com.test.app'
        ];

        $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'ES256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode($sandboxPayload)), '+/', '-_'), '=');
        $signature = rtrim(strtr(base64_encode('mock_signature'), '+/', '-_'), '=');

        $sandboxJws = "{$header}.{$payload}.{$signature}";

        // Execute
        $result = $this->storeKit2Service->isSandboxEnvironmentFromJws($sandboxJws);

        // Verify
        $this->assertTrue($result, 'サンドボックス環境として判定されるべき');
    }

    #[Test]
    public function isSandboxEnvironmentFromJws_本番環境を正しく判定()
    {
        // Setup - 本番環境のJWSトークンをシミュレート
        $productionPayload = [
            'transactionId' => 'test-transaction-456',
            'productId' => 'test.product',
            'environment' => 'Production',
            'bundleId' => 'com.test.app'
        ];

        $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'ES256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode($productionPayload)), '+/', '-_'), '=');
        $signature = rtrim(strtr(base64_encode('mock_signature'), '+/', '-_'), '=');

        $productionJws = "{$header}.{$payload}.{$signature}";

        // Execute
        $result = $this->storeKit2Service->isSandboxEnvironmentFromJws($productionJws);

        // Verify
        $this->assertFalse($result, '本番環境として判定されるべき');
    }

    #[Test]
    public function isSandboxEnvironmentFromJws_environmentフィールドがない場合は例外()
    {
        // Setup - environmentフィールドがないペイロード
        $payloadWithoutEnv = [
            'transactionId' => 'test-transaction-789',
            'productId' => 'test.product',
            'bundleId' => 'com.test.app'
        ];

        $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'ES256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payload = rtrim(strtr(base64_encode(json_encode($payloadWithoutEnv)), '+/', '-_'), '=');
        $signature = rtrim(strtr(base64_encode('mock_signature'), '+/', '-_'), '=');

        $jwsWithoutEnv = "{$header}.{$payload}.{$signature}";

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('JWS payload environment field is missing or invalid');

        $this->storeKit2Service->isSandboxEnvironmentFromJws($jwsWithoutEnv);
    }

    #[Test]
    public function isSandboxEnvironmentFromJws_不正なJWS形式の場合は例外()
    {
        // Setup
        $invalidJws = 'invalid.jws.format.with.too.many.parts';

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('Invalid JWS format: expected 3 parts separated by dots');

        $this->storeKit2Service->isSandboxEnvironmentFromJws($invalidJws);
    }

    #[Test]
    public function isSandboxEnvironmentFromJws_デコードエラーの場合は例外()
    {
        // Setup - 不正なBase64エンコーディング
        $invalidJws = 'invalid_base64.invalid_base64.invalid_base64';

        // Execute & Verify
        $this->expectException(WpBillingException::class);
        $this->expectExceptionMessage('JWS payload JSON decode failed');

        $this->storeKit2Service->isSandboxEnvironmentFromJws($invalidJws);
    }
}
