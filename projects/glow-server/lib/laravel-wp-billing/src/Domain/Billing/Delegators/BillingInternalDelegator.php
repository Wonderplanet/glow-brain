<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Delegators;

use WonderPlanet\Domain\Billing\Services\BillingInternalService;

/**
 * 課金・通過基盤内の内部向けDelegator
 * 主にwp-currencyから呼び出される
 */
class BillingInternalDelegator
{
    public function __construct(
        private BillingInternalService $billingInternalService,
    ) {
    }

    /**
     * ユーザーのショップ登録情報を論理削除する
     *
     * ※このメソッドはBilling内の情報のみ削除します。
     * これを含めて全て削除する場合、Currency側のメソッドを使用してください
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteBillingDataByUserId(string $userId): void
    {
        $this->billingInternalService->softDeleteBillingDataByUserId($userId);
    }

    /**
     * getCurrencyPaidで使用するusr_store_product_historyの情報を取得
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param array<int, string> $receiptUniqueIds
     * @return \Illuminate\Support\Collection<int, \WonderPlanet\Domain\Billing\Models\UsrStoreProductHistory>
     */
    public function getUsrStoreProductHistoryCollectionByUserIdAndBillingPlatformAndReceiptUniqueIds(
        string $userId,
        string $billingPlatform,
        array $receiptUniqueIds
    ): \Illuminate\Support\Collection {
        return $this->billingInternalService
            ->getUsrStoreProductHistoryCollectionByUserIdAndBillingPlatformAndReceiptUniqueIds(
                $userId,
                $billingPlatform,
                $receiptUniqueIds
            );
    }
}
