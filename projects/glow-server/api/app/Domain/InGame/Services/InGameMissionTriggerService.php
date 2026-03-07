<?php

declare(strict_types=1);

namespace App\Domain\InGame\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use Illuminate\Support\Collection;

class InGameMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    /**
     * インゲーム中に発見した敵に関するミッションのトリガーを送信する
     *
     * @param Collection<\App\Domain\InGame\Entities\DiscoveredEnemy> $discoveredEnemies
     */
    public function sendDiscoveredEnemyTriggers(
        Collection $discoveredEnemies,
        int $lapCount = 1,
    ): void {
        $triggers = collect();

        foreach ($discoveredEnemies as $discoveredEnemy) {
            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT->value,
                    $discoveredEnemy->getMstEnemyCharacterId(),
                    $discoveredEnemy->getDiscoveredCount() * $lapCount,
                )
            );

            if ($discoveredEnemy->isNew() === false) {
                continue;
            }
            // ここからは新発見エネミーのみトリガーする

            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_SERIES_ENEMY_DISCOVERY_COUNT->value,
                    $discoveredEnemy->getMstSeriesId(),
                    1,
                )
            );

            $triggers->push(
                new MissionTrigger(
                    MissionCriterionType::ENEMY_DISCOVERY_COUNT->value,
                    null,
                    1,
                )
            );
        }

        $this->missionDelegator->addTriggers($triggers);
    }
}
