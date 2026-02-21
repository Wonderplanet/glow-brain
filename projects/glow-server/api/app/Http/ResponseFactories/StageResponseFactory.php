<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Domain\Resource\Entities\Rewards\StageAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\StageFirstClearReward;
use App\Domain\Resource\Entities\Rewards\StageRandomClearReward;
use App\Domain\Resource\Entities\Rewards\StageSpeedAttackClearReward;
use App\Http\Responses\ResultData\StageCleanupResultData;
use App\Http\Responses\ResultData\StageAbortResultData;
use App\Http\Responses\ResultData\StageContinueAdResultData;
use App\Http\Responses\ResultData\StageContinueResultData;
use App\Http\Responses\ResultData\StageEndResultData;
use App\Http\Responses\ResultData\StageStartResultData;
use Illuminate\Http\JsonResponse;

class StageResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createStartResponse(StageStartResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);

        $result = $this->responseDataFactory->addUsrInGameStatusData($result, $resultData->usrStageStatus);

        return response()->json($result);
    }

    public function createEndResponse(StageEndResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addStageRewardData(
            $result,
            $resultData->stageFirstClearRewards,
            $resultData->stageAlwaysClearRewards,
            $resultData->stageRandomClearRewards,
            $resultData->stageSpeedAttackClearRewards,
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

        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

        $rewards = $resultData->stageFirstClearRewards->concat($resultData->stageAlwaysClearRewards);
        $result = $this->responseDataFactory->addDuplicatedRewardData($result, $rewards);

        $result = $this->responseDataFactory->addUsrArtworkData($result, $resultData->usrArtworks);
        $result = $this->responseDataFactory->addUsrArtworkFragmentData($result, $resultData->usrArtworkFragments);

        $result = $this->responseDataFactory->addUsrEnemyDiscoveryData($result, $resultData->newUsrEnemyDiscoveries);

        $result['oprCampaignIds'] = $resultData->oprCampaignIds->values()->toArray();

        return response()->json($result);
    }

    public function createContinueResponse(StageContinueResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);

        $result = $this->responseDataFactory->addContinueCountData($result, $resultData->usrStageStatusData);

        return response()->json($result);
    }

    public function createContinueAdResponse(StageContinueAdResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addContinueCountData($result, $resultData->usrStageStatusData);
        $result = $this->responseDataFactory->addContinueAdCountData($result, $resultData->usrStageStatusData);

        return response()->json($result);
    }

    public function createAbortResponse(StageAbortResultData $resultData): JsonResponse
    {
        $result = [];

        return response()->json($result);
    }

    public function createCleanupResponse(StageCleanupResultData $resultData): JsonResponse
    {
        $result = [];

        return response()->json($result);
    }
}
