<?php

declare(strict_types=1);

namespace App\Domain\Item\Delegators;

use App\Domain\Item\Repositories\UsrItemRepository;
use App\Domain\Item\Services\ItemIdleBoxService;
use App\Domain\Item\Services\UsrItemService;
use App\Domain\Resource\Entities\LogTriggers\LogTrigger;
use App\Domain\Resource\Usr\Entities\UsrItemEntity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ItemDelegator
{
    public function __construct(
        private UsrItemService $usrItemService,
        private UsrItemRepository $usrItemRepository,
        private ItemIdleBoxService $itemIdleBoxService,
    ) {
    }

    public function validateItemAmount(string $usrUserId, string $mstItemId, int $requiredAmount): void
    {
        $this->usrItemService->validateItemAmount($usrUserId, $mstItemId, $requiredAmount);
    }

    public function addItemByRewards(
        string $usrUserId,
        Collection $rewards,
        CarbonImmutable $now,
    ): void {
        $this->usrItemService->addItemByRewards($usrUserId, $rewards, $now);
    }

    public function useItemByMstItemId(
        string $usrUserId,
        string $mstItemId,
        int $useNum,
        LogTrigger $logTrigger,
    ): void {
        $this->usrItemService->consumeItem($usrUserId, $mstItemId, $useNum, $logTrigger);
    }

    public function useItemByMstItemIds(
        string $usrUserId,
        Collection $consumeAmountByMstItemId,
        LogTrigger $logTrigger,
    ): void {
        $this->usrItemService->consumeItems($usrUserId, $consumeAmountByMstItemId, $logTrigger);
    }

    public function getUsrItemByMstItemId(string $usrUserId, string $mstItemId): ?UsrItemEntity
    {
        return $this->usrItemRepository->getByMstItemId($usrUserId, $mstItemId)?->toEntity();
    }

    public function getListByMstItemIds(string $usrUserId, Collection $mstItemIds): Collection
    {
        return $this->usrItemRepository->getListByMstItemIds($usrUserId, $mstItemIds);
    }

    /**
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     * @param CarbonImmutable $now
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    public function convertIdleBoxToRealResources(
        string $usrUserId,
        Collection $rewards,
        CarbonImmutable $now
    ): Collection {
        return $this->itemIdleBoxService->convertIdleBoxToRealResources($usrUserId, $rewards, $now);
    }
}
