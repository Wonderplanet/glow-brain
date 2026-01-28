<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Resource\Entities\LogTriggers\JoinLogTrigger;
use App\Domain\Resource\Mst\Repositories\MstUnitGradeUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\LogUnitGradeUpRepository;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Repositories\UsrUnitSummaryRepository;

readonly class UnitGradeUpService
{
    public function __construct(
        private MstUnitGradeUpRepository $mstUnitGradeUpRepository,
        private UsrUnitSummaryRepository $usrUnitSummaryRepository,
        private MstUnitRepository $mstUnitRepository,
        private UsrUnitRepository $usrUnitRepository,
        private UnitMissionTriggerService $unitMissionTriggerService,
        private LogUnitGradeUpRepository $logUnitGradeUpRepository,
        // Delegator
        private ItemDelegator $itemDelegator,
    ) {
    }

    /**
     * @param string $unitId
     * @param string $usrUserId
     * @return UsrUnitInterface|null
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function gradeUp(string $unitId, string $usrUserId): ?UsrUnitInterface
    {
        $usrUnit = $this->usrUnitRepository->getById($unitId, $usrUserId);
        $mstUnit = $this->mstUnitRepository->getByIdWithError($usrUnit->getMstUnitId());

        $beforeGradeLevel = $usrUnit->getGradeLevel();
        $afterGradeLevel = $beforeGradeLevel + 1;
        $mstUnitGradeUp = $this->mstUnitGradeUpRepository->getByUnitLabelAndGradeLevel(
            $mstUnit->getUnitLabel(),
            $afterGradeLevel,
            true
        );

        $logUnitGradeUp = $this->logUnitGradeUpRepository->create(
            $usrUserId,
            $mstUnit->getId(),
            $beforeGradeLevel,
            $afterGradeLevel,
        );

        $this->itemDelegator->useItemByMstItemId(
            $usrUserId,
            $mstUnit->getFragmentMstItemId(),
            $mstUnitGradeUp->getRequireAmount(),
            new JoinLogTrigger($logUnitGradeUp),
        );

        $usrUnit->incrementGradeLevel();

        $this->usrUnitRepository->syncModel($usrUnit);
        // ユーザーのUnitSummaryのグレードレベルを上げる
        $this->addGradeLevelTotalCount($usrUserId, 1);

        // ミッショントリガー送信
        $this->unitMissionTriggerService->sendGradeUpTrigger($usrUnit);

        return $usrUnit;
    }

    /**
     * ユーザーのUnitSummaryのグレードレベルをaddGradeLevel分上げる
     * @param string $usrUserId
     * @oaram int $addGradeLevel
     * @return void
     */
    public function addGradeLevelTotalCount(string $usrUserId, int $addGradeLevel): void
    {
        // UnitSummaryのレコードを取得
        $usrUnitSummary = $this->usrUnitSummaryRepository->getOrCreate($usrUserId);
        // addGradeLevelを足して更新
        $usrUnitSummary->setGradeLevelTotalCount($usrUnitSummary->getGradeLevelTotalCount() + $addGradeLevel);
        $this->usrUnitSummaryRepository->syncModel($usrUnitSummary);
    }
}
