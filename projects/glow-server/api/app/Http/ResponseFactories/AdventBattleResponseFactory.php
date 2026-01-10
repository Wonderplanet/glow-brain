<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\AdventBattleAbortResultData;
use App\Http\Responses\ResultData\AdventBattleCleanupResultData;
use App\Http\Responses\ResultData\AdventBattleEndResultData;
use App\Http\Responses\ResultData\AdventBattleInfoResultData;
use App\Http\Responses\ResultData\AdventBattleRankingResultData;
use App\Http\Responses\ResultData\AdventBattleStartResultData;
use App\Http\Responses\ResultData\AdventBattleTopResultData;
use Illuminate\Http\JsonResponse;

readonly class AdventBattleResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createTopResponse(
        AdventBattleTopResultData $resultData
    ): JsonResponse {
        $result = [];
        $result = $this->responseDataFactory->addAdventBattleRaidRewardData(
            $result,
            $resultData->sentRaidTotalScoreRewards,
        );
        $result = $this->responseDataFactory->addAdventBattleMaxScoreRewardData(
            $result,
            $resultData->sentMaxScoreRewards,
        );
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrEmblemData($result, $resultData->usrEmblems);

        return response()->json($result);
    }

    public function createStartResponse(AdventBattleStartResultData $resultData): JsonResponse
    {
        $result = [];

        return response()->json($result);
    }

    public function createEndResponse(AdventBattleEndResultData $resultData): JsonResponse
    {
        $result = [
            'totalDamage' => $resultData->allUserTotalScore,
        ];
        $result = $this->responseDataFactory->addUsrAdventBattleData(
            $result,
            collect([$resultData->usrAdventBattle]),
            false
        );
        $result = $this->responseDataFactory->addUsrEnemyDiscoveryData($result, $resultData->newUsrEnemyDiscoveries);

        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrParameterData);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addAdventBattleClearRewardData(
            $result,
            $resultData->adventBattleFirstClearRewards,
            $resultData->adventBattleAlwaysClearRewards,
            $resultData->adventBattleRandomClearRewards,
        );
        $result = $this->responseDataFactory->addAdventBattleDropRewardData(
            $result,
            $resultData->adventBattleDropRewards,
        );
        $result = $this->responseDataFactory->addAdventBattleRankRewardData(
            $result,
            $resultData->adventBattleRankRewards,
        );
        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

        return response()->json($result);
    }

    public function createAbortResponse(AdventBattleAbortResultData $resultData): JsonResponse
    {
        $result = [
            'totalDamage' => $resultData->allUserTotalScore,
        ];

        return response()->json($result);
    }

    public function createRankingResponse(AdventBattleRankingResultData $resultData): JsonResponse
    {
        return response()->json($resultData->adventBattleRankingData->formatToResponse());
    }

    public function createInfoResponse(AdventBattleInfoResultData $resultData): JsonResponse
    {
        $result = [
            'adventBattleResult' => $resultData->adventBattleResultData?->formatToResponse() ?? null,
        ];
        return response()->json($result);
    }

    public function createCleanupResponse(AdventBattleCleanupResultData $resultData): JsonResponse
    {
        $result = [];

        return response()->json($result);
    }
}
