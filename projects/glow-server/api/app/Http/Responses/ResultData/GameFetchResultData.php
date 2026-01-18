<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\GameFetchData;

class GameFetchResultData
{
    public function __construct(
        public GameFetchData $gameFetchData,
    ) {
    }
}
