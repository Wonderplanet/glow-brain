<?php

declare(strict_types=1);

namespace App\Domain\Unit\Delegators;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Entities\CheatCheckUnit;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Entities\UnitAudit;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Repositories\UsrUnitSummaryRepository;
use App\Domain\Unit\Services\UnitService;
use App\Domain\Unit\Services\UnitStatusService;
use Illuminate\Support\Collection;

class UnitDelegator
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
        private UsrUnitSummaryRepository $usrUnitSummaryRepository,
        private UnitService $unitService,
        private UnitStatusService $unitStatusService,
    ) {
    }

    /**
     * @param string $usrUserId
     * @return Collection<UsrUnitEntity>
     */
    public function getUsrUnitsByUsrUserId(string $usrUserId): Collection
    {
        $entities = collect();
        $usrModels = $this->usrUnitRepository->getListByUsrUserId($usrUserId);
        foreach ($usrModels as $usrModel) {
            $entities->push($usrModel->toEntity());
        }
        return $entities;
    }

    /**
     * @return Collection<string, UsrUnitEntity> key: mst_units.id
     */
    public function getByMstUnitIds(string $usrUserId, Collection $mstUnitIds): Collection
    {
        $entities = collect();
        $usrModels = $this->usrUnitRepository->getByMstUnitIds($usrUserId, $mstUnitIds);
        foreach ($usrModels as $usrModel) {
            $entity = $usrModel->toEntity();
            $entities->put($entity->getMstUnitId(), $entity);
        }
        return $entities;
    }

    /**
     * @param Collection<string> $mstUnitIds
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        $this->unitService->bulkCreate($usrUserId, $mstUnitIds);
    }

    public function fetchUnitDataByUsrUnitIds(string $usrUserId, Collection $usrUnitIds): Collection
    {
        return $this->unitService->fetchUnitDataByUsrUnitIds($usrUserId, $usrUnitIds);
    }

    public function validateHasUsrUnitByMstUnitId(string $usrUserId, string $mstUnitId): void
    {
        $this->unitService->validateHasUsrUnitByMstUnitId($usrUserId, $mstUnitId);
    }

    public function getChangedUsrUnits(): Collection
    {
        return $this->usrUnitRepository->getChangedModels();
    }

    /**
     * @param string $usrUserId
     * @param Collection<BaseReward> $rewards
     */
    public function convertDuplicatedUnitToItem(
        string $usrUserId,
        Collection $rewards,
    ): void {
        $this->unitService->convertDuplicatedUnitToItem($usrUserId, $rewards);
    }

    /**
     * @param Collection<CheatCheckUnit> $units
     * @return Collection<UnitAudit>
     */
    public function convertUnitDataListToUnitStatusDataList(Collection $units): Collection
    {
        return $this->unitStatusService->convertUnitDataListToUnitStatusDataList($units);
    }

    /**
     * @param Collection<UnitAudit> $unitAudits
     * @param ?string $eventBonusGroupId
     * @param Collection $mstUnitEncyclopediaEffectIds
     * @throws GameException
     */
    public function assignEffectBonusesToUnitStatus(
        Collection $unitAudits,
        ?string $eventBonusGroupId,
        Collection $mstUnitEncyclopediaEffectIds,
    ): void {
        $this->unitStatusService->assignEffectBonusesToUnitStatus(
            $unitAudits,
            $eventBonusGroupId,
            $mstUnitEncyclopediaEffectIds,
        );
    }

    /**
     * グレードの合計値を取得する(usr_unit_summariesテーブルから)
     * @param string $usrUserId
     * @return int
     */
    public function getGradeLevelTotalCount(string $usrUserId): int
    {
        return $this->usrUnitSummaryRepository->getGradeLevelTotalCount($usrUserId);
    }

    /**
     * 対象のユニットのバトル回数をインクリメントする
     *
     * @param string $usrUserId
     * @param Collection $usrUnitIds
     */
    public function incrementBattleCount(string $usrUserId, Collection $usrUnitIds): void
    {
        $this->unitService->incrementBattleCount($usrUserId, $usrUnitIds);
    }

    /**
     * 対象のユニットのバトル回数を加算する
     *
     * @param string $usrUserId
     * @param Collection $usrUnitIds
     * @param int $addNum
     */
    public function addBattleCount(string $usrUserId, Collection $usrUnitIds, int $addNum): void
    {
        $this->unitService->addBattleCount($usrUserId, $usrUnitIds, $addNum);
    }

    /**
     * ユニットの図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstUnitId
     * @throws GameException
     */
    public function markAsCollected(string $usrUserId, string $mstUnitId): void
    {
        $this->unitService->markAsCollected($usrUserId, $mstUnitId);
    }
}
