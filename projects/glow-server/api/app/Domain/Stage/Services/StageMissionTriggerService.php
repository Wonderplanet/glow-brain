<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Utils\MissionUtil;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Mst\Entities\MstStageEntity;
use App\Domain\Stage\Entities\StageInGameBattleLog;
use App\Domain\Stage\Models\IBaseUsrStage;

class StageMissionTriggerService
{
    public function __construct(
        // Delegator
        private MissionDelegator $missionDelegator,
        private PartyDelegator $partyDelegator,
    ) {
    }

    public function sendStageClearTriggers(
        string $usrUserId,
        MstStageEntity $mstStage,
        IBaseUsrStage $usrStage,
        StageInGameBattleLog $inGameBattleLogData,
        int $partyNo,
        bool $isQuestFirstClear,
        int $lapCount,
    ): void {
        if ($usrStage->isClear() === false) {
            return;
        }

        $triggers = collect();

        // インゲームバトルログ系
        if ($inGameBattleLogData->getDefeatEnemyCount() > 0) {
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::DEFEAT_ENEMY_COUNT->value,
                    null,
                    $inGameBattleLogData->getDefeatEnemyCount() * $lapCount,
                )
            );
        }
        if ($inGameBattleLogData->getDefeatBossEnemyCount() > 0) {
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT->value,
                    null,
                    $inGameBattleLogData->getDefeatBossEnemyCount() * $lapCount,
                )
            );
        }

        // パーティ
        $party = $this->partyDelegator->getParty($usrUserId, $partyNo);
        $unitEntities = $party->getUnits();
        foreach ($unitEntities as $unitEntity) {
            /** @var \App\Domain\Resource\Entities\Unit $unitEntity */
            $mstUnit = $unitEntity->getMstUnit();
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_STAGE_CLEAR_COUNT->value,
                    MissionUtil::makeSpecificUnitStageClearCountCriterionValue(
                        $mstUnit->getId(),
                        $mstStage->getId(),
                    ),
                    $lapCount,
                )
            );
        }

        // ステージをクリアしよう系

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CLEAR_COUNT->value,
                $usrStage->getMstStageId(),
                $lapCount,
            )
        );

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::STAGE_CLEAR_COUNT->value,
                null,
                $lapCount,
            )
        );

        if ($isQuestFirstClear) {
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_QUEST_CLEAR->value,
                    $mstStage->getMstQuestId(),
                    1,
                )
            );

            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::QUEST_CLEAR_COUNT->value,
                    null,
                    1,
                )
            );
        }

        $this->missionDelegator->addTriggers($triggers);
    }

    public function sendStageStartTriggers(
        string $usrUserId,
        string $mstStageId,
        int $partyNo,
        int $lapCount = 1,
    ): void {
        $triggers = collect();

        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_STAGE_CHALLENGE_COUNT->value,
                $mstStageId,
                $lapCount,
            )
        );

        // パーティ
        $party = $this->partyDelegator->getParty($usrUserId, $partyNo);
        $unitEntities = $party->getUnits();
        foreach ($unitEntities as $unitEntity) {
            /** @var \App\Domain\Resource\Entities\Unit $unitEntity */
            $mstUnit = $unitEntity->getMstUnit();
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT->value,
                    MissionUtil::makeSpecificUnitStageClearCountCriterionValue(
                        $mstUnit->getId(),
                        $mstStageId,
                    ),
                    $lapCount,
                )
            );
        }

        $this->missionDelegator->addTriggers($triggers);
    }
}
