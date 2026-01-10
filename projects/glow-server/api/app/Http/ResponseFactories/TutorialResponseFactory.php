<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\TutorialGachaConfirmResultData;
use App\Http\Responses\ResultData\TutorialGachaDrawResultData;
use App\Http\Responses\ResultData\TutorialStageEndResultData;
use App\Http\Responses\ResultData\TutorialStageStartResultData;
use App\Http\Responses\ResultData\TutorialUnitLevelUpResultData;
use App\Http\Responses\ResultData\TutorialUpdateStatusResultData;
use Illuminate\Http\JsonResponse;

class TutorialResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createUpdateStatusResponse(TutorialUpdateStatusResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrGachaData($result, $resultData->usrGachas, true);
        $result = $this->responseDataFactory->addUsrIdleIncentiveData($result, $resultData->usrIdleIncentive);
        $result = $this->responseDataFactory->addMissionDailyBonusRewardData($result, $resultData->missionDailyBonusRewards);
        $result = $this->responseDataFactory->addMissionEventDailyBonusRewardData($result, $resultData->missionEventDailyBonusRewards);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);
        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addUsrEventDailyBonusProgressData(
            $result,
            $resultData->usrMissionEventDailyBonusProgresses,
        );
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrEmblemData($result, $resultData->usrEmblems);
        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);


        return response()->json($result);
    }

    public function createGachaDrawResponse(TutorialGachaDrawResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addGachaResultData($result, $resultData->gachaRewards);

        return response()->json($result);
    }

    public function createGachaConfirmResponse(TutorialGachaConfirmResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addTutorialStatusData($result, $resultData->tutorialStatus);
        $result = $this->responseDataFactory->addGachaResultData($result, $resultData->gachaRewards);
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);

        return response()->json($result);
    }

    public function createStageStartResponse(TutorialStageStartResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addTutorialStatusData($result, $resultData->tutorialStatus);
        $result = $this->responseDataFactory->addUsrInGameStatusData($result, $resultData->usrStageStatus);

        return response()->json($result);
    }

    public function createStageEndResponse(TutorialStageEndResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addTutorialStatusData($result, $resultData->tutorialStatus);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);

        $result = $this->responseDataFactory->addStageRewardData(
            $result,
            $resultData->stageFirstClearRewards,
            collect(),
            collect(),
            collect(),
        );

        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            true,
        );
        $result = $this->responseDataFactory->addUsrUnitData(
            $result,
            $resultData->usrUnits,
            true,
        );
        $result = $this->responseDataFactory->addUsrEmblemData(
            $result,
            $resultData->usrEmblems,
        );

        return response()->json($result);
    }

    public function createUnitLevelUpResponse(TutorialUnitLevelUpResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addTutorialStatusData($result, $resultData->tutorialStatus);
        $result = $this->responseDataFactory->addUsrUnitData($result, collect([$resultData->usrUnit]), false);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);
        return response()->json($result);
    }
}
