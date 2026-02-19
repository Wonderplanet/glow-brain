<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Tests\Traits\ReflectionTrait;

/**
 * JwsService のテスト
 */
class JwsServiceTest extends TestCase
{
    use ReflectionTrait;

    private JwsService $jwsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwsService = new JwsService();
    }

    /**
     * parseJwsHeader_正常なJWSヘッダーのパース
     */
    #[Test]
    public function parseJwsHeader_正常なJWSヘッダーのパース()
    {
        // Setup
        // 実際のStoreKit2のJWSヘッダー構造をシミュレート
        $header = json_encode([
            'alg' => 'ES256',
            'typ' => 'JWT',
            'x5c' => ['MIICertificate...']
        ]);
        $encodedHeader = base64_encode($header);
        $jws = $encodedHeader . '.payload.signature';

        // Execute
        $result = $this->callMethod($this->jwsService, 'parseJwsHeader', [$jws]);

        // Verify
        $this->assertEquals('ES256', $result['alg']);
        $this->assertEquals('JWT', $result['typ']);
        $this->assertIsArray($result['x5c']);
    }

    /**
     * parseJwsHeader_不正なJWS形式でエラー
     */
    #[Test]
    public function parseJwsHeader_不正なJWS形式でエラー()
    {
        // Setup
        $invalidJws = 'invalid.format';

        // Execute & Verify
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JWS format error');

        $this->callMethod($this->jwsService, 'parseJwsHeader', [$invalidJws]);
    }

    /**
     * decodePayloadOnly_ペイロード部分のみをデコード
     */
    #[Test]
    public function decodePayloadOnly_ペイロード部分のみをデコード()
    {
        // Setup
        $payload = json_encode([
            'transactionId' => 'test-123',
            'productId' => 'premium',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
        ]);
        $encodedPayload = base64_encode($payload);
        $jws = 'header.' . $encodedPayload . '.signature';

        // Execute
        $result = $this->jwsService->decodePayloadOnly($jws);

        // Verify
        $this->assertEquals('test-123', $result['transactionId']);
        $this->assertEquals('premium', $result['productId']);
        $this->assertEquals(AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX, $result['environment']);
    }

    /**
     * decodePayloadOnly_不正なJWS形式でエラー
     */
    #[Test]
    public function decodePayloadOnly_不正なJWS形式でエラー()
    {
        // Setup
        $invalidJws = 'onlyonepart';

        // Execute & Verify
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JWS format error');

        $this->jwsService->decodePayloadOnly($invalidJws);
    }

    /**
     * decodeStoreServerJws_テスト環境では署名検証をスキップ
     */
    #[Test]
    public function decodeStoreServerJws_テスト環境では署名検証をスキップ()
    {
        // Setup
        // Apple特有の検証をバイパスするため、verifyの返り値に必要なキーを含める
        $jws = 'header.payload.signature';
        $mock = \Mockery::mock(JwsService::class)->makePartial();
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('verify')->once()->with($jws)->andReturn([
            'transactionId' => 'test-456',
            'productId' => 'basic_plan',
            'environment' => AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX,
            'subject' => [
                'OU' => 'Apple',
                'CN' => 'Apple Inc',
            ],
            'extensions' => [
                'extendedKeyUsage' => '1.2.840.113635.100.6.11.1',
            ],
        ]);

        // Execute
        $result = $this->callMethod($mock, 'decodeStoreServerJws', [$jws]);

        // Verify
        $this->assertIsArray($result);
        $this->assertArrayHasKey('transactionId', $result);
        $this->assertEquals('test-456', $result['transactionId']);
        $this->assertEquals('basic_plan', $result['productId']);
        $this->assertEquals(AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX, $result['environment']);
    }

    /**
     * convertX5cToPem_証明書配列をPEM形式に変換
     */
    #[Test]
    public function convertX5cToPem_証明書配列をPEM形式に変換()
    {
        // Setup
        // テスト用のダミー証明書データ（実際のApple証明書ではない）
        $x5c = [
            'MIIBkjCB+wIBATAKBggqhkjOPQQDAjASMRAwDgYDVQQDEwdUZXN0IENBMCIYDzIwMjMwMTAxMDAwMDAwWhgPMjAyNDAxMDEwMDAwMDBaMBIxEDAOBgNVBAMTB1Rlc3QgSUQwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATExample',
            'MIIBkjCB+wIBATAKBggqhkjOPQQDAjASMRAwDgYDVQQDEwdUZXN0IENBMCIYDzIwMjMwMTAxMDAwMDAwWhgPMjAyNDAxMDEwMDAwMDBaMBIxEDAOBgNVBAMTB1Rlc3QgSUQwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATAnother'
        ];

        // Execute
        $result = $this->callMethod($this->jwsService, 'convertX5cToPem', [$x5c]);

        // Verify
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $result[0]);
        $this->assertStringContainsString('-----END CERTIFICATE-----', $result[0]);
        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $result[1]);
        $this->assertStringContainsString('-----END CERTIFICATE-----', $result[1]);
    }

    /**
     * certificateManager_を使用して証明書を取得
     */
    #[Test]
    public function certificateManager_を使用して証明書を取得()
    {
        // Execute - CertificateManagerを通じて証明書を取得
        $reflectionClass = new \ReflectionClass($this->jwsService);
        $certificateManagerProperty = $reflectionClass->getProperty('certificateManager');
        $certificateManagerProperty->setAccessible(true);
        $certificateManager = $certificateManagerProperty->getValue($this->jwsService);

        $result = $certificateManager->getAppleRootCaPem('g3');

        // Verify
        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $result);
        $this->assertStringContainsString('-----END CERTIFICATE-----', $result);
    }

    /**
     * certificateManager_複数ルート証明書を取得できること
     */
    #[Test]
    public function certificateManager_複数ルート証明書を取得できること()
    {
        // Execute - CertificateManagerを通じてG2/G3両方の証明書を取得
        $reflectionClass = new \ReflectionClass($this->jwsService);
        $certificateManagerProperty = $reflectionClass->getProperty('certificateManager');
        $certificateManagerProperty->setAccessible(true);
        $certificateManager = $certificateManagerProperty->getValue($this->jwsService);

        $results = $certificateManager->getAllAppleRootCaPems();

        // Verify
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results), 'G2/G3両方の証明書が取得できること');
        foreach ($results as $pem) {
            $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $pem);
            $this->assertStringContainsString('-----END CERTIFICATE-----', $pem);
        }
    }

    /**
     * calculateJwsHash_同一JWSトークンから同じハッシュ値を生成
     */
    #[Test]
    public function calculateJwsHash_同一JWSトークンから同じハッシュ値を生成()
    {
        // Setup
        $jws = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoidGVzdCJ9.signature';

        // Execute
        $hash1 = $this->jwsService->calculateJwsHash($jws);
        $hash2 = $this->jwsService->calculateJwsHash($jws);

        // Verify
        $this->assertIsString($hash1, 'ハッシュ値は文字列である');
        $this->assertEquals(64, strlen($hash1), 'SHA256ハッシュは64文字である');
        $this->assertEquals($hash1, $hash2, '同一JWSから同じハッシュ値が生成される');
    }

    /**
     * calculateJwsHash_異なるJWSトークンから異なるハッシュ値を生成
     */
    #[Test]
    public function calculateJwsHash_異なるJWSトークンから異なるハッシュ値を生成()
    {
        // Setup
        $jws1 = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoidGVzdDEifQ.signature1';
        $jws2 = 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0ZXN0IjoidGVzdDIifQ.signature2';

        // Execute
        $hash1 = $this->jwsService->calculateJwsHash($jws1);
        $hash2 = $this->jwsService->calculateJwsHash($jws2);

        // Verify
        $this->assertNotEquals($hash1, $hash2, '異なるJWSから異なるハッシュ値が生成される');
    }

    /**
     * verify_実際のApple署名JWSの検証
     * @group wp_billing_appstore
     */
    #[Test]
    public function verify_実際のApple署名JWSの検証()
    {
        // Setup
        // この tests は実際のApple署名済みJWSでのみ実行可能
        $this->markTestSkipped('実際のApple署名JWS検証テストはスキップ（外部API/証明書依存のため、統合テストで対応）');
    }
}
