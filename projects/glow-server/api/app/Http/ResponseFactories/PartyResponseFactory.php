<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\PartySaveResultData;
use Illuminate\Http\JsonResponse;

class PartyResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    public function createPartySaveResponse(PartySaveResultData $resultData): JsonResponse
    {
        $result = $this->responseDataFactory->addUsrPartyData([], $resultData->usrParties, true);
        return response()->json($result);
    }
}
