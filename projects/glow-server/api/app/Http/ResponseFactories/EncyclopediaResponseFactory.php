<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\ArtworkGradeUpResultData;
use App\Http\Responses\ResultData\EncyclopediaReceiveRewardResultData;
use App\Http\Responses\ResultData\EncyclopediaReceiveFirstCollectionRewardResultData;
use Illuminate\Http\JsonResponse;

readonly class EncyclopediaResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createReceiveRewardResponse(EncyclopediaReceiveRewardResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrReceivedUnitEncyclopediaRewardData(
            $result,
            $resultData->usrReceivedUnitEncyclopediaRewards
        );
        $result = $this->responseDataFactory->addUnitEncyclopediaRewardData(
            $result,
            $resultData->unitEncyclopediaRewards
        );
        $result = $this->responseDataFactory->addDuplicatedRewardData($result, $resultData->unitEncyclopediaRewards);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUserLevelData($result, $resultData->userLevelUpData);
        $result = $this->responseDataFactory->addUsrConditionPackData($result, $resultData->usrConditionPacks);

        return response()->json($result);
    }

    public function createReceiveFirstCollectionRewardResponse(EncyclopediaReceiveFirstCollectionRewardResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrEmblemData($result, $resultData->usrEmblems);
        $result = $this->responseDataFactory->addUsrArtworkData($result, $resultData->usrArtworks);
        $result = $this->responseDataFactory->addUsrEnemyDiscoveryData($result, $resultData->usrEnemyDiscoveries);
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addEncyclopediaFirstCollectionRewardData(
            $result,
            $resultData->rewards
        );

        return response()->json($result);
    }

    public function createArtworkGradeUpResponse(ArtworkGradeUpResultData $resultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrArtworkData($result, collect([$resultData->usrArtwork]), false);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);

        return response()->json($result);
    }
}
