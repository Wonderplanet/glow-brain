<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Entities\LogTriggers\JoinLogTrigger;
use App\Domain\Resource\Mst\Entities\MstUnitEntity;
use App\Domain\Resource\Mst\Repositories\MstUnitLevelUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRankUpRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitSpecificRankUpRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\LogUnitLevelUpRepository;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\User\Delegators\UserDelegator;
use Carbon\CarbonImmutable;

class UnitLevelUpService
{
    public function __construct(
        // Repository
        private MstUnitLevelUpRepository $mstUnitLevelUpRepository,
        private MstUnitSpecificRankUpRepository $mstUnitSpecificRankUpRepository,
        private MstUnitRankUpRepository $mstUnitRankUpRepository,
        private MstUnitRepository $mstUnitRepository,
        private MstConfigService $mstConfigService,
        private LogUnitLevelUpRepository $logUnitLevelUpRepository,
        // Service
        private UsrUnitRepository $usrUnitRepository,
        private UnitMissionTriggerService $unitMissionTriggerService,
        // Delegator
        private UserDelegator $userDelegator
    ) {
    }

    /**
     * @param string $usrUserId
     * @param string $usrUnitId
     * @param int    $level 強化後のレベル
     * @param CarbonImmutable $now
     * @return UsrUnitInterface|null
     * @throws GameException
     */
    public function levelUp(string $usrUserId, string $usrUnitId, int $level, CarbonImmutable $now): ?UsrUnitInterface
    {
        $usrUnit = $this->usrUnitRepository->getById($usrUnitId, $usrUserId);
        $mstUnit = $this->mstUnitRepository->getByIdWithError($usrUnit->getMstUnitId());

        $beforeLevel = $usrUnit->getLevel();

        // ランクアップ状況に応じた最大レベルを取得
        $maxLevel = $this->getMaxLevel($usrUnit->getRank(), $mstUnit);

        $this->validateLevel($level, $usrUnit->getLevel(), $maxLevel);

        $mstUnitLevelUps = $this->mstUnitLevelUpRepository->getLevelIsInRange(
            $mstUnit->getUnitLabel(),
            $usrUnit->getLevel() + 1,
            $level
        );
        if ($mstUnitLevelUps->isEmpty() || $mstUnitLevelUps->last()->getLevel() !== $level) {
            // レベルアップのマスターデータがない
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_unit_level_ups record is not found. (level: $level)"
            );
        }

        $logUnitLevelUp = $this->logUnitLevelUpRepository->create(
            $usrUserId,
            $mstUnit->getId(),
            $beforeLevel,
            $level,
        );

        // コスト消費
        $costCoin = $mstUnitLevelUps->sum(fn($mstUnitLevelUp) => $mstUnitLevelUp->getRequiredCoin());
        $this->userDelegator->consumeCoin(
            $usrUserId,
            $costCoin,
            $now,
            new JoinLogTrigger($logUnitLevelUp),
        );

        // レベル更新
        $usrUnit->setLevel($level);
        $this->usrUnitRepository->syncModel($usrUnit);

        // ミッショントリガー送信
        $this->unitMissionTriggerService->sendLevelUpTrigger($usrUnit, $beforeLevel);

        return $usrUnit;
    }

    /**
     * ランクアップ状況に応じた最大レベルを取得する
     * @return int
     * @throws GameException
     */
    public function getMaxLevel(int $currentRank, MstUnitEntity $mstUnit): int
    {
        $maxLevel = null;
        $targetRank = $currentRank + 1;

        if ($mstUnit->hasSpecificRankUp()) {
            $mstUnitRankUp = $this->mstUnitSpecificRankUpRepository->getByMstUnitIdAndRank(
                $mstUnit->getId(),
                $targetRank,
                false
            );
            $maxLevel = $mstUnitRankUp?->getRequireLevel();
        } else {
            $mstUnitRankUp = $this->mstUnitRankUpRepository->getByUnitLabelAndRank(
                $mstUnit->getUnitLabel(),
                $targetRank,
                false
            );
            $maxLevel = $mstUnitRankUp?->getRequireLevel();
        }

        if (!is_null($maxLevel)) {
            // 上位のランクがあるので上位のランクの必要レベルが今のランクの最大レベル
            return $maxLevel;
        }

        // 上位のランクがない(=現時点で最上位ランク)のためmst_unit_level_upsの最大レベルがユニットの最大レベル
        return $this->mstUnitLevelUpRepository->getMaxMstUnitLevelUp($mstUnit->getUnitLabel(), true)->getLevel();
    }

    /**
     * 強化後のレベルを検証する
     * @param int $targetLevel 強化先のレベル
     * @param int $currentLevel 現在のレベル
     * @param int $maxLevel マスターデータの上限レベル
     * @throws GameException
     */
    private function validateLevel(int $targetLevel, int $currentLevel, int $maxLevel): void
    {
        if ($targetLevel < 1 || $targetLevel <= $currentLevel) {
            // 強化先のレベルの値が不正
            throw new GameException(
                ErrorCode::UNIT_LEVE_UP_INVALID_LEVEL,
                "invalid level. (targetLevel: $targetLevel, currentLevel: $currentLevel, maxLevel: $maxLevel)"
            );
        }

        // mstConfigからユニットのレベル上限の設定を取得
        $mstConfigUnitLevelCap = $this->mstConfigService->getUnitLevelUpCap();
        // マスターデータの上限レベルとユニットのランクごとに設定された上限レベルの小さい方を上限とする
        $maxLevel = ($mstConfigUnitLevelCap !== 0) ? min($maxLevel, $mstConfigUnitLevelCap) : $maxLevel;

        if ($maxLevel < $targetLevel) {
            // レベル上限を越えて強化しようとしている
            throw new GameException(
                ErrorCode::UNIT_LEVEL_UP_EXCEED_LIMIT_LEVEL,
                "invalid level. (targetLevel: $targetLevel, currentLevel: $currentLevel, maxLevel: $maxLevel)"
            );
        }
    }
}
