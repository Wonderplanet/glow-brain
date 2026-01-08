<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Services;

use App\Domain\Common\Managers\Cache\CacheClientManager;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Gacha\Constants\GachaConstants;
use App\Domain\Gacha\Entities\GachaHistory;
use Illuminate\Support\Collection;

class GachaCacheService
{
    public function __construct(
        private CacheClientManager $cacheClientManager,
    ) {
    }

    /**
     * ガシャ履歴の先頭にデータを追加する
     * @param string $usrUserId
     * @param GachaHistory $gachaHistory
     * @return void
     */
    public function prependGachaHistory(string $usrUserId, GachaHistory $gachaHistory): void
    {
        $cacheKey = CacheKeyUtil::getGachaHistoryKey($usrUserId);
        $gachaHistories = $this->getGachaHistories($usrUserId) ?? collect();
        // 先頭に追加(常に先頭が最新の履歴で末尾が古い履歴)
        $gachaHistories->prepend($gachaHistory);
        // 表示上限を超えた場合は上限までに調整
        if (GachaConstants::GACHA_HISTORY_LIMIT < $gachaHistories->count()) {
            $gachaHistories = $gachaHistories->take(GachaConstants::GACHA_HISTORY_LIMIT);
        }
        // 履歴の期限をttlとして設定する
        $ttl = GachaConstants::HISTORY_DAYS * 24 * 60 * 60;
        $this->cacheClientManager->getCacheClient()->set($cacheKey, $gachaHistories, $ttl);
    }

    /**
     * @param string $usrUserId
     * @return Collection|null
     */
    public function getGachaHistories(string $usrUserId): ?Collection
    {
        $cacheKey = CacheKeyUtil::getGachaHistoryKey($usrUserId);
        return $this->cacheClientManager->getCacheClient()->get($cacheKey);
    }
}
