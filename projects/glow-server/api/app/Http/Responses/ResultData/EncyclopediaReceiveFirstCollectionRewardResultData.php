<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class EncyclopediaReceiveFirstCollectionRewardResultData
{
    public function __construct(
        public Collection $usrEmblems,
        public Collection $usrArtworks,
        public Collection $usrEnemyDiscoveries,
        public Collection $usrUnits,
        public UsrParameterData $usrUserParameter,
        public Collection $rewards,
    ) {
    }
}
