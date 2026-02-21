<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\GachaDrawResultData;
use App\Http\Responses\ResultData\GachaHistoryResultData;
use App\Http\Responses\ResultData\GachaPrizeResultData;
use Illuminate\Http\JsonResponse;

class GachaResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createPrizeResponse(GachaPrizeResultData $resultData): JsonResponse
    {
        $response = $resultData->gachaProbabilityData->formatToResponse();

        // ステップアップガシャの場合、stepupGachaPrizesを追加
        if ($resultData->stepupGachaPrizes->isNotEmpty()) {
            $response['stepupGachaPrizes'] = $resultData->stepupGachaPrizes
                ->map(fn($stepPrize) => $stepPrize->formatToResponse())
                ->values()
                ->toArray();
        } else {
            $response['stepupGachaPrizes'] = null;
        }

        return response()->json($response);
    }

    public function createDrawResponse(GachaDrawResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addGachaResultData($result, $resultData->gachaRewards);
        $result = $this->responseDataFactory->addStepRewardsData($result, $resultData->stepRewards);
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);
        $result = $this->responseDataFactory->addUsrGachaUpperData($result, $resultData->usrGachaUppers, true);
        $result = $this->responseDataFactory->addUsrGachaData($result, collect([$resultData->usrGacha]), false);

        return response()->json($result);
    }

    public function createHistoryResponse(GachaHistoryResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addGachaHistoryData($result, $resultData->gachaHistories);
        return response()->json($result);
    }
}
