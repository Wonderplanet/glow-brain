<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\ExchangeTradeResultData;
use Illuminate\Http\JsonResponse;

class ExchangeResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    /**
     * /api/exchange/tradeのレスポンスを生成する
     */
    public function createExchangeTradeResponse(
        ExchangeTradeResultData $resultData
    ): JsonResponse {
        $result = $this->responseDataFactory->addUsrParameterData(
            [],
            $resultData->usrUserParameter
        );

        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            true
        );

        $result = $this->responseDataFactory->addUsrEmblemData(
            $result,
            $resultData->usrEmblems
        );

        $result = $this->responseDataFactory->addUsrUnitData(
            $result,
            $resultData->usrUnits,
            true
        );

        $result = $this->responseDataFactory->addUsrArtworkData(
            $result,
            $resultData->usrArtworks
        );

        $result = $this->responseDataFactory->addUsrArtworkFragmentData(
            $result,
            $resultData->usrArtworkFragments
        );

        $result = $this->responseDataFactory->addUsrExchangeLineupData(
            $result,
            $resultData->usrExchangeLineups
        );

        $result = $this->responseDataFactory->addExchangeTradeRewardData(
            $result,
            $resultData->exchangeTradeRewards
        );

        return response()->json($result);
    }
}
