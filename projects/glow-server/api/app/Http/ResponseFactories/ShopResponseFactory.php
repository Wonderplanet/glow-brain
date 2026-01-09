<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\GetStoreInfoResultData;
use App\Http\Responses\ResultData\ShopPurchaseHistoryResultData;
use App\Http\Responses\ResultData\ShopPurchaseResultData;
use App\Http\Responses\ResultData\ShopSetStoreInfoResultData;
use App\Http\Responses\ResultData\ShopTradePackResultData;
use App\Http\Responses\ResultData\ShopTradeShopItemResultData;
use Illuminate\Http\JsonResponse;

class ShopResponseFactory
{
    use CurrencySummaryResponderTrait;
    use StoreInfoResponderTrait;

    public function __construct(
        private ResponseDataFactory $responseDataFactory,
    ) {
    }

    /**
     * /api/shop/trade_shop_itemのレスポンスを生成する
     * @param ShopTradeShopItemResultData $resultData
     * @return JsonResponse
     */
    public function createShopTradeShopItemResponse(
        ShopTradeShopItemResultData $resultData
    ): JsonResponse {
        $result = $this->responseDataFactory->addUsrShopItemsData(
            [],
            $resultData->usrShopItems
        );

        $result = $this->responseDataFactory->addUsrParameterData(
            $result,
            $resultData->usrUserParameter
        );

        $result = $this->responseDataFactory->addUsrItemData(
            $result,
            $resultData->usrItems,
            true
        );

        return response()->json($result);
    }

    public function createTradePackResponse(ShopTradePackResultData $resultData): JsonResponse
    {
        $result = $this->responseDataFactory->addUsrParameterData([], $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrTradePackData($result, $resultData->usrTradePacks);

        // リワードデータを追加
        $result = $this->responseDataFactory->addShopPurchaseRewardData(
            $result,
            $resultData->rewards
        );

        return response()->json($result);
    }

    public function createPurchaseResponse(ShopPurchaseResultData $resultData): JsonResponse
    {
        $result = $this->responseDataFactory->addUsrStoreProductData([], $resultData->usrStoreProduct);

        $result = $this->responseDataFactory->addUsrShopPassData(
            $result,
            ($resultData->usrShopPass !== null ? collect([$resultData->usrShopPass]) : collect()),
            false
        );

        $result = $this->responseDataFactory->addShopPassRewardData(
            $result,
            $resultData->shopPassRewards,
        );

        // リワードデータを追加
        $result = $this->responseDataFactory->addShopPurchaseRewardData(
            $result,
            $resultData->rewards
        );

        $result = $this->responseDataFactory->addUsrParameterData($result, $resultData->usrUserParameter);
        $result = $this->responseDataFactory->addUsrItemData($result, $resultData->usrItems, true);
        $result = $this->responseDataFactory->addUsrUnitData($result, $resultData->usrUnits, true);
        $result = $this->responseDataFactory->addUsrTradePackData($result, $resultData->usrTradePacks);
        $result = $this->responseDataFactory->addUsrStoreInfoData($result, $resultData->usrStoreInfo);

        return response()->json($result);
    }

    public function createPurchaseHistoryResponse(ShopPurchaseHistoryResultData $resultData): JsonResponse
    {
        $result = $this->responseDataFactory->addShopCurrencyPurchaseData([], $resultData->currencyPurchases);
        return response()->json($result);
    }

    /**
     *
     * @param array{product_sub_id: string, product_id: string} $data
     * @return JsonResponse
     */
    public function createAllowanceResponse(array $data): JsonResponse
    {
        // 互換性のため、opr_product_idにもproduct_sub_idを入れておく
        $result = [
            'oprProductId' => $data['product_sub_id'],
            'productSubId' => $data['product_sub_id'],
            'productId' => $data['product_id'],
        ];

        return response()->json($result);
    }

    /**
     *
     * @param string $billingPlatform
     * @param array{product_sub_id: string, product_id: string, currency_summary: \WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity} $data
     * @return JsonResponse
     */
    public function createBuyResponse(string $billingPlatform, array $data): JsonResponse
    {
        $result = [
            'product_id' => $data['product_id'],
            'product_sub_id' => $data['product_sub_id'],
            'data' => [
                'currency_summary' => $this->createCurrencySummaryResponse($billingPlatform, $data['currency_summary'])
            ]
        ];

        return response()->json($result);
    }

    /**
     * 新しいget_store_info APIのレスポンスを生成する
     *
     * @param GetStoreInfoResultData $resultData
     * @return JsonResponse
     */
    public function createGetStoreInfoResponse(GetStoreInfoResultData $resultData): JsonResponse
    {
        $result = $this->responseDataFactory->addUsrStoreInfoData([], $resultData->usrStoreInfo);

        return response()->json($result);
    }

    public function createSetStoreInfoResponse(ShopSetStoreInfoResultData $resultData): JsonResponse
    {
        $result = $this->responseDataFactory->addUsrStoreInfoData([], $resultData->usrStoreInfo);

        return response()->json($result);
    }

}
