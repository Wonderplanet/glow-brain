<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusResponseData;

class PvpResumeResultData
{
    public function __construct(
        private OpponentPvpStatusData $opponentPvpStatus,
        private OpponentSelectStatusResponseData $opponentSelectStatusResponse,
    ) {
    }

    public function getOpponentPvpStatus(): OpponentPvpStatusData
    {
        return $this->opponentPvpStatus;
    }

    public function getOpponentSelectStatusResponse(): OpponentSelectStatusResponseData
    {
        return $this->opponentSelectStatusResponse;
    }
}
