<?php

declare(strict_types=1);

namespace App\Domain\Common\Managers\Cache;

use Illuminate\Support\Facades\Redis;

class RedisCacheClient implements CacheClientInterface
{
    /**
     * @inheritDoc
     */
    public function zAdd(string $key, array $members): int
    {
        return Redis::connection()->zadd($key, $members);
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, int $increment, string $member): float
    {
        return Redis::connection()->zincrby($key, $increment, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRem(string $key, array $members): int
    {
        return Redis::connection()->zrem($key, ...$members);
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int|string $start, int|string $end): int
    {
        return Redis::connection()->zcount($key, $start, $end);
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member): float|bool
    {
        return Redis::connection()->zscore($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int|string $start, int|string $end, bool $withScores): array
    {
        return Redis::connection()->zrevrange($key, $start, $end, $withScores);
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScore(
        string $key,
        int|string $max,
        int|string $min,
        bool $withScores,
        int $limit = 0
    ): array|false {
        $option = [];
        if ($withScores) {
            $option['withscores'] = true;
        }
        if ($limit) {
            $option['limit'] = [0, $limit];
        }
        return Redis::connection()->zrevrangebyscore($key, $max, $min, $option);
    }

    /**
     * @inheritDoc
     */
    public function zUnionStore(string $destination, array $keys, array $weights, string $aggregateFunction): int
    {
        $option = [
            'weights' => $weights,
            'aggregate' => $aggregateFunction,
        ];
        return Redis::connection()->zunionstore($destination, $keys, $option);
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl): void
    {
        Redis::connection()->expire($key, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key): int
    {
        return Redis::connection()->ttl($key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, $ttl = null): void
    {
        /** @var \Illuminate\Redis\Connections\PhpRedisConnection $connection */
        $connection = Redis::connection();
        if (is_null($ttl)) {
            $connection->set($key, serialize($value));
        } else {
            $connection->set($key, serialize($value), 'EX', $ttl);
        }
    }

    /**
     * @inheritDoc
     */
    public function setIfNotExists(string $key, mixed $value, $ttl = null): bool
    {
        /** @var \Illuminate\Redis\Connections\PhpRedisConnection $connection */
        $connection = Redis::connection();
        if (is_null($ttl)) {
            return $connection->set($key, serialize($value), 'NX') !== false;
        } else {
            return $connection->set($key, serialize($value), 'EX', $ttl, 'NX') !== false;
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $value = Redis::connection()->get($key);
        if ($value === null) {
            return null;
        }
        try {
            return unserialize($value);
        } catch (\Throwable $e) {
            // シリアライズされていない場合はそのまま返す
            // 主にテストでRedis::connection()->set()などを直接呼び出してセットし取得はRedisCacheClientのgetを取得するときなど
            return $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $increment): int
    {
        return Redis::connection()->incrby($key, $increment);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $key): bool
    {
        return Redis::connection()->exists($key) === 1;
    }

    /**
     * @inheritDoc
     */
    public function del(string $key): void
    {
        Redis::connection()->del($key);
    }
}
