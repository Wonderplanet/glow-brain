<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Services\MissionFetchService;
use App\Http\Responses\ResultData\MissionAdventBattleFetchResultData;

class MissionAdventBattleFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        // MissionFetchService
        private MissionFetchService $missionFetchService,
        //other
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user): MissionAdventBattleFetchResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // ミッション進捗データ取得
        // ここでは、リセットなどのモデル値のDB更新は行わない
        $limitedTermFetchStatus = $this->missionFetchService->getMissionLimitedTermFetchStatusWhenFetchAll(
            $usrUserId,
            $now,
        );

        $this->processWithoutUserTransactionChanges();
        return new MissionAdventBattleFetchResultData(
            collect(),
            $limitedTermFetchStatus->getAdventBattleMissionFetchStatus()->getUsrMissionStatusDataList(),
        );
    }
}
