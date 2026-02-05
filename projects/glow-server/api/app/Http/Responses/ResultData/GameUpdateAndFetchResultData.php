<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\GameFetchData;
use App\Http\Responses\Data\GameFetchOtherData;
use App\Http\Responses\Data\GameUpdateData;

class GameUpdateAndFetchResultData
{
    public function __construct(
        public GameFetchData $gameFetchData,
        public GameFetchOtherData $gameFetchOtherData,
        public GameUpdateData $gameUpdateData,
    ) {
    }
}
