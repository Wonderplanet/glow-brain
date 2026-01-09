<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class AdventBattleTopResultData
{
    public function __construct(
        public Collection $sentRaidTotalScoreRewards,
        public Collection $sentMaxScoreRewards,
        public UsrParameterData $usrParameterData,
        public Collection $usrItems,
        public Collection $usrEmblems,
    ) {
    }
}
