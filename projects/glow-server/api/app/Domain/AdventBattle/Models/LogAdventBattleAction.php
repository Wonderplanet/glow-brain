<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Models;

use App\Domain\AdventBattle\Enums\LogAdventBattleResult;
use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $mst_advent_battle_id
 * @property string $api_path
 * @property int $result
 * @property string|null $party_units
 * @property string|null $used_outpost
 * @property string|null $in_game_battle_log
 */
class LogAdventBattleAction extends LogModel
{
    use HasFactory;

    public function setMstAdventBattleId(string $mstAdventBattleId): void
    {
        $this->mst_advent_battle_id = $mstAdventBattleId;
    }

    public function setApiPath(string $apiPath): void
    {
        $this->api_path = $apiPath;
    }

    public function setResult(LogAdventBattleResult $result): void
    {
        $this->result = $result->value;
    }

    /**
     * @param array<mixed> $partyUnits
     */
    public function setPartyUnits(?array $partyUnits): void
    {
        if ($partyUnits === null) {
            $this->party_units = null;
            return;
        }
        $this->party_units = json_encode($partyUnits);
    }

    /**
     * @param array<mixed> $usedOutpost
     */
    public function setUsedOutpost(?array $usedOutpost): void
    {
        if ($usedOutpost === null) {
            $this->used_outpost = null;
            return;
        }
        $this->used_outpost = json_encode($usedOutpost);
    }

    /**
     * @param array<mixed> $inGameBattleLog
     */
    public function setInGameBattleLog(?array $inGameBattleLog): void
    {
        if ($inGameBattleLog === null) {
            $this->in_game_battle_log = null;
            return;
        }
        $this->in_game_battle_log = json_encode($inGameBattleLog);
    }
}
