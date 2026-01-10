<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;

class AdventBattleMissionTriggerService
{
    public function __construct(
        // Delegator
        private MissionDelegator $missionDelegator,
    ) {
    }

    public function sendStartTriggers(): void
    {
        $triggers = collect();

        // 降臨バトルを X 回挑戦する
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT->value,
                null,
                1,
            )
        );

        $this->missionDelegator->addTriggers($triggers);
    }

    public function sendEndTriggers(
        AdventBattleInGameBattleLog $inGameBattleLogData,
    ): void {
        $score = $inGameBattleLogData->getScore();

        $triggers = collect();

        // 降臨バトルの累計スコアが X 達成
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::ADVENT_BATTLE_SCORE->value,
                null,
                $score,
            )
        );

        // 降臨バトルのハイスコアが X 達成
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::ADVENT_BATTLE_TOTAL_SCORE->value,
                null,
                $score,
            )
        );

        // インゲームバトルログ系
        if ($inGameBattleLogData->getDefeatEnemyCount() > 0) {
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::DEFEAT_ENEMY_COUNT->value,
                    null,
                    $inGameBattleLogData->getDefeatEnemyCount(),
                )
            );
        }
        if ($inGameBattleLogData->getDefeatBossEnemyCount() > 0) {
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT->value,
                    null,
                    $inGameBattleLogData->getDefeatBossEnemyCount(),
                )
            );
        }

        $this->missionDelegator->addTriggers($triggers);
    }
}
