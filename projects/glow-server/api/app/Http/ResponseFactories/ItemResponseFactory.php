<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\ItemConsumeResultData;
use App\Http\Responses\ResultData\ItemExchangeSelectItemResultData;
use Illuminate\Http\JsonResponse;

class ItemResponseFactory
{
    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }


    /**
     * @param ItemConsumeResultData $itemConsumeResultData
     * @return JsonResponse
     */
    public function createItemConsumeResponse(ItemConsumeResultData $itemConsumeResultData): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrParameterData($result, $itemConsumeResultData->usrUserParameter);

        $result = $this->responseDataFactory->addUsrItemData($result, $itemConsumeResultData->usrItems, true);

        $rewards = $itemConsumeResultData->itemRewards->merge($itemConsumeResultData->itemTradeRewards);
        $result = $this->responseDataFactory->addItemRewardData($result, $rewards);

        $result = $this->responseDataFactory->addUsrItemTradeData(
            $result,
            collect([$itemConsumeResultData->usrItemTrade]),
            false,
        );

        return response()->json($result);
    }

    /**
     * @param ItemExchangeSelectItemResultData $itemConsumeResultData
     * @return JsonResponse
     */
    public function createItemExchangeSelectItemResponse(
        ItemExchangeSelectItemResultData $itemConsumeResultData
    ): JsonResponse
    {
        $result = [];

        $result = $this->responseDataFactory->addUsrItemData($result, $itemConsumeResultData->usrItems, true);

        $result = $this->responseDataFactory->addItemRewardData($result, $itemConsumeResultData->itemRewards);

        $result = $this->responseDataFactory->addDuplicatedRewardData($result, $itemConsumeResultData->itemRewards);

        return response()->json($result);
    }
}
