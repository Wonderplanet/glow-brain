<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\MissionAdventBattleFetchResultData;
use App\Http\Responses\ResultData\MissionBulkReceiveRewardResultData;
use App\Http\Responses\ResultData\MissionClearOnCallResultData;
use App\Http\Responses\ResultData\MissionEventDailyBonusUpdateResultData;
use App\Http\Responses\ResultData\MissionEventUpdateAndFetchResultData;
use App\Http\Responses\ResultData\MissionUpdateAndFetchResultData;
use Illuminate\Http\JsonResponse;

class MissionResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createUpdateAndFetchResponse(MissionUpdateAndFetchResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrMissionAchievementData(
            $result,
            $resultData->usrMissionAchievementStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionDailyData(
            $result,
            $resultData->usrMissionDailyStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionWeeklyData(
            $result,
            $resultData->usrMissionWeeklyStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionDailyBonusData(
            $result,
            $resultData->usrMissionDailyBonusStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionBeginnerData(
            $result,
            $resultData->usrMissionBeginnerStatusDataList,
        );
        $result = $this->responseDataFactory->addMissionBeginnerDaysFromStartData(
            $result,
            $resultData->missionBeginnerDaysFromStart,
        );
        $result = $this->responseDataFactory->addUsrMissionBonusPointData(
            $result,
            $resultData->usrMissionBonusPoints,
        );

        return response()->json($result);
    }

    public function createBulkReceiveRewardResponse(MissionBulkReceiveRewardResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addMissionReceiveRewardData(
            $result,
            $resultData->missionReceiveRewardStatuses,
        );
        $result = $this->responseDataFactory->addMissionRewardData(
            $result,
            $resultData->missionRewards,
        );

        // ミッション進捗データ
        $result = $this->responseDataFactory->addUsrMissionAchievementData(
            $result,
            $resultData->usrMissionAchievementStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionDailyData(
            $result,
            $resultData->usrMissionDailyStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionWeeklyData(
            $result,
            $resultData->usrMissionWeeklyStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionBeginnerData(
            $result,
            $resultData->usrMissionBeginnerStatusDataList,
        );
        $result = $this->responseDataFactory->addEventMissionData(
            $result,
            $resultData->usrMissionEventStatusDataList,
            $resultData->usrMissionEventDailyStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrLimitedTermMissionData(
            $result,
            $resultData->usrMissionlimitedTermStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionBonusPointData(
            $result,
            $resultData->usrMissionBonusPoints,
        );

        //  その他
        $result = $this->responseDataFactory->addUsrParameterData(
            $result,
            $resultData->usrUserParameter,
        );
        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            isMulti: true,
        );
        $result = $this->responseDataFactory->addUsrUnitData(
            $result,
            $resultData->usrUnits,
            isMulti: true,
        );
        $result = $this->responseDataFactory->addUserLevelData(
            $result,
            $resultData->userLevelUpData,
        );
        $result = $this->responseDataFactory->addUsrConditionPackData(
            $result,
            $resultData->usrConditionPacks,
        );

        return response()->json($result);
    }

    public function createClearOnCallResponse(MissionClearOnCallResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrMissionAchievementData(
            $result,
            $resultData->usrMissionAchievementStatusDataList,
        );
        $result = $this->responseDataFactory->addUsrMissionBeginnerData(
            $result,
            $resultData->usrMissionBeginnerStatusDataList,
        );

        return response()->json($result);
    }

    public function createEventDailyBonusUpdateResponse(
        MissionEventDailyBonusUpdateResultData $resultData
    ): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addMissionEventDailyBonusRewardData(
            $result,
            $resultData->missionEventDailyBonusRewards,
        );
        $result = $this->responseDataFactory->addUsrEventDailyBonusProgressData(
            $result,
            $resultData->usrMissionEventDailyBonusProgresses,
        );
        $result = $this->responseDataFactory->addUsrParameterData(
            $result,
            $resultData->usrUserParameter,
        );
        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            isMulti: true,
        );
        $result = $this->responseDataFactory->addUsrUnitData(
            $result,
            $resultData->usrUnits,
            isMulti: true,
        );
        $result = $this->responseDataFactory->addUsrEmblemData(
            $result,
            $resultData->usrEmblems,
        );
        $result = $this->responseDataFactory->addUserLevelData(
            $result,
            $resultData->userLevelUpData,
        );
        $result = $this->responseDataFactory->addUsrConditionPackData(
            $result,
            $resultData->usrConditionPacks,
        );
        return response()->json($result);
    }

    public function createEventUpdateAndFetchResponse(MissionEventUpdateAndFetchResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addEventMissionData(
            $result,
            $resultData->usrMissionEventStatusDataList,
            $resultData->usrMissionEventDailyStatusDataList,
        );

        return response()->json($result);
    }

    public function createAdventBattleFetchResponse(MissionAdventBattleFetchResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrEventMissionData(
            $result,
            $resultData->usrMissionEventStatusDataList
        );
        $result = $this->responseDataFactory->addUsrLimitedTermMissionData(
            $result,
            $resultData->usrMissionLimitedTermStatusDataList
        );

        return response()->json($result);
    }
}
