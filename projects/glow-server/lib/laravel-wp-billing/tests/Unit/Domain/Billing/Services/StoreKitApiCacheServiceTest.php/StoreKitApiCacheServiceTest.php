<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Services\StoreKitApiCacheService;

/**
 * StoreKitApiCacheService のテスト（Redis版）
 */
class StoreKitApiCacheServiceTest extends TestCase
{
    private StoreKitApiCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(StoreKitApiCacheService::class);
        
        // テスト前にキャッシュをクリア
        Cache::flush();
    }

    protected function tearDown(): void
    {
        // テスト後にキャッシュをクリア
        Cache::flush();
        parent::tearDown();
    }

    #[Test]
    public function findValidByJwsHash_有効なキャッシュを取得()
    {
        // Setup
        $jwsHash = 'test_hash_123';
        $apiResponse = ['transactionId' => '123', 'environment' => 'production'];
        
        $this->service->store($jwsHash, '123', 'production', $apiResponse, 60);

        // Execute
        $result = $this->service->findValidByJwsHash($jwsHash);

        // Verify
        $this->assertNotNull($result, '有効なキャッシュが取得される');
        $this->assertEquals($jwsHash, $result['jws_hash']);
        $this->assertEquals($apiResponse, $result['api_response']);
        $this->assertEquals('123', $result['transaction_id']);
        $this->assertEquals('production', $result['environment']);
    }

    #[Test]
    public function findValidByJwsHash_存在しないキャッシュはnullを返す()
    {
        // Setup
        $jwsHash = 'non_existent_hash';

        // Execute
        $result = $this->service->findValidByJwsHash($jwsHash);

        // Verify
        $this->assertNull($result, '存在しないキャッシュはnullが返される');
    }

    #[Test]
    public function store_新規キャッシュが正常に保存される()
    {
        // Setup
        $jwsHash = 'new_hash_123';
        $transactionId = 'transaction_456';
        $environment = 'sandbox';
        $apiResponse = ['transactionId' => $transactionId, 'environment' => $environment];
        $cacheTtl = 30; // 30分

        // Execute
        $result = $this->service->store($jwsHash, $transactionId, $environment, $apiResponse, $cacheTtl);

        // Verify
        $this->assertIsArray($result);
        $this->assertEquals($jwsHash, $result['jws_hash']);
        $this->assertEquals($transactionId, $result['transaction_id']);
        $this->assertEquals($environment, $result['environment']);
        $this->assertEquals($apiResponse, $result['api_response']);
        $this->assertArrayHasKey('cached_at', $result);
        
        // キャッシュから取得して確認
        $cachedData = $this->service->findValidByJwsHash($jwsHash);
        $this->assertNotNull($cachedData);
        $this->assertEquals($apiResponse, $cachedData['api_response']);
    }

    #[Test]
    public function store_既存キャッシュが上書きされる()
    {
        // Setup
        $jwsHash = 'existing_hash_123';
        
        // 既存データを作成
        $this->service->store($jwsHash, 'old_transaction', 'production', ['old' => 'data'], 60);

        $newTransactionId = 'new_transaction_789';
        $newEnvironment = 'sandbox';
        $newApiResponse = ['new' => 'data'];

        // Exercise
        $result = $this->service->store($jwsHash, $newTransactionId, $newEnvironment, $newApiResponse);

        // Verify
        $this->assertEquals($newTransactionId, $result['transaction_id']);
        $this->assertEquals($newEnvironment, $result['environment']);
        $this->assertEquals($newApiResponse, $result['api_response']);
        
        // キャッシュから取得して上書きされていることを確認
        $cachedData = $this->service->findValidByJwsHash($jwsHash);
        $this->assertEquals($newApiResponse, $cachedData['api_response']);
    }

    #[Test]
    public function deleteByJwsHash_指定されたキャッシュが削除される()
    {
        // Setup
        $targetHash = 'target_hash';
        $otherHash = 'other_hash';
        
        $this->service->store($targetHash, '123', 'production', ['target' => 'data'], 60);
        $this->service->store($otherHash, '456', 'sandbox', ['other' => 'data'], 60);

        // Exercise
        $result = $this->service->deleteByJwsHash($targetHash);

        // Verify
        $this->assertTrue($result, 'キャッシュの削除が成功する');
        $this->assertNull($this->service->findValidByJwsHash($targetHash), 'ターゲットキャッシュが削除される');
        $this->assertNotNull($this->service->findValidByJwsHash($otherHash), '他のキャッシュは残る');
    }

    #[Test]
    public function deleteByJwsHash_存在しないキャッシュの削除はfalseを返す()
    {
        // Setup
        $nonExistentHash = 'non_existent_hash';

        // Exercise
        $result = $this->service->deleteByJwsHash($nonExistentHash);

        // Verify
        $this->assertFalse($result, '存在しないキャッシュの削除はfalseを返す');
    }
}
