<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Pvp\Entities\PvpInGameBattleLog;

/**
 * 降臨バトルログサービスからコピーしてきた、これを改変していく
 */
class PvpLogService
{
    public function __construct(
        // Delegator
        private PartyDelegator $partyDelegator,
    ) {
    }

    /**
     * インゲームバトルログデータを生成する
     *
     * @param array<array<mixed>>  $inGameBattleLog
     * @return PvpInGameBattleLog
     */
    public function makeInGameBattleLogData(array $inGameBattleLog): PvpInGameBattleLog
    {
        return new PvpInGameBattleLog(
            $inGameBattleLog['clearTimeMs'] ?? 99999, // デフォルト値はスピードアタック計測値の最大とする
            $inGameBattleLog['maxDamage'] ?? 0,
            $this->partyDelegator->makePartyStatusList(collect($inGameBattleLog['partyStatus'] ?? [])),
            $this->partyDelegator->makePartyStatusList(collect($inGameBattleLog['opponentPartyStatus'] ?? [])),
        );
    }
}
