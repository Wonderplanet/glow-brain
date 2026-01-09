<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleInGameBattleLog;
use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity;

class AdventBattleEndRaidService extends AdventBattleEndService
{
    /**
     * @inheritdoc
     */
    public function updateAllUserTotalScore(
        MstAdventBattleEntity $mstAdventBattle,
        AdventBattleInGameBattleLog $inGameBattleLogData,
    ): int {
        return $this->adventBattleCacheService->incrementRaidTotalScore(
            $mstAdventBattle->getId(),
            $inGameBattleLogData->getScore(),
        );
    }
}
