<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\OutpostChangeArtworkResultData;
use App\Http\Responses\ResultData\OutpostEnhanceResultData;
use Illuminate\Http\JsonResponse;

class OutpostResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createEnhanceResponse(OutpostEnhanceResultData $resultData): JsonResponse
    {
        $result = [
            'beforeLevel' => $resultData->beforeLevel,
            'afterLevel' => $resultData->afterLevel,
        ];
        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);

        return response()->json($result);
    }

    public function createChangeArtworkResponse(OutpostChangeArtworkResultData $resultData): JsonResponse
    {
        $result = [];
        $result = $this->responseDataFactory->addUsrOutpostData($result, collect([$resultData->usrOutpost]), false);

        return response()->json($result);
    }
}
