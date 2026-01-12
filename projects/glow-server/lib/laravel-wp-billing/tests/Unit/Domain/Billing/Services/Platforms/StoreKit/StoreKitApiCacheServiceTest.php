<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKitApiCacheService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Domain\Billing\Services\StoreKitApiCacheService as StoreKitApiCacheStorageService;

/**
 * StoreKitApiCacheService のテスト
 */
class StoreKitApiCacheServiceTest extends TestCase
{
    private $mockCacheRepository;
    private $mockJwsService;
    private StoreKitApiCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCacheRepository = Mockery::mock(StoreKitApiCacheStorageService::class);
        $this->mockJwsService = Mockery::mock(JwsService::class);

        $this->cacheService = new StoreKitApiCacheService(
            $this->mockCacheRepository,
            $this->mockJwsService
        );
    }

    #[Test]
    public function getCachedResponse_キャッシュヒット時に正常なレスポンスを返す()
    {
        // Setup
        $jws = 'test.jws.token';
        $jwsHash = 'test_hash';
        $expectedResponse = ['transactionId' => '123', 'environment' => 'production'];
        
        $cacheData = [
            'jws_hash' => $jwsHash,
            'transaction_id' => '123',
            'environment' => 'production',
            'api_response' => $expectedResponse,
            'cached_at' => now()->toISOString(),
        ];

        $this->mockJwsService
            ->shouldReceive('calculateJwsHash')
            ->once()
            ->with($jws)
            ->andReturn($jwsHash);

        $this->mockCacheRepository
            ->shouldReceive('findValidByJwsHash')
            ->once()
            ->with($jwsHash)
            ->andReturn($cacheData);

        // Execute
        $result = $this->cacheService->getCachedResponse($jws);

        // Verify
        $this->assertEquals($expectedResponse, $result, 'キャッシュされたレスポンスが返される');
    }

    #[Test]
    public function getCachedResponse_キャッシュミス時にnullを返す()
    {
        // Setup
        $jws = 'test.jws.token';
        $jwsHash = 'test_hash';

        $this->mockJwsService
            ->shouldReceive('calculateJwsHash')
            ->once()
            ->with($jws)
            ->andReturn($jwsHash);

        $this->mockCacheRepository
            ->shouldReceive('findValidByJwsHash')
            ->once()
            ->with($jwsHash)
            ->andReturn(null);

        // Execute
        $result = $this->cacheService->getCachedResponse($jws);

        // Verify
        $this->assertNull($result, 'キャッシュミス時はnullが返される');
    }

    #[Test]
    public function cacheResponse_正常にキャッシュに保存される()
    {
        // Setup
        $jws = 'test.jws.token';
        $jwsHash = 'test_hash';
        $transactionId = '123456';
        $environment = 'production';
        $apiResponse = ['transactionId' => $transactionId, 'environment' => $environment];
        $cacheTtl = 60;

        $this->mockJwsService
            ->shouldReceive('calculateJwsHash')
            ->once()
            ->with($jws)
            ->andReturn($jwsHash);

        $this->mockCacheRepository
            ->shouldReceive('store')
            ->once()
            ->with($jwsHash, $transactionId, $environment, $apiResponse, $cacheTtl)
            ->andReturn([
                'jws_hash' => $jwsHash,
                'transaction_id' => $transactionId,
                'environment' => $environment,
                'api_response' => $apiResponse,
                'cached_at' => now()->toISOString(),
            ]);

        // Execute
        $this->cacheService->cacheResponse($jws, $transactionId, $environment, $apiResponse, $cacheTtl);

        // Verify
        // Mockery::mock で期待値の検証が完了
        $this->assertTrue(true, 'モックの期待値が満たされた');
    }

    #[Test]
    public function invalidateCache_正常にキャッシュが削除される()
    {
        // Setup
        $jws = 'test.jws.token';
        $jwsHash = 'test_hash';

        $this->mockJwsService
            ->shouldReceive('calculateJwsHash')
            ->once()
            ->with($jws)
            ->andReturn($jwsHash);

        $this->mockCacheRepository
            ->shouldReceive('deleteByJwsHash')
            ->once()
            ->with($jwsHash)
            ->andReturn(true);

        // Execute
        $result = $this->cacheService->invalidateCache($jws);

        // Verify
        $this->assertTrue($result, 'キャッシュの削除が成功する');
    }
}
