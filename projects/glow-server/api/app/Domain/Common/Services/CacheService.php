<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Http\Responses\Data\GachaProbabilityData;

class CacheService
{
    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * ガシャ提供割合データオブジェクトをキャッシュから取得
     * @param string $oprGachaId
     * @return GachaProbabilityData|null
     */
    public function getGachaProbability(string $oprGachaId): ?GachaProbabilityData
    {
        $cacheKey = CacheKeyUtil::getGachaProbabilityKey($oprGachaId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }

    /**
     * ガシャ提供割合データオブジェクトをキャッシュに設定
     * @param string $oprGachaId
     * @param GachaProbabilityData $gachaProbabilityData
     */
    public function setGachaProbability(string $oprGachaId, GachaProbabilityData $gachaProbabilityData): void
    {
        $cacheKey = CacheKeyUtil::getGachaProbabilityKey($oprGachaId);
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $gachaProbabilityData, 60 * 60);
    }

    /**
     * BNIDのアクセストークンAPIから取得したIDキャッシュがあれば取得
     * @param string $code
     * @return string|null
     */
    public function getBnidUserIdFromCache(string $code): ?string
    {
        $key = CacheKeyUtil::getBnidUserIdKey($code);
        return $this->cacheClientManager->getCacheClient()->get($key);
    }

    /**
     * BNIDのアクセストークンAPIから取得したIDをキャッシュに設定
     * @param string $code
     * @param string $bnidUserId
     * @return void
     */
    public function setBnidUserIdToCache(string $code, string $bnidUserId): void
    {
        $key = CacheKeyUtil::getBnidUserIdKey($code);
        $this->cacheClientManager->getCacheClient()->set($key, $bnidUserId, 60 * 60);
    }
}
