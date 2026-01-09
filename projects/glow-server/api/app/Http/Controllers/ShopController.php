<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Shop\UseCases\AllowanceUseCase;
use App\Domain\Shop\UseCases\GetStoreInfoUseCase;
use App\Domain\Shop\UseCases\SetStoreInfoUseCase;
use App\Domain\Shop\UseCases\ShopPurchaseHistoryUseCase;
use App\Domain\Shop\UseCases\ShopPurchaseUseCase;
use App\Domain\Shop\UseCases\ShopTradePackUseCase;
use App\Domain\Shop\UseCases\ShopTradeShopItemUseCase;
use App\Http\ResponseFactories\ShopResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private ShopResponseFactory $responseFactory,
    ) {
    }

    /**
     * 購入許可登録
     *
     * @param AllowanceUseCase $useCase
     * @param Request $request
     * @return JsonResponse
     */
    public function allowance(AllowanceUseCase $useCase, Request $request): JsonResponse
    {
        $user = $request->user();

        $platform = $request->getPlatform();
        $billingPlatform = $request->getBillingPlatform();

        $validated = $request->validate([
            'productSubId' => 'required',
            'productId' => 'required',
            'currencyCode' => 'required',
            'price' => 'required',
        ]);

        $data = $useCase(
            $user,
            $platform,
            $billingPlatform,
            $validated['productId'],
            $validated['productSubId'],
            $request->header(System::HEADER_LANGUAGE, Language::Ja->value),
            $validated['currencyCode'],
            (string) $validated['price'],
        );
        return $this->responseFactory->createAllowanceResponse($data);
    }

    /**
     * ショップ情報取得
     *
     * @param GetStoreInfoUseCase $useCase
     * @return JsonResponse
     */
    public function getStoreInfo(GetStoreInfoUseCase $useCase, Request $request): JsonResponse
    {
        $resultData = $useCase($request->user());
        return $this->responseFactory->createGetStoreInfoResponse($resultData);
    }

    /**
     * ショップ情報の設定
     *
     * @param SetStoreInfoUseCase $useCase
     * @return JsonResponse
     */
    public function setStoreInfo(SetStoreInfoUseCase $useCase, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'birthDate' => 'required|integer|date_format:Ymd',
        ]);
        $birthDate = $validated['birthDate'];

        $resultData = $useCase->exec($request->user(), $birthDate);
        return $this->responseFactory->createSetStoreInfoResponse($resultData);
    }

    public function tradeShopItem(
        ShopTradeShopItemUseCase $useCase,
        ShopResponseFactory $responseFactory,
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'mstShopItemId' => 'required',
        ]);
        $mstShopItemId = $validated['mstShopItemId'];
        $platform = (int)$request->header(System::HEADER_PLATFORM);
        $billingPlatform = $request->getBillingPlatform();
        $response = $useCase->exec($request->user(), $mstShopItemId, $platform, $billingPlatform);

        return $responseFactory->createShopTradeShopItemResponse($response);
    }

    /**
     * 課金購入パックと、課金なしで所持リソースと交換するパック で処理を分岐するメソッド
     */
    public function branchTradePack(ShopResponseFactory $responseFactory, Request $request): JsonResponse
    {
        $receipt = $request->input('receipt', null);
        if (StringUtil::isSpecified($receipt)) {
            // レシートがあれば、課金処理(purchase)を呼び出す
            return $this->purchase(
                app()->make(ShopPurchaseUseCase::class),
                $responseFactory,
                $request
            );
        } else {
            // レシートがなければ、課金なしの交換処理を呼びだす
            return $this->tradePack(
                app()->make(ShopTradePackUseCase::class),
                $responseFactory,
                $request
            );
        }
    }

    public function tradePack(
        ShopTradePackUseCase $useCase,
        ShopResponseFactory $responseFactory,
        Request $request
    ): JsonResponse {
        $platform = (int)$request->header(System::HEADER_PLATFORM);
        $billingPlatform = $request->getBillingPlatform();

        $validated = $request->validate([
            'productSubId' => 'required',
        ]);
        $oprProductId = (string)$validated['productSubId'];
        $response = $useCase->exec(
            $request->user(),
            $platform,
            $billingPlatform,
            $oprProductId
        );

        return $responseFactory->createTradePackResponse($response);
    }

    public function purchase(
        ShopPurchaseUseCase $useCase,
        ShopResponseFactory $responseFactory,
        Request $request
    ): JsonResponse {
        $platform = (int)$request->header(System::HEADER_PLATFORM);
        $billingPlatform = $request->getBillingPlatform();

        $validated = $request->validate([
            'productSubId' => 'required',
            'price' => 'required',
            'rawPriceString' => 'required',
            'currencyCode' => 'required',
            'receipt' => 'required',
        ]);

        $oprProductId = $validated['productSubId'];
        // priceは小数値で送られてくることも想定されるため、stringにキャストする
        $purchasePrice = (string)$validated['price'];
        $rawPriceString = $validated['rawPriceString'];
        $currencyCode = $validated['currencyCode'];
        $receipt = $validated['receipt'];

        $response = $useCase->exec(
            $request->user(),
            $platform,
            $billingPlatform,
            $oprProductId,
            $purchasePrice,
            $rawPriceString,
            $currencyCode,
            $receipt,
            $request->header(System::HEADER_LANGUAGE, Language::Ja->value),
        );

        return $responseFactory->createPurchaseResponse($response);
    }

    public function purchaseHistory(
        ShopPurchaseHistoryUseCase $useCase,
        ShopResponseFactory $responseFactory,
        Request $request,
    ): JsonResponse {
        $billingPlatform = $request->getBillingPlatform();
        $resultData = $useCase->exec($request->user(), $billingPlatform);

        return $responseFactory->createPurchaseHistoryResponse($resultData);
    }
}
