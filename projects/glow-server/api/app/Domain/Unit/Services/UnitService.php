<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Entities\Unit;
use App\Domain\Resource\Enums\RewardConvertedReason;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Repositories\MstUnitFragmentConvertRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Unit\Constants\UnitConstant;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use Illuminate\Support\Collection;

class UnitService
{
    public function __construct(
        private UsrUnitRepository $usrUnitRepository,
        private MstUnitRepository $mstUnitRepository,
        private MstUnitFragmentConvertRepository $mstUnitFragmentConvertRepository,
        private UnitMissionTriggerService $unitMissionTriggerService,
        private UnitGradeUpService $unitGradeUpService,
    ) {
    }

    /**
     * 指定ユニットを新規獲得する
     * ユニットIDに重複があった場合は獲得されないので注意
     *
     * @param string $usrUserId
     * @param Collection<string> $mstUnitIds mst_units.idの配列
     */
    public function bulkCreate(string $usrUserId, Collection $mstUnitIds): void
    {
        $mstUnitIds = $mstUnitIds->unique();
        $validMstUnits = $this->mstUnitRepository->getByIds($mstUnitIds);

        $units = collect();
        foreach ($validMstUnits as $mstUnit) {
            /** @var \App\Domain\Resource\Mst\Entities\MstUnitEntity $mstUnit */
            $usrUnit = $this->usrUnitRepository->create($usrUserId, $mstUnit->getId());

            $units->push(new Unit($mstUnit, $usrUnit->toEntity()));
        }

        // insertしたユニット分のグエレードレベルをサマリー値に足す
        $this->unitGradeUpService->addGradeLevelTotalCount(
            $usrUserId,
            $validMstUnits->count() * UnitConstant::FIRST_UNIT_GRADE_LEVEL
        );
        // ミッショントリガー送信
        $this->unitMissionTriggerService->sendNewUnitTrigger($units);
    }

    /**
     * 指定されたユニットのマスタとユーザデータを取得し、紐付けたデータを返す
     * 指定されたusrUnitIdsの並びと同じになるように返す
     *
     * @return Collection<Unit>
     */
    public function fetchUnitDataByUsrUnitIds(string $usrUserId, Collection $usrUnitIds): Collection
    {
        $unitEntities = collect();

        $usrUnits = $this->usrUnitRepository->getByIds($usrUserId, $usrUnitIds)
            ->keyBy(fn(UsrUnitInterface $usrUnit) => $usrUnit->getId());

        $mstUnitIds = $usrUnits->map(function (UsrUnitInterface $usrUnit) {
            return $usrUnit->getMstUnitId();
        });
        $mstUnits = $this->mstUnitRepository->getByIds($mstUnitIds);

        foreach ($usrUnitIds as $usrUnitId) {
            /** @var UsrUnitInterface|null $usrUnit */
            $usrUnit = $usrUnits->get($usrUnitId);
            if (is_null($usrUnit)) {
                continue;
            }

            $mstUnitId = $usrUnit->getMstUnitId();
            $mstUnit = $mstUnits->get($mstUnitId);
            if (is_null($mstUnit)) {
                continue;
            }

            $unitEntity = new Unit(
                $mstUnit,
                $usrUnit->toEntity(),
            );

            $unitEntities->push($unitEntity);
        }
        return $unitEntities;
    }

    /**
     * ユーザがユニットを所持していて、有効なユニットか確認する
     * @param string $usrUserId
     * @param string $mstUnitId
     * @return void
     * @throws GameException
     */
    public function validateHasUsrUnitByMstUnitId(string $usrUserId, string $mstUnitId): void
    {
        $this->mstUnitRepository->getByIdWithError($mstUnitId);
        if (!$this->usrUnitRepository->isCheckUnit($usrUserId, $mstUnitId)) {
            throw new GameException(
                ErrorCode::UNIT_NOT_FOUND,
                sprintf('usr_units record is not found. (mst_unit_id: %s)', $mstUnitId)
            );
        }
    }

