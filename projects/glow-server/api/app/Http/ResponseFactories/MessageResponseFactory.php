<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\MessageOpenResultData;
use App\Http\Responses\ResultData\MessageReceiveResultData;
use App\Http\Responses\ResultData\MessageUpdateAndFetchResultData;
use Illuminate\Http\JsonResponse;

class MessageResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createUpdateAndFetchResponse(MessageUpdateAndFetchResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addMessageData(
            $result,
            $resultData->messageDataList,
        );

        return response()->json($result);
    }

    public function createOpenedResponse(MessageOpenResultData $resultData): JsonResponse
    {
        $result = [];

        return response()->json($result);
    }

    public function createReceivedResponse(MessageReceiveResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addMessageRewardData(
            $result,
            $resultData->messageRewards,
        );
        $result = $this->responseDataFactory->addDuplicatedRewardData(
            $result,
            $resultData->messageRewards
        );
        $result = $this->responseDataFactory->addUsrUnitData(
            $result,
            $resultData->usrUnits,
            isMulti: true,
        );
        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            isMulti: true,
        );
        $result = $this->responseDataFactory->addUsrEmblemData(
            $result,
            $resultData->usrEmblems,
        );
        $result = $this->responseDataFactory->addUsrParameterData(
            $result,
            $resultData->usrUserParameter,
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
}
