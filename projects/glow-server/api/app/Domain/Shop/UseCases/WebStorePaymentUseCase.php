<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Shop\Services\WebStorePurchaseService;
use App\Domain\Shop\Services\WebStoreUserService;
use App\Domain\User\Delegators\UserDelegator;
use App\Infrastructure\UsrModelManager;
use Illuminate\Support\Facades\Log;

/**
 * WebStore W4: 支払い通知
 *
 * notification_type: payment
 */
class WebStorePaymentUseCase
{
    public function __construct(
        private readonly WebStorePurchaseService $webStorePurchaseService,
        private readonly WebStoreUserService $webStoreUserService,
        private readonly UsrModelManager $usrModelManager,
        private readonly UserDelegator $userDelegator,
        private readonly Clock $clock,
    ) {
    }

    /**
     * W4: 支払い通知
     *
     * @param string $usrUserId ユーザーID
     * @param array<mixed> $items 購入アイテム一覧
     * @param string $transactionId トランザクションID
     * @param bool $isSandbox サンドボックスモードか
     * @param int $dryRun ドライラン値
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(
        string $usrUserId,
        array $items,
        string $transactionId,
        bool $isSandbox,
        int $dryRun
    ): void {
        Log::info('WebStore payment notification received', [
            'usr_user_id' => $usrUserId,
            'transaction_id' => $transactionId,
            'is_sandbox' => $isSandbox,
            'dry_run' => $dryRun,
            'items_count' => count($items),
        ]);

        // 本番環境でのサンドボックス決済チェック
        $this->webStoreUserService->checkSandboxInProduction($isSandbox);

        $now = $this->clock->now();

        // アクセストークン認証ができないのでここでユーザーをセットする
        $this->usrModelManager->setUsrUserId($usrUserId);

        // 1. BANチェック
        $this->userDelegator->checkUserBan($usrUserId, $now);

        // W4のlineitems配列が空の場合はリソース上限チェック不要
        if (count($items) === 0) {
            return;
        }

        // 2. アイテム配列をEntityコレクションに変換
        // W4のlineitems配列にはtypeフィールドが含まれていないため、virtual_goodフィルタは行わない
        $webStoreItems = $this->webStorePurchaseService->convertItemsToEntities($items);

        // 3. リソース所持上限チェック（W2とW4の間にユーザーがリソースを獲得している可能性があるため再チェック）
        $this->webStorePurchaseService->validatePurchaseItems($webStoreItems, $usrUserId, $now);
    }
}
