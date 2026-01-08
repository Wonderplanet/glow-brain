<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Pvp\Services\PvpCacheService as BasePvpCacheService;

class PvpCacheService extends BasePvpCacheService
{

    public function __construct(
        private CacheClientManager $cacheClientManager
    ) {
        parent::__construct($cacheClientManager);
    }

    /**
     * ランクマッチのランキングにスコアを追加
     * @param string $sysPvpSeasonId
     * @param array  $data
     * @return void
     */
    public function addRankingScoreAll(string $sysPvpSeasonId, array $data): void
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->zadd($cacheKey, $data);
    }

    public function removeRanking(string $sysPvpSeasonId, string $usrUserId): void
    {
        $cacheKey = CacheKeyUtil::getPvpRankingKey($sysPvpSeasonId);
        $this->cacheClientManager->getCacheClient()->zRem($cacheKey, [$usrUserId]);
    }
}
