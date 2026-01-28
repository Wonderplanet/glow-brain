<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\PvpAbortResultData;
use App\Http\Responses\ResultData\PvpChangeOpponentResultData;
use App\Http\Responses\ResultData\PvpCleanupResultData;
use App\Http\Responses\ResultData\PvpEndResultData;
use App\Http\Responses\ResultData\PvpRankingResultData;
use App\Http\Responses\ResultData\PvpResumeResultData;
use App\Http\Responses\ResultData\PvpStartResultData;
use App\Http\Responses\ResultData\PvpTopResultData;
use Illuminate\Http\JsonResponse;

class PvpResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createPvpTopResponse(
        PvpTopResultData $resultData
    ): JsonResponse {
        $result = [];

        $result = $this->responseDataFactory->addPvpHeldStatusData(
            $result,
            $resultData->pvpHeldStatusData
        );

        $result = $this->responseDataFactory->addUsrPvpStatusData(
            $result,
            $resultData->usrPvpStatusData
        );

        $result = $this->responseDataFactory->addOpponentSelectStatusData(
            $result,
            $resultData->opponentSelectStatusResponses,
            true
        );

        $result = $this->responseDataFactory->addPvpPreviousSeasonResultData(
            $result,
            $resultData->pvpPreviousSeasonResultData
        );

        $result = $this->responseDataFactory->addIsViewableRanking(
            $result,
            $resultData->isViewableRanking
        );

        return response()->json($result);
    }

    public function createPvpChangeOpponentResponse(
        PvpChangeOpponentResultData $resultData
    ): JsonResponse {
        $result = [];
        $result = $this->responseDataFactory->addOpponentSelectStatusData(
           $result,
           $resultData->opponentSelectStatusResponses,
           true,
        );

        return response()->json($result);
    }

    public function createPvpStartResponse(
        PvpStartResultData $resultData
    ): JsonResponse {
        $result = [];

        $result = $this->responseDataFactory->addOpponentPvpStatusData(
            $result,
            $resultData->getOpponentPvpStatus()
        );

        return response()->json($result);
    }

    public function createPvpEndResponse(
        PvpEndResultData $resultData
    ): JsonResponse {
        $result = [];

        $result = $this->responseDataFactory->addUsrPvpStatusData(
            $result,
            $resultData->usrPvpStatus
        );
        $result = $this->responseDataFactory->addUsrParameterData(
            $result,
            $resultData->usrParameterData
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

        $result = $this->responseDataFactory->addPvpEndResultBonusPointData(
            $result,
            $resultData->pvpResultPoints
        );

        $result = $this->responseDataFactory->addPvpTotalScoreRewardData(
            $result,
            $resultData->pvpTotalScoreRewards
        );

        return response()->json($result);
    }

    public function createPvpRankingResponse(
        PvpRankingResultData $resultData
    ): JsonResponse {
        return response()->json($resultData->pvpRankingData->formatToResponse());
    }

    public function createPvpResumeResponse(
        PvpResumeResultData $resultData
    ): JsonResponse {
        $result = [];

        $result = $this->responseDataFactory->addOpponentSelectStatusData(
            $result,
            collect([$resultData->getOpponentSelectStatusResponse()])
        );

        $result = $this->responseDataFactory->addOpponentPvpStatusData(
            $result,
            $resultData->getOpponentPvpStatus()
        );

        return response()->json($result);
    }

    public function createPvpAbortResponse(
        PvpAbortResultData $resultData
    ): JsonResponse {
        $result = [];

        $result = $this->responseDataFactory->addUsrPvpStatusData(
            $result,
            $resultData->usrPvpStatus
        );

        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            true
        );

        return response()->json($result);
    }

    public function createPvpCleanupResponse(PvpCleanupResultData $resultData): JsonResponse
    {
        $result = [];

        return response()->json($result);
    }
}
