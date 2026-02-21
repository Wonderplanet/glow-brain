<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services;

use WonderPlanet\Domain\Billing\Repositories\LogAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreAllowanceRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Currency\Entities\UserDeleteTrigger;

/**
 * ユーザーの商品購入関連のService
 *
 * このServiceはwp-currencyから呼び出される
 * そのため、CurrencyServiceを呼び出すことはできない
 * 呼び出すと循環参照になるため
 */
class BillingInternalService
{
    public function __construct(
        private UsrStoreAllowanceRepository $usrStoreAllowanceRepository,
        private UsrStoreInfoRepository $usrStoreInfoRepository,
        private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository,
        private LogAllowanceRepository $logAllowanceRepository,
    ) {
    }

    /**
     * 指定されたユーザーIDの課金情報を論理削除する
     *
     * 論理削除はlaravelのSoftDeletesトレイト機能を使用しているため、モデルなどを変更する場合は注意すること
     *
     * ※allowanceは不要な情報になるので完全削除する
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteBillingDataByUserId(string $userId): void
    {
        // 購入許可情報の削除(完全削除)
        // allowanceが存在していたら削除する
        // 削除前にログを追加する
        $usrStoreAllowances = $this->usrStoreAllowanceRepository->findAllByUserId($userId);
        if (count($usrStoreAllowances) > 0) {
            foreach ($usrStoreAllowances as $usrStoreAllowance) {
                $this->logAllowanceRepository->insertAllowanceLog(
                    $usrStoreAllowance->usr_user_id,
                    $usrStoreAllowance->os_platform,
                    $usrStoreAllowance->billing_platform,
                    $usrStoreAllowance->product_id,
                    $usrStoreAllowance->mst_store_product_id,
                    $usrStoreAllowance->product_sub_id,
                    $usrStoreAllowance->device_id,
                    new UserDeleteTrigger(
                        $userId,
                        $usrStoreAllowance->id,
                        "",
                    )
                );
            }
            $this->usrStoreAllowanceRepository->forceDeleteByUserId($userId);
        }

        // ストア情報の削除
        $this->usrStoreInfoRepository->softDeleteByUserId($userId);

        // 購入履歴の削除
        $this->usrStoreProductHistoryRepository->softDeleteByUserId($userId);
    }

    /**
     * userId、billingPlatform、receiptUniqueIdsをもとにusr_store_product_historyの情報を取得
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
        return $this->usrStoreProductHistoryRepository
            ->findByUserIdAndReceiptUniqueIdsFromBillingPlatform($userId, $billingPlatform, $receiptUniqueIds);
    }
}
