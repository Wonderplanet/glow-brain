<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\LogTriggers\JoinLogTrigger;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Mst\Entities\Contracts\IMstUnitRankUpEntity;
use App\Domain\Resource\Mst\Entities\MstItemEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRankUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitSpecificRankUpRepository;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\LogUnitRankUpRepository;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use Carbon\CarbonImmutable;

class UnitRankUpService
{
    public function __construct(
        // MstRepository
        private MstUnitRankUpRepository $mstUnitRankUpRepository,
        private MstUnitSpecificRankUpRepository $mstUnitSpecificRankUpRepository,
        private MstUnitRepository $mstUnitRepository,
        private MstItemRepository $mstItemRepository,
        // UsrRepository
        private UsrUnitRepository $usrUnitRepository,
        private UnitMissionTriggerService $unitMissionTriggerService,
        // LogRepository
        private LogUnitRankUpRepository $logUnitRankUpRepository,
        // Delegator
        private ItemDelegator $itemDelegator,
    ) {
    }

    /**
     * @param string $usrUnitId
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return UsrUnitInterface|null
     * @throws GameException
     */
    public function rankUp(string $usrUnitId, string $usrUserId, CarbonImmutable $now): ?UsrUnitInterface
    {
        $usrUnit = $this->usrUnitRepository->getById($usrUnitId, $usrUserId);
        $mstUnit = $this->mstUnitRepository->getByIdWithError($usrUnit->getMstUnitId());

        $beforeRank = $usrUnit->getRank();
        $targetRank = $beforeRank + 1;

        if ($mstUnit->hasSpecificRankUp()) {
            $mstUnitRankUp = $this->mstUnitSpecificRankUpRepository->getByMstUnitIdAndRank(
                $mstUnit->getId(),
                $targetRank,
                true
            );
        } else {
            $mstUnitRankUp = $this->mstUnitRankUpRepository->getByUnitLabelAndRank(
                $mstUnit->getUnitLabel(),
                $targetRank,
                true
            );
        }
        /** @var \App\Domain\Resource\Mst\Entities\Contracts\IMstUnitRankUpEntity $mstUnitRankUp */

        if ($mstUnitRankUp->getRequireLevel() !== $usrUnit->getLevel()) {
            throw new GameException(ErrorCode::UNIT_INSUFFICIENT_LEVEL, "Unit level is insufficient.");
        }

        // ユニットランクアップログ保存
        $logUnitRankUp = $this->logUnitRankUpRepository->create(
            $usrUserId,
            $mstUnit->getId(),
            $beforeRank,
            $targetRank,
        );
        $joinLogTrigger = new JoinLogTrigger($logUnitRankUp);

        // リミテッドメモリー消費
        $mstItem = $this->mstItemRepository->getRankUpMaterialByColor($mstUnit->getColor(), $now, true);
        $this->itemDelegator->useItemByMstItemId(
            $usrUserId,
            $mstItem->getId(),
            $mstUnitRankUp->getAmount(),
            $joinLogTrigger,
        );

        // メモリーフラグメント消費
        $this->consumeMemoryFragment($usrUserId, $now, $mstUnitRankUp, $joinLogTrigger);

        // キャラ個別メモリー消費
        $this->consumeUnitMemory($usrUserId, $now, $mstUnitRankUp, $joinLogTrigger);

        $usrUnit->setRank($targetRank);
        $this->usrUnitRepository->syncModel($usrUnit);

        // ミッショントリガー送信
        $this->unitMissionTriggerService->sendRankUpTrigger($usrUnit);

        return $usrUnit;
    }

    /**
     * メモリーフラグメントが必要な場合の消費処理
     */
    private function consumeMemoryFragment(
        string $usrUserId,
        CarbonImmutable $now,
        IMstUnitRankUpEntity $mstUnitRankUp,
        JoinLogTrigger $joinLogTrigger
    ): void {
        $memoryFragmentMstItems = $this->mstItemRepository->getRankUpMemoryFragments($now)
            ->keyBy(function (MstItemEntity $mstItem) {
                return $mstItem->getRarity();
            });

        $srAmount = $mstUnitRankUp->getSrMemoryFragmentAmount();
        $ssrAmount = $mstUnitRankUp->getSsrMemoryFragmentAmount();
        $urAmount = $mstUnitRankUp->getUrMemoryFragmentAmount();

        $consumeAmountByMstItemId = collect();
        if ($srAmount > 0 && $memoryFragmentMstItems->has(RarityType::SR->value)) {
            $consumeAmountByMstItemId->put(
                $memoryFragmentMstItems->get(RarityType::SR->value)->getId(),
                $srAmount,
            );
        }
        if ($ssrAmount > 0 && $memoryFragmentMstItems->has(RarityType::SSR->value)) {
            $consumeAmountByMstItemId->put(
                $memoryFragmentMstItems->get(RarityType::SSR->value)->getId(),
                $ssrAmount,
            );
        }
        if ($urAmount > 0 && $memoryFragmentMstItems->has(RarityType::UR->value)) {
            $consumeAmountByMstItemId->put(
                $memoryFragmentMstItems->get(RarityType::UR->value)->getId(),
                $urAmount,
            );
        }
        if ($consumeAmountByMstItemId->isEmpty()) {
            return;
        }

        $this->itemDelegator->useItemByMstItemIds(
            $usrUserId,
            $consumeAmountByMstItemId,
            $joinLogTrigger,
        );
    }

    /**
     * キャラ個別メモリーが必要な場合の消費処理
     *
     * キャラ個別メモリーアイテムのデータ(mst_items)
     *   type = RankUpMemoryFragment
     *   effect_value = mst_units.id
     */
    private function consumeUnitMemory(
        string $usrUserId,
        CarbonImmutable $now,
        IMstUnitRankUpEntity $mstUnitRankUp,
        JoinLogTrigger $joinLogTrigger
    ): void {
        if (! $mstUnitRankUp->needUnitMemory()) {
            return;
        }

        $mstItem = $this->mstItemRepository->getUnitMemoryByMstUnitId(
            $mstUnitRankUp->getMstUnitId(),
            $now,
            true,
        );

        $this->itemDelegator->useItemByMstItemId(
            $usrUserId,
            $mstItem->getId(),
            $mstUnitRankUp->getUnitMemoryAmount(),
            $joinLogTrigger,
        );
    }
}
