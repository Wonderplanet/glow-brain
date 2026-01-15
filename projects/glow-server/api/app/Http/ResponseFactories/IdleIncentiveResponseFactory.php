<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\IdleIncentiveQuickReceiveByAdResultData;
use App\Http\Responses\ResultData\IdleIncentiveQuickReceiveByDiamondResultData;
use App\Http\Responses\ResultData\IdleIncentiveReceiveResultData;
use Illuminate\Http\JsonResponse;

class IdleIncentiveResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createReceiveResponse(IdleIncentiveReceiveResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addReceiveRewardData($result, $resultData->idleIncentiveRewards);
        $result = $this->responseDataFactory->addUsrIdleIncentiveData($result, $resultData->usrIdleIncentive);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

        return response()->json($result);
    }

    public function createQuickReceiveByDiamondResponse(IdleIncentiveQuickReceiveByDiamondResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addReceiveRewardData($result, $resultData->idleIncentiveRewards);
        $result = $this->responseDataFactory->addUsrIdleIncentiveData($result, $resultData->usrIdleIncentive);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

        return response()->json($result);
    }

    public function createQuickReceiveByAdResponse(IdleIncentiveQuickReceiveByAdResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addReceiveRewardData($result, $resultData->idleIncentiveRewards);
        $result = $this->responseDataFactory->addUsrIdleIncentiveData($result, $resultData->usrIdleIncentive);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

        return response()->json($result);
    }
}
