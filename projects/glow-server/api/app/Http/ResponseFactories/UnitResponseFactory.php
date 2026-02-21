<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\UnitGradeUpResultData;
use App\Http\Responses\ResultData\UnitLevelUpResultData;
use App\Http\Responses\ResultData\UnitRankUpResultData;
use App\Http\Responses\ResultData\UnitReceiveGradeUpRewardResultData;
use App\Http\Responses\ResultData\UnitResetLevelResultData;
use Illuminate\Http\JsonResponse;

class UnitResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createUnitGradeUpResponse(UnitGradeUpResultData $resultData): JsonResponse
    {
        $result = [];
        $unit = $resultData->usrUnit;
        $result = $this->responseDataFactory->addUsrUnitData($result, collect([$unit]), false);
        
        $usrItems = $resultData->usrItems;
        $result = $this->responseDataFactory->addUsrItemData($result, $usrItems, true);
        
        $usrArtworks = $resultData->usrArtworks;
        $result = $this->responseDataFactory->addUsrArtworkData($result, $usrArtworks);
        
        $usrArtworkFragments = $resultData->usrArtworkFragments;
        $result = $this->responseDataFactory->addUsrArtworkFragmentData($result, $usrArtworkFragments);
        
        $result = $this->responseDataFactory->addUnitGradeUpRewardData($result, $resultData->unitGradeUpRewards);
        
        return response()->json($result);
    }

    public function createUnitLevelUpResponse(UnitLevelUpResultData $resultData): JsonResponse
    {
        $result = [];
        $unit = $resultData->usrUnit;
        $result = $this->responseDataFactory->addUsrUnitData($result, collect([$unit]), false);

        $usrParam = $resultData->usrUserParameter;
        $result = $this->responseDataFactory->addUsrParameterData($result, $usrParam);

        return response()->json($result);
    }

    public function createRankUpResponse(UnitRankUpResultData $resultData): JsonResponse
    {
        $result = [];
        $unit = $resultData->usrUnit;
        $result = $this->responseDataFactory->addUsrUnitData($result, collect([$unit]), false);
        $usrItems = $resultData->usrItems;
        $result = $this->responseDataFactory->addUsrItemData($result, $usrItems, true);
        return response()->json($result);
    }

    public function createUnitResetLevelResponse(UnitResetLevelResultData $resultData): JsonResponse
    {
        $result = [];
        $unit = $resultData->usrUnit;
        $result = $this->responseDataFactory->addUsrUnitData($result, collect([$unit]), false);

        $usrItems = $resultData->usrItems;
        $result = $this->responseDataFactory->addUsrItemData($result, $usrItems, true);

        $usrParam = $resultData->usrUserParameter;
        $result = $this->responseDataFactory->addUsrParameterData($result, $usrParam);

        return response()->json($result);
    }

    public function createReceiveGradeUpRewardResponse(UnitReceiveGradeUpRewardResultData $resultData): JsonResponse
    {
        $result = [];
        $unit = $resultData->usrUnit;
        $result = $this->responseDataFactory->addUsrUnitData($result, collect([$unit]), false);

        $usrArtworks = $resultData->usrArtworks;
        $result = $this->responseDataFactory->addUsrArtworkData($result, $usrArtworks);

        $usrArtworkFragments = $resultData->usrArtworkFragments;
        $result = $this->responseDataFactory->addUsrArtworkFragmentData($result, $usrArtworkFragments);

        $result = $this->responseDataFactory->addUnitGradeUpRewardData($result, $resultData->unitGradeUpRewards);

        return response()->json($result);
    }
}
