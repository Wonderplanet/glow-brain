<?php

declare(strict_types=1);

namespace App\Domain\Common\Managers\Cache;

/**
 * CacheClientManager
 *
 * 環境に応じて適切なキャッシュクライアントを管理するクラス。
 * ローカル、テスト環境ではRedis、それ以外の環境ではMomentoを使用する。
 */
class CacheClientManager
{
    private CacheClientInterface $cacheClient;

    public function __construct()
    {
        $env = app()->environment();
        if (str_starts_with($env, 'local')) {
            $this->cacheClient = app()->make(RedisCacheClient::class);
        } else {
            $this->cacheClient = app()->make(MomentoCacheClient::class);
        }
    }

    public function getCacheClient(): CacheClientInterface
    {
        return $this->cacheClient;
    }
}
