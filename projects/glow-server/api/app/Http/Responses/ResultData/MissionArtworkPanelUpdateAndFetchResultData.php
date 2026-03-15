<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class MissionArtworkPanelUpdateAndFetchResultData
{
    /**
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionLimitedTerms
     * @param Collection<\App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface> $usrArtworkFragments
     */
    public function __construct(
        public Collection $usrMissionLimitedTerms,
        public Collection $usrArtworkFragments,
    ) {
    }
}
