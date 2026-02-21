<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Services\WebStorePurchaseNotificationCacheService;
use App\Domain\Shop\Services\WebStorePurchaseService;
use App\Domain\Shop\Services\WebStoreTransactionService;
use App\Domain\Shop\Services\WebStoreUserService;
use App\Domain\User\Constants\UserConstant;
use App\Infrastructure\UsrModelManager;
use Illuminate\Support\Facades\Log;

/**
 * WebStore W5: 注文支払い成功
 *
 * notification_type: order_paid
 */
class WebStoreOrderPaidUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly Clock $clock,
        private readonly WebStorePurchaseNotificationCacheService $webStorePurchaseNotificationCacheService,
        private readonly WebStorePurchaseService $webStorePurchaseService,
        private readonly WebStoreTransactionService $webStoreTransactionService,
        private readonly WebStoreUserService $webStoreUserService,
        private readonly UsrModelManager $usrModelManager,
    ) {
    }

    /**
     * W5: 注文支払い成功
     *
     * @param string      $usrUserId ユーザーID
     * @param int         $orderId 注文ID
     * @param string|null $invoiceId インボイスID
     * @param string|null $currencyCode 通貨コード
     * @param int         $orderAmount 注文金額
     * @param string      $orderMode 注文モード
     * @param array<string, mixed> $items アイテム配列
     * @param string      $transactionId トランザクションID
     * @param string|null $clientIp クライアントIP
     * @throws GameException
     */
    public function exec(
        string $usrUserId,
        int $orderId,
        ?string $invoiceId,
        ?string $currencyCode,
        int $orderAmount,
        string $orderMode,
        array $items,
        string $transactionId,
        ?string $clientIp
    ): void {
        // 本番環境でのサンドボックス決済チェック（order.modeが"sandbox"の場合）
        $isSandboxMode = $orderMode === WebStoreConstant::SANDBOX;
        $this->webStoreUserService->checkSandboxInProduction($isSandboxMode);

        $now = $this->clock->now();

        // トランザクション完了済みチェック（存在確認も兼ねる、W5の重複リクエスト対策）
        if ($this->webStoreTransactionService->isTransactionCompleted($transactionId)) {
            Log::error('WebStore order_paid notification received for already completed transaction', [
                'usr_user_id' => $usrUserId,
                'transaction_id' => $transactionId,
            ]);
            return;
        }

        // べき等性チェック
        if ($this->webStorePurchaseService->isDuplicateOrder($orderId)) {
            return ;
        }

        // アクセストークン認証ができないのでここでユーザーをセットする
        $this->usrModelManager->setUsrUserId($usrUserId);

        // 配列をEntityコレクションに変換
        $webStoreItemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);

        // リソース所持上限チェック
        $this->webStorePurchaseService->validatePurchaseItems($webStoreItemEntities, $usrUserId, $now);

        // トランザクション内でアイテム付与と履歴保存を実行
        $productSubIds = $this->commitUserAndLogDataChanges(function () use (
            $usrUserId,
            $orderId,
            $invoiceId,
            $currencyCode,
            $orderAmount,
            $orderMode,
            $webStoreItemEntities,
            $transactionId,
            $now
        ) {
            return $this->webStorePurchaseService->processOrder(
                $usrUserId,
                $orderId,
                $invoiceId,
                $currencyCode,
                $orderMode,
                $webStoreItemEntities,
                $transactionId,
                UserConstant::PLATFORM_WEBSTORE,
                $now
            );
        });

        // Adjustイベント送信
        $this->webStorePurchaseService->sendAdjustEvent(
            $usrUserId,
            $currencyCode,
            $orderAmount,
            $orderMode,
            $clientIp,
            $transactionId,
            $now
        );

        // アプリ通知用にキャッシュに一括保存
        $this->webStorePurchaseNotificationCacheService->addPurchaseNotifications($usrUserId, $productSubIds);
    }
}
