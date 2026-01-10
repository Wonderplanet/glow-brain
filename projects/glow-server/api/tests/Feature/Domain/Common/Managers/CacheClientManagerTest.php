<?php

namespace Feature\Domain\Common\Managers;

use App\Domain\AdventBattle\Entities\AdventBattleRankingItem;
use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Managers\Cache\RedisCacheClient;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CacheClientManagerTest extends TestCase
{
    private CacheClientManager $cacheClientManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheClientManager = app(CacheClientManager::class);
    }

    public function testGetCacheClient_RedisCacheClientインスタンスが取得できる()
    {
        // Setup

        // Exercise
        $client = $this->cacheClientManager->getCacheClient();

        // Verify
        $this->assertInstanceOf(RedisCacheClient::class, $client);
    }

    public function testZAdd_スコアを追加できる()
    {
        // Setup
        $key = 'test';
        $members = ['member1' => 1, 'member2' => 2];

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->zAdd($key, $members);

        // Verify
        $this->assertSame(2, $result);

        foreach ($members as $member => $score) {
            $this->assertSame($score, (int)Redis::connection()->zscore($key, $member));
        }
    }

    public function testZIncrBy_スコアを加算できる()
    {
        // Setup
        $key = 'test';
        $member = 'member1';
        Redis::connection()->zadd($key, ['member1' => 1]);

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->zIncrBy($key, 2, $member);

        // Verify
        $this->assertSame(3, (int)$result);
        $this->assertSame(3, (int)Redis::connection()->zscore($key, $member));
    }

    public function testZRem_スコアを削除できる()
    {
        // Setup
        $key = 'test';
        $members = ['member1'];
        Redis::connection()->zadd($key, ['member1' => 1, 'member2' => 2]);

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->zRem($key, $members);

        // Verify
        $this->assertSame(1, $result);
        $this->assertSame(1, Redis::connection()->zcard($key));
    }

    public function testZCount_スコアの範囲内のメンバー数を取得できる()
    {
        // Setup
        $key = 'test';
        Redis::connection()->zadd($key, ['member1' => 1, 'member2' => 2, 'member3' => 3, 'member4' => 4]);

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->zCount($key, 2, 3);

        // Verify
        $this->assertSame(2, $result);
    }

    public function testZRevRange_範囲内のメンバーをスコアの降順に取得できる()
    {
        // Setup
        $key = 'test';
        Redis::connection()->zadd($key, ['member1' => 1, 'member2' => 2, 'member3' => 3, 'member4' => 4]);

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->zRevRange($key, 0, 2, true);

        // Verify
        $this->assertSame(['member4' => 4.0, 'member3' => 3.0, 'member2' => 2.0], $result);
    }

    public function testZRevRangeByScore_スコアの範囲内のメンバーを降順に取得できる()
    {
        // Setup
        $key = 'test';
        Redis::connection()->zadd($key, ['member1' => 1, 'member2' => 2, 'member3' => 3, 'member4' => 4]);

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->zRevRangeByScore($key, 3, 2, true);

        // Verify
        $this->assertSame(['member3' => 3.0, 'member2' => 2.0], $result);
    }

    public function testZUnionStore_和集合を取得できる()
    {
        // Setup
        $destination = 'destination';
        $keys = ['key1', 'key2'];
        Redis::connection()->zadd('key1', ['member1' => 1, 'member2' => 2, 'member3' => 3]);
        Redis::connection()->zadd('key2', ['member2' => -1]);

        $weights = [1, 1];

        // Exercise
        // key1とkey2のスコアの小さい値を採用して和集合をdestinationに格納
        $result = $this->cacheClientManager->getCacheClient()->zUnionStore($destination, $keys, $weights, 'min');

        // Verify
        $this->assertSame(3, $result);
        $this->assertSame(['member2' => -1.0, 'member1' => 1.0, 'member3' => 3.0], Redis::connection()->zrange($destination, 0, -1, true));
    }

    public function testExpire_キャッシュの有効期限を設定できる()
    {
        // Setup
        $key = 'test';
        Redis::connection()->set($key, 'value');

        $ttl = 10;

        // Exercise
        $this->cacheClientManager->getCacheClient()->expire($key, $ttl);

        // Verify
        $this->assertThat(Redis::connection()->ttl($key), $this->lessThanOrEqual($ttl));
    }

    public static function params_testSetAndGet_キャッシュを取得できる()
    {
        return [
            '数値' => ['value' => 100],
            '文字列' => ['value' => 'test'],
            '配列' => ['value' => ['key' => 'value']],
            'コレクション' => ['value' => collect(['key' => 'value'])],
            'クラスオブジェクト' => ['value' => new AdventBattleRankingItem('myId', 1, 'test', 'id', 'id', 100, 1500)],
        ];
    }

    #[DataProvider('params_testSetAndGet_キャッシュを取得できる')]
    public function testSetAndGet_キャッシュを取得できる(mixed $value)
    {
        $this->cacheClientManager->getCacheClient()->set('test', $value);
        $actual = $this->cacheClientManager->getCacheClient()->get('test');
        $this->assertEquals($value, $actual);
    }

    public function testIncrBy_キャッシュの値をインクリメントできる()
    {
        // Setup
        $key = 'test';
        Redis::connection()->set($key, 1);

        // Exercise
        $result = $this->cacheClientManager->getCacheClient()->incrBy($key, 2);

        // Verify
        $this->assertSame(3, $result);
        $this->assertSame(3, (int)Redis::connection()->get($key));
    }
}
