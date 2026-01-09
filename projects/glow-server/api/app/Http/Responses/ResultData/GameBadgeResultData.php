<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\GameBadgeData;
use Illuminate\Support\Collection;

class GameBadgeResultData
{
    public function __construct(
        public GameBadgeData $gameBadgeData,
        public Collection $mngContentCloses,
    ) {
    }
}
