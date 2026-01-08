<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\AdventBattleResultData;

class AdventBattleInfoResultData
{
    public function __construct(
        public ?AdventBattleResultData $adventBattleResultData,
    ) {
    }
}