    /**
     * 所持済みユニットの場合は、別リソースへ変換する
     * Rewardインスタンスは参照渡しで変換しているため、返り値はなし
     *
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     */
    public function convertDuplicatedUnitToItem(
        string $usrUserId,
        Collection $rewards,
    ): void {
        if ($rewards->isEmpty()) {
            return;
        }

        $targetRewards = collect();
        $uniqueMstUnitIds = collect();
        foreach ($rewards as $reward) {
            if ($reward->getType() !== RewardType::UNIT->value) {
                continue;
            }
            $mstUnitId = $reward->getResourceId();
            $uniqueMstUnitIds->put($mstUnitId, $mstUnitId);
            $targetRewards->push($reward);
        }
        if ($uniqueMstUnitIds->isEmpty()) {
            return;
        }

        $validMstUnits = $this->mstUnitRepository->getByIds($uniqueMstUnitIds->values());
        $mstUnitFragmentConverts = $this->mstUnitFragmentConvertRepository->getMapAllByUnitLabel();

        $ownedMstUnitIds = $this->usrUnitRepository
            ->getByMstUnitIds($usrUserId, $validMstUnits->keys())
            ->mapWithKeys(function (UsrUnitInterface $usrUnit) {
                return [$usrUnit->getMstUnitId() => $usrUnit->getMstUnitId()];
            });

        $duplicatedMstUnitIds = collect();
        foreach ($targetRewards as $reward) {
            /** @var \App\Domain\Resource\Entities\Rewards\BaseReward $reward */
            $mstUnitId = $reward->getResourceId();

            // 初獲得ユニットの場合
            if ($ownedMstUnitIds->has($mstUnitId) === false) {
                // 所持済みユニットとして記録
                $ownedMstUnitIds->put($mstUnitId, $mstUnitId);

                if ($reward->getAmount() <= 1) {
                    continue;
                }

                // 初獲得分と重複分のRewardインスタンスを分離する
                $noConvertReward = $reward->divideRewardByAmount(1);
                $rewards->put($noConvertReward->getId(), $noConvertReward);
            }

            // 重複獲得ユニットの変換
            // ミッショントリガーように変換前のユニットと数を記録
            $duplicatedMstUnitIds = $duplicatedMstUnitIds->pad(
                $duplicatedMstUnitIds->count() + $reward->getAmount(),
                $mstUnitId,
            );
            // Rewardの変換
            $mstUnit = $validMstUnits->get($mstUnitId);
            $mstUnitFragmentConvert = $mstUnitFragmentConverts->get($mstUnit?->getUnitLabel());
            if ($mstUnit === null || $mstUnitFragmentConvert === null) {
                // 無効なマスタデータの場合は配布対象から除外する
                $rewards->forget($reward->getId());
                continue;
            }
            $reward->setRewardData(new RewardDto(
                RewardType::ITEM->value,
                $mstUnit->getFragmentMstItemId(),
                $reward->getAmount() * $mstUnitFragmentConvert->getConvertAmount(),
            ));
            $reward->setRewardConvertedReason(RewardConvertedReason::DUPLICATED_UNIT);
        }

        // ミッショントリガー
        $this->unitMissionTriggerService->sendDuplicatedUnitTrigger($duplicatedMstUnitIds);
    }

    /**
     * 対象のユニットのバトル回数をインクリメントする
     *
     * @param string $usrUserId
     * @param Collection $usrUnitIds
     */
    public function incrementBattleCount(string $usrUserId, Collection $usrUnitIds): void
    {
        $usrUnits = $this->usrUnitRepository->getByIds($usrUserId, $usrUnitIds);
        $usrUnits->each(fn(UsrUnitInterface $usrUnit) => $usrUnit->incrementBattleCount());
        $this->usrUnitRepository->syncModels($usrUnits);
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
        $usrUnits = $this->usrUnitRepository->getByIds($usrUserId, $usrUnitIds);
        $usrUnits->each(fn(UsrUnitInterface $usrUnit) => $usrUnit->addBattleCount($addNum));
        $this->usrUnitRepository->syncModels($usrUnits);
    }


    /**
     * ユニットの図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstUnitId
     * @throws GameException
     */
    public function markAsCollected(string $usrUserId, string $mstUnitId): void
    {
        $usrUnit =  $this->usrUnitRepository->getByMstUnitId($usrUserId, $mstUnitId);

        // データがない
        if (is_null($usrUnit)) {
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_DATA_NOT_FOUND,
                'unit encyclopedia is new data not found. (' . $mstUnitId . ')'
            );
        }
        // 取得したデータのis_new_encyclopediaが1かどうか
        if ($usrUnit->isAlreadyCollected()) {
            // 取得したデータのis_new_encyclopediaが1でない
            throw new GameException(
                ErrorCode::ENCYCLOPEDIA_NOT_IS_NEW,
                'unit encyclopedia not is new data . (' . $mstUnitId . ')'
            );
        }
        $usrUnit->markAsCollected();
        $this->usrUnitRepository->syncModel($usrUnit);
    }
}
