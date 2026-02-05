<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Shop\Services\WebStorePurchaseService;
use App\Domain\Shop\Services\WebStoreTransactionService;
use App\Domain\Shop\Services\WebStoreUserService;
use App\Http\Responses\ResultData\ShopWebstorePaymentValidationResultData;
use App\Infrastructure\UsrModelManager;

/**
 * WebStore W2: 決済事前確認
 *
 * notification_type: web_store_payment_validation
 */
class WebStorePaymentValidationUseCase
{
    use UseCaseTrait;

    public function __construct(
        private readonly Clock $clock,
        private readonly WebStoreUserService $webStoreUserService,
        private readonly WebStorePurchaseService $webStorePurchaseService,
        private readonly WebStoreTransactionService $webStoreTransactionService,
        private readonly UsrModelManager $usrModelManager,
    ) {
    }

    /**
     * W2: 決済事前確認
     *
     * @param string $usrUserId ユーザーID
     * @param int    $userBirthday ユーザー生年月日
     * @param array<string, mixed> $items 購入アイテム（配列）
     * @param bool   $isSandbox サンドボックスモードか
     * @return ShopWebstorePaymentValidationResultData
     * @throws GameException
     */
    public function exec(
        string $usrUserId,
        int $userBirthday,
        array $items,
        bool $isSandbox
    ): ShopWebstorePaymentValidationResultData {
        $now = $this->clock->now();

        // アクセストークン認証ができないのでここでユーザーをセットする
        $this->usrModelManager->setUsrUserId($usrUserId);

        // 0. 配列をEntityコレクションに変換
        $webStoreItemEntities = $this->webStorePurchaseService->convertItemsToEntities($items);

        // 1. virtual_goodアイテムのフィルタリング
        $virtualGoodWebStoreItems = $this->webStorePurchaseService->filterVirtualGoodItems($webStoreItemEntities);

        // 2. 有料商品かどうかを判定（purchase.itemsから判定）
        // W2では金額情報が送られてこないため、アイテム情報から有料か判定する
        $isPaidOrder = $this->webStorePurchaseService->isPaidOrderFromItems($virtualGoodWebStoreItems);

        // 3. 年齢制限チェック
        $this->webStoreUserService->checkPurchaseRestriction($userBirthday, $isPaidOrder, $now);

        // 4. 購入回数制限チェック（各アイテムごと）
        $this->webStorePurchaseService->validatePurchaseItems($virtualGoodWebStoreItems, $usrUserId, $now);

        // 5. トランザクションIDを発行
        $transactionId = $this->webStoreTransactionService->issueTransactionId($usrUserId, $isSandbox);

        // トランザクション処理
        $this->commitUserAndLogDataChanges();

        return new ShopWebstorePaymentValidationResultData($transactionId);
    }
}
