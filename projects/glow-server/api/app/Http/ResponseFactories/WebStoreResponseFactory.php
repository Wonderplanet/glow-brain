<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use App\Http\Responses\ResultData\ShopWebstorePaymentValidationResultData;
use App\Http\Responses\ResultData\ShopWebstoreUserValidationResultData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * WebStore(Xsolla)ウェブフック用のレスポンスを生成するFactory
 */
class WebStoreResponseFactory
{
    /**
     * W1: ユーザー情報取得のレスポンスを生成
     *
     * @param ShopWebstoreUserValidationResultData $resultData
     * @return JsonResponse
     */
    public function createUserValidationResponse(ShopWebstoreUserValidationResultData $resultData): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $resultData->id,
                'internal_id' => $resultData->internalId,
                'name' => $resultData->name,
                'level' => $resultData->level,
                'birthday' => $resultData->birthday,
                'birthday_month' => $resultData->birthdayMonth,
                'country' => $resultData->country,
            ],
        ]);
    }

    /**
     * W2: 決済事前確認のレスポンスを生成
     *
     * @param ShopWebstorePaymentValidationResultData $resultData
     * @return JsonResponse
     */
    public function createPaymentValidationResponse(ShopWebstorePaymentValidationResultData $resultData): JsonResponse
    {
        return response()->json([
            'transaction_id' => $resultData->transactionId,
        ]);
    }

    /**
     * 空のJSONレスポンスを返す
     * W3, W4, W5で使用
     *
     * @param int $status
     * @return JsonResponse
     */
    public function createEmptySuccessResponse(int $status = Response::HTTP_NO_CONTENT): JsonResponse
    {
        return response()->json([], $status);
    }
}
