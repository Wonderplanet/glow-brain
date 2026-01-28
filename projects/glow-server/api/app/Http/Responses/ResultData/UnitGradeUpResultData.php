<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface;
use App\Domain\Encyclopedia\Models\UsrArtworkInterface;
use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

class UnitGradeUpResultData
{
    /**
     * @param UsrUnitInterface $usrUnit
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<UsrArtworkInterface> $usrArtworks
     * @param Collection<UsrArtworkFragmentInterface> $usrArtworkFragments
     * @param Collection<\App\Domain\Resource\Entities\Rewards\UnitGradeUpReward> $unitGradeUpRewards
     */
    public function __construct(
        public UsrUnitInterface $usrUnit,
        public Collection $usrItems,
        public Collection $usrArtworks,
        public Collection $usrArtworkFragments,
        public Collection $unitGradeUpRewards,
    ) {
    }
}
