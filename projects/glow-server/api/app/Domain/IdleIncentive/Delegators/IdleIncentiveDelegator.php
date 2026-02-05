<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Delegators;

use App\Domain\IdleIncentive\Repositories\UsrIdleIncentiveRepository;
use App\Domain\IdleIncentive\Services\IdleIncentiveRewardService;
use App\Domain\IdleIncentive\Services\UsrIdleIncentiveService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class IdleIncentiveDelegator
{
    public function __construct(
        private readonly IdleIncentiveRewardService $idleIncentiveRewardService,
        private readonly UsrIdleIncentiveService $usrIdleIncentiveService,
        private readonly UsrIdleIncentiveRepository $usrIdleIncentiveRepository,
    ) {
    }

    public function createUsrIdleIncentive(string $usrUserId, CarbonImmutable $now): void
    {
        $this->usrIdleIncentiveRepository->create($usrUserId, $now);
    }

    public function resetReceiveCount(string $usrUserId, CarbonImmutable $now): void
    {
        $this->usrIdleIncentiveService->resetReceiveCount($usrUserId, $now);
    }

    /**
     * @param string $usrUserId
     * @param Collection<\App\Domain\Item\Entities\ItemIdleBoxRewardExchangeInterface> $itemIdleBoxRewardExchangeList
     * @return Collection<\App\Domain\Item\Entities\ItemIdleBoxRewardExchangeInterface>
     */
    public function calcIdleBoxRewardAmounts(
        string $usrUserId,
        Collection $itemIdleBoxRewardExchangeList,
        CarbonImmutable $now
    ): Collection {
        return $this->idleIncentiveRewardService->calcIdleBoxRewardAmounts(
            $usrUserId,
            $itemIdleBoxRewardExchangeList,
            $now,
        );
    }

    /**
     * @param string $usrUserId
     * @param Collection<int> $minutesList
     * @return Collection<int|string, int> key: 放置時間(分), value: 報酬量
     */
    public function calcCoinRewardAmounts(string $usrUserId, Collection $minutesList, CarbonImmutable $now): Collection
    {
        return $this->idleIncentiveRewardService->calcCoinRewardAmounts($usrUserId, $minutesList, $now);
    }

    /**
     * 放置開始日時を現在日時でセットする
     */
    public function setIdleStartedAtNow(
        string $usrUserId,
        CarbonImmutable $now
    ): void {
        $this->usrIdleIncentiveService->setIdleStartedAtNow($usrUserId, $now);
    }

    public function updateRewardMstStageId(
        string $usrUserId,
        string $mstStageId,
        CarbonImmutable $now
    ): void {
        $this->usrIdleIncentiveService->updateRewardMstStageId($usrUserId, $mstStageId, $now);
    }
}
