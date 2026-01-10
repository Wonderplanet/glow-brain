<?php

declare(strict_types=1);

namespace App\Domain\Shop\Delegators;

use App\Domain\Resource\Usr\Entities\UsrConditionPackEntity;
use App\Domain\Shop\Services\AppShopService;
use App\Domain\Shop\Services\ShopService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;

class ShopDelegator
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly AppShopService $appShopService,
    ) {
    }

    public function releaseConditionPacks(string $usrUserId, int $level, CarbonImmutable $now): void
    {
        $this->shopService->releaseConditionPacks($usrUserId, $level, $now);
    }

    /**
     * @param string $usrUserId
     * @param string $mstStageId
     * @param CarbonImmutable $now
     * @return Collection<UsrConditionPackEntity>
     */
    public function releaseStageClearPack(string $usrUserId, string $mstStageId, CarbonImmutable $now): Collection
    {
        return $this->shopService->releaseStageClearPack($usrUserId, $mstStageId, $now)->map->toEntity();
    }

    /**
     * @param string $usrUserId
     * @param int $level
     * @param CarbonImmutable $now
     * @return Collection<UsrConditionPackEntity>
     */
    public function releaseUserLevelPack(string $usrUserId, int $level, CarbonImmutable $now): Collection
    {
        return $this->shopService->releaseUserLevelPack($usrUserId, $level, $now)->map->toEntity();
    }

    /**
     * 課金基盤周りの処理
     */

    public function createUsrStoreInfo(string $usrUserId, CarbonImmutable $now): UsrStoreInfoEntity
    {
        return $this->appShopService->createUsrStoreInfo($usrUserId, $now);
    }

    public function getUsrTradePacks(string $usrUserId, CarbonImmutable $now): Collection
    {
        return $this->shopService->getUsrTradePackList($usrUserId, $now);
    }
}
