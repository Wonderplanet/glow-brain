<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\Pvp\Models\UsrPvpSessionInterface;
use App\Domain\Resource\Enums\InGameContentType;

class UsrPvpInGameStatusData extends UsrInGameStatusData
{
    public function __construct(
        ?UsrPvpSessionInterface $usrPvpSession,
    ) {
        if (!is_null($usrPvpSession)) {
            parent::__construct(
                $usrPvpSession->isStarted(),
                InGameContentType::PVP->value,
                $usrPvpSession->getSysPvpSeasonId(),
                $usrPvpSession->getPartyNo(),
            );
        } else {
            parent::__construct();
        }
    }
}
