<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\Resource\Enums\InGameContentType;

class UsrAdventBattleStatusData extends UsrInGameStatusData
{
    public function __construct(
        ?UsrAdventBattleSessionInterface $usrAdventBattleSession,
    ) {
        if (!is_null($usrAdventBattleSession)) {
            parent::__construct(
                $usrAdventBattleSession->isStarted(),
                InGameContentType::ADVENT_BATTLE->value,
                $usrAdventBattleSession->getMstAdventBattleId(),
                $usrAdventBattleSession->getPartyNo(),
            );
        } else {
            parent::__construct();
        }
    }
}
