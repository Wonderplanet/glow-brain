<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\OpponentPvpStatusData;

class PvpStartResultData
{
    public function __construct(
        private OpponentPvpStatusData $opponentPvpStatus,
    ) {
    }

    public function getOpponentPvpStatus(): OpponentPvpStatusData
    {
        return $this->opponentPvpStatus;
    }
}
