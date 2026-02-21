<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\UseCases\AllowanceUseCase;
use App\Domain\Shop\UseCases\GetStoreInfoUseCase;
use App\Domain\Shop\UseCases\SetStoreInfoUseCase;
use App\Domain\Shop\UseCases\ShopPurchaseHistoryUseCase;
use App\Domain\Shop\UseCases\ShopPurchaseUseCase;
use App\Domain\Shop\UseCases\ShopTradePackUseCase;
use App\Domain\Shop\UseCases\ShopTradeShopItemUseCase;
use App\Domain\Shop\UseCases\WebStoreOrderPaidUseCase;
use App\Domain\Shop\UseCases\WebStorePaymentUseCase;
use App\Domain\Shop\UseCases\WebStorePaymentValidationUseCase;
use App\Domain\Shop\UseCases\WebStoreUserValidationUseCase;
use App\Domain\Shop\UseCases\WebStoreUserVerificationUseCase;
use App\Http\ResponseFactories\ShopResponseFactory;
use App\Http\ResponseFactories\WebStoreResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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

    /**
     * WebStore(Xsolla)ウェブフック受信エンドポイント
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function webstore(Request $request, WebStoreResponseFactory $responseFactory): JsonResponse
    {
        $notificationType = $request->input('notification_type');

        if (is_null($notificationType)) {
            return $this->createErrorResponse('INVALID_REQUEST', 'notification_type is required', 400);
        }

        try {
            return match ($notificationType) {
                'web_store_user_validation' => $this->handleUserValidation($request, $responseFactory),
                'web_store_payment_validation' => $this->handlePaymentValidation($request, $responseFactory),
                'user_validation' => $this->handleUserVerification($request, $responseFactory),
                'payment' => $this->handlePayment($request, $responseFactory),
                'order_paid' => $this->handleOrderPaid($request, $responseFactory),
                default => $this->createNotImplementedResponse($notificationType),
            };
        } catch (GameException $e) {
            Log::warning('WebStore webhook business error', [
                'notification_type' => $notificationType,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);

            $message = $this->getErrorMessage($e->getCode());
            return $this->createErrorResponse($e->getCode(), $message, 400);
        } catch (\Exception $e) {
            // 予期しないエラーは500で返す
            Log::error('WebStore webhook unexpected error', [
                'notification_type' => $notificationType,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse('WEBSTORE_INTERNAL_ERROR', 'Internal error occurred.', 500);
        }
    }

    /**
     * W1: ユーザー情報取得
     */
    private function handleUserValidation(Request $request, WebStoreResponseFactory $responseFactory): JsonResponse
    {
        $validated = $request->validate([
            'user' => 'required|array',
            'user.user_id' => 'required|string',
        ]);

        $bnUserId = $validated['user']['user_id'];

        $useCase = app(WebStoreUserValidationUseCase::class);
        $response = $useCase->exec($bnUserId);
        return $responseFactory->createUserValidationResponse($response);
    }

    /**
     * W2: 決済事前確認
     */
    private function handlePaymentValidation(Request $request, WebStoreResponseFactory $responseFactory): JsonResponse
    {
        $validated = $request->validate([
            'custom_parameters' => 'required|array',
            'custom_parameters.internal_id' => 'required|string',
            'user' => 'required|array',
            'user.birthday' => 'required',
            'purchase' => 'required|array',
            'purchase.items' => 'required|array',
            'is_sandbox' => 'sometimes|boolean',
        ]);

        $usrUserId = $validated['custom_parameters']['internal_id'];
        $userBirthday = (int) $validated['user']['birthday'];
        $items = $validated['purchase']['items'];
        $isSandbox = $validated['is_sandbox'] ?? false;

        $useCase = app()->make(WebStorePaymentValidationUseCase::class);
        $response = $useCase->exec($usrUserId, $userBirthday, $items, $isSandbox);
        return $responseFactory->createPaymentValidationResponse($response);
    }

    /**
     * W3: ユーザー検証
     */
    private function handleUserVerification(Request $request, WebStoreResponseFactory $responseFactory): JsonResponse
    {
        $validated = $request->validate([
            'user' => 'required|array',
            'user.id' => 'required|string',
            'custom_parameters' => 'required|array',
            'custom_parameters.internal_id' => 'required|string',
        ]);

        $usrUserId = $validated['custom_parameters']['internal_id'];

        $useCase = app()->make(WebStoreUserVerificationUseCase::class);
        $useCase->exec($usrUserId);
        return $responseFactory->createEmptySuccessResponse();
    }

    /**
     * W4: 支払い通知
     */
    private function handlePayment(Request $request, WebStoreResponseFactory $responseFactory): JsonResponse
    {
        $validated = $request->validate([
            'transaction' => 'sometimes|array',
            'transaction.dry_run' => 'sometimes|integer',
            'custom_parameters' => 'required|array',
            'custom_parameters.transaction_id' => 'required|string',
            'custom_parameters.internal_id' => 'required|string',
            'purchase' => 'sometimes|array',
            'purchase.order' => 'sometimes|array',
            'purchase.order.lineitems' => 'sometimes|array',
        ]);

        $usrUserId = $validated['custom_parameters']['internal_id'];
        $transactionId = $validated['custom_parameters']['transaction_id'];
        $dryRun = $validated['transaction']['dry_run'] ?? 0;
        $isSandbox = $dryRun === 1;
        $items = $validated['purchase']['order']['lineitems'] ?? [];

        $useCase = app()->make(WebStorePaymentUseCase::class);
        $useCase->exec($usrUserId, $items, $transactionId, $isSandbox, $dryRun);
        return $responseFactory->createEmptySuccessResponse();
    }

    /**
     * W5: 注文支払い成功
     */
    private function handleOrderPaid(Request $request, WebStoreResponseFactory $responseFactory): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.id' => 'required|integer',
            'order.invoice_id' => 'sometimes|string|nullable',
            'order.currency' => 'sometimes|string|nullable',
            'order.amount' => 'required|numeric',
            'order.mode' => 'required|string',
            'items' => 'required|array',
            'custom_parameters' => 'required|array',
            'custom_parameters.internal_id' => 'required|string',
            'custom_parameters.transaction_id' => 'required|string',
            'custom_parameters.user_ip' => 'sometimes|string|nullable',
        ]);

        $usrUserId = $validated['custom_parameters']['internal_id'];
        $orderId = (int) $validated['order']['id'];
        $invoiceId = $validated['order']['invoice_id'] ?? null;
        $currencyCode = $validated['order']['currency'] ?? null;
        $orderAmount = (int) $validated['order']['amount'];
        $orderMode = $validated['order']['mode'];
        $items = $validated['items'];
        $transactionId = $validated['custom_parameters']['transaction_id'];
        $clientIp = $validated['custom_parameters']['user_ip'] ?? null;

        $useCase = app()->make(WebStoreOrderPaidUseCase::class);
        $useCase->exec(
            $usrUserId,
            $orderId,
            $invoiceId,
            $currencyCode,
            $orderAmount,
            $orderMode,
            $items,
            $transactionId,
            $clientIp
        );
        return $responseFactory->createEmptySuccessResponse(Response::HTTP_OK);
    }

    private function getErrorMessage(mixed $code): string
    {
        // 特定のエラーコードの場合はエラーメッセージを返す
        return match ($code) {
            // リソース所持上限
            ErrorCode::WEBSTORE_RESOURCE_POSSESSION_LIMIT_EXCEEDED => WebStoreConstant::ERROR_RESOURCE_POSSESSION_LIMIT_EXCEEDED,
            // 商品が存在しない(マスタがない、期間外)
            ErrorCode::WEBSTORE_PRODUCT_NOT_FOUND => WebStoreConstant::ERROR_PRODUCT_NOT_FOUND,
            // ユーザーBAN関連
            ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_CHEATING => WebStoreConstant::ERROR_USER_ACCOUNT_BAN,
            ErrorCode::USER_ACCOUNT_BAN_TEMPORARY_BY_DETECTED_ANOMALY => WebStoreConstant::ERROR_USER_ACCOUNT_BAN,
            ErrorCode::USER_ACCOUNT_BAN_PERMANENT => WebStoreConstant::ERROR_USER_ACCOUNT_BAN,
            ErrorCode::USER_ACCOUNT_DELETED => WebStoreConstant::ERROR_USER_ACCOUNT_BAN,
            ErrorCode::USER_ACCOUNT_REFUNDING => WebStoreConstant::ERROR_USER_ACCOUNT_BAN,
            default => '',
        };
    }

    /**
     * エラーレスポンスを生成
     */
    private function createErrorResponse(string|int $code, string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $statusCode);
    }

    /**
     * 未対応エラーレスポンスを生成
     */
    private function createNotImplementedResponse(string $notificationType): JsonResponse
    {
        return $this->createErrorResponse(
            'UNSUPPORTED_NOTIFICATION_TYPE',
            "Notification type '$notificationType' is not supported",
            500
        );
    }
}
