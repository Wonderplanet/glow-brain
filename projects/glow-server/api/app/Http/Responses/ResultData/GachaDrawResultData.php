<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Gacha\Models\UsrGachaInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class GachaDrawResultData
{
    public function __construct(
        public Collection $gachaRewards,
        public Collection $usrUnits,
        public Collection $usrItems,
        public UsrParameterData $usrParameterData,
        public UsrGachaInterface $usrGacha,
        public Collection $usrGachaUppers,
    ) {
    }
}
