<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class MngCacheRepository
{
    private const DEFAULT_TTL_SECONDS = 86400; // 1日

    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * CarbonImmutable::setTestNow()の影響を受けない時刻を取得する
     *
     * デバッグ時間変更機能が有効な場合でも、キャッシュ生成時は実時刻を基準にすることで、
     * 未来時刻でキャッシュが作られて現在時刻でデータが取得できなくなる問題を回避する
     *
     * 本番環境では通常通り現在時刻を返す
     *
     * @return CarbonImmutable
     */
    public function getCacheBaseTime(CarbonImmutable $now): CarbonImmutable
    {
        // 固定時間設定がなかったり、本番環境ならそのまま返す
        if (!CarbonImmutable::hasTestNow() || app()->isProduction()) {
            return $now;
        }

        // 現在時刻のデータが十分含まれる程度の過去の時間にする
        $realNow = new \DateTimeImmutable('now', new \DateTimeZone(config('app.timezone')));
        return CarbonImmutable::createFromTimestamp(
            $realNow->getTimestamp(),
            config('app.timezone'),
        )->subMonths(6);
    }

    /**
     * キャッシュを取得、作成する
     * @param string $cacheKey
     * @param callable $closure
     * @return mixed
     */
    public function getOrCreateCache(string $cacheKey, callable $closure): mixed
    {
        $cachedData = $this->cacheClientManager->getCacheClient()->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $data = $closure();
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $data, self::DEFAULT_TTL_SECONDS);

        return $data;
    }

    /**
     * キャッシュを削除する
     * @param string $cacheKey
     * @return void
     */
    public function deleteCache(string $cacheKey): void
    {
        $this->cacheClientManager->getCacheClient()->del($cacheKey);

        Log::debug('MngCacheRepository: deleteCache', ['cacheKey' => $cacheKey]);
    }
}
