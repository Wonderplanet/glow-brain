<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\BoxGacha\Models\UsrBoxGachaInterface;
use App\Domain\Emblem\Models\UsrEmblemInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Resource\Entities\Rewards\BoxGachaReward;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class BoxGachaDrawResultData
{
    /**
     * @param UsrParameterData $usrParameterData
     * @param Collection<int, UsrItemInterface> $usrItems
     * @param Collection<int, UsrUnitInterface> $usrUnits
     * @param Collection<int, UsrEmblemInterface> $usrEmblems
     * @param Collection<int, UsrArtworkInterface> $usrArtworks
     * @param Collection<int, UsrArtworkFragmentInterface> $usrArtworkFragments
     * @param UsrBoxGachaInterface $usrBoxGacha
     * @param Collection<int, BoxGachaReward> $boxGachaRewards
     */
    public function __construct(
        public UsrParameterData $usrParameterData,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $usrEmblems,
        public Collection $usrArtworks,
        public Collection $usrArtworkFragments,
        public UsrBoxGachaInterface $usrBoxGacha,
        public Collection $boxGachaRewards,
    ) {
    }
}
