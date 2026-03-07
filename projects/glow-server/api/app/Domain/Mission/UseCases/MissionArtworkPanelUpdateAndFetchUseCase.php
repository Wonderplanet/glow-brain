<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Services\MissionArtworkPanelService;
use App\Domain\Mission\Services\MissionFetchService;
use App\Domain\Resource\Mst\Repositories\MstArtworkPanelMissionRepository;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Http\Responses\ResultData\MissionArtworkPanelUpdateAndFetchResultData;

class MissionArtworkPanelUpdateAndFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        private MissionFetchService $missionFetchService,
        private MstArtworkPanelMissionRepository $mstArtworkPanelMissionRepository,
        private MissionArtworkPanelService $missionArtworkPanelService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private Clock $clock,
    ) {
    }

    public function exec(
        CurrentUser $user,
    ): MissionArtworkPanelUpdateAndFetchResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $mstArtworkPanelMissions = $this->mstArtworkPanelMissionRepository->getActives($now);

        $this->missionArtworkPanelService->createInitialUsrArtworkFragmentsIfNeeded(
            $usrUserId,
            $mstArtworkPanelMissions,
        );

        $artworkPanelMissionFetchStatus = $this->missionFetchService
            ->getMissionLimitedTermFetchStatusForArtworkPanel($usrUserId, $now);

        $this->applyUserTransactionChanges();

        return new MissionArtworkPanelUpdateAndFetchResultData(
            $artworkPanelMissionFetchStatus->getUsrMissionStatusDataList(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
        );
    }
}
