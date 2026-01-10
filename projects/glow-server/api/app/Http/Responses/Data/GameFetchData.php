<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\User\Models\UsrUserBuyCountInterface;
use Illuminate\Support\Collection;

class GameFetchData
{
    /**
     * @param Collection<\App\Domain\Stage\Models\UsrStageInterface> $usrStages
     * @param Collection<\App\Domain\Stage\Models\UsrStageEventInterface> $usrStageEvents
     * @param Collection<\App\Http\Responses\Data\UsrStageEnhanceStatusData> $usrStageEnhanceStatusDataList
     */
    public function __construct(
        public UsrParameterData $usrUserParameter,
        public Collection $usrStages,
        public Collection $usrStageEvents,
        public Collection $usrStageEnhanceStatusDataList,
        public Collection $usrAdventBattles,
        public GameBadgeData $gameBadgeData,
        public UsrUserBuyCountInterface $usrUserBuyCount,
        public MissionStatusData $missionStatusData,
    ) {
    }
}
