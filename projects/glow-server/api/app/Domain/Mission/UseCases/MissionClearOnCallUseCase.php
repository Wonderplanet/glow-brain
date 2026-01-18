<?php

declare(strict_types=1);

namespace App\Domain\Mission\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Services\MissionNormalClearOnCallService;
use App\Http\Responses\ResultData\MissionClearOnCallResultData;

class MissionClearOnCallUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private MissionNormalClearOnCallService $missionNormalClearOnCallService,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $missionType,
        string $mstMissionId,
    ): MissionClearOnCallResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        // クリア処理
        $usrMissionStatusDataList = $this->missionNormalClearOnCallService->clearOnCall(
            $usrUserId,
            $now,
            MissionType::getFromString($missionType),
            $mstMissionId,
        );

        // APIレスポンスデータを生成
        $usrMissionAchievementStatusDataList = collect();
        $usrMissionBeginnerStatusDataList = collect();
        switch ($missionType) {
            case MissionType::ACHIEVEMENT->value:
                $usrMissionAchievementStatusDataList = $usrMissionStatusDataList;
                break;
            case MissionType::BEGINNER->value:
                $usrMissionBeginnerStatusDataList = $usrMissionStatusDataList;
                break;
        }

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new MissionClearOnCallResultData(
            $usrMissionAchievementStatusDataList,
            $usrMissionBeginnerStatusDataList,
        );
    }
}
