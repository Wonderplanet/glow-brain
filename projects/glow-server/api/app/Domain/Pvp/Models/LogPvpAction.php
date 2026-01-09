<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Models;

use App\Domain\Pvp\Enums\LogPvpResult;
use App\Domain\Resource\Log\Models\LogModel;

class LogPvpAction extends LogModel
{
    public function setUsrUserId(string $usrUserId): void
    {
        $this->usr_user_id = $usrUserId;
    }

    public function setSysPvpSeasonId(string $sysPvpSeasonId): void
    {
        $this->sys_pvp_season_id = $sysPvpSeasonId;
    }

    public function setResult(LogPvpResult $result): void
    {
        $this->result = $result->value;
    }

    public function setApiPath(string $apiPath): void
    {
        $this->api_path = $apiPath;
    }

    public function setMyPvpStatus(string $myPvpStatus): void
    {
        $this->my_pvp_status = $myPvpStatus;
    }

    public function setOpponentMyId(string $opponentMyId): void
    {
        $this->opponent_my_id = $opponentMyId;
    }

    public function setOpponentPvpStatus(string $opponentPvpStatus): void
    {
        $this->opponent_pvp_status = $opponentPvpStatus;
    }

    /**
     * @param array<mixed>|null $inGameBattleLog
     */
    public function setInGameBattleLog(?array $inGameBattleLog): void
    {
        $this->in_game_battle_log = json_encode($inGameBattleLog, JSON_THROW_ON_ERROR);
    }
}
