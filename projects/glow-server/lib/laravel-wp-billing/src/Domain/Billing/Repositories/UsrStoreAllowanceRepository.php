<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Repositories;

use WonderPlanet\Domain\Billing\Models\UsrStoreAllowance;

/**
 * ユーザーの購入許可情報のレコードを管理するRepository
 */
class UsrStoreAllowanceRepository
{
    /**
     * 購入許可情報を登録する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $productId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $deviceId
     * @return UsrStoreAllowance 登録した購入許可情報
     */
    public function insertStoreAllowance(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $productId,
        string $mstStoreProductId,
        string $productSubId,
        string $deviceId,
    ): UsrStoreAllowance {
        $usrStoreAllowance = new UsrStoreAllowance();

        $usrStoreAllowance->usr_user_id = $userId;
        $usrStoreAllowance->os_platform = $osPlatform;
        $usrStoreAllowance->billing_platform = $billingPlatform;
        $usrStoreAllowance->product_id = $productId;
        $usrStoreAllowance->mst_store_product_id = $mstStoreProductId;
        $usrStoreAllowance->product_sub_id = $productSubId;
        $usrStoreAllowance->device_id = $deviceId;
        $usrStoreAllowance->save();

        return $usrStoreAllowance;
    }

    /**
     * 購入許可情報を完全削除する
     *
     * @param string $userId
     * @param string $storeAllowanceId
     * @return void
     */
    public function deleteStoreAllowance(string $userId, string $storeAllowanceId): void
    {
        // レコードを消すだけであればuser_idは必須ではないけれど、
        // 別ユーザーのIDを間違って削除しないようにするために入れている
        UsrStoreAllowance::query()
            ->where('usr_user_id', $userId)
            ->where('id', $storeAllowanceId)
            ->forceDelete();
    }

    /**
     * ユーザーの購入許可情報をIDで検索する
     *
     * @param string $id
     * @return UsrStoreAllowance|null
     */
    public function findById(string $id): ?UsrStoreAllowance
    {
        return UsrStoreAllowance::query()
            ->where('id', $id)
            ->first() ?? null;
    }

    /**
     * ユーザーの購入許可情報を全て取得する
     *
     * @param string $userId
     * @return array<UsrStoreAllowance>
     */
    public function findAllByUserId(string $userId): array
    {
        return UsrStoreAllowance::query()
            ->where('usr_user_id', $userId)
            ->get()
            ->all();
    }

    /**
     * ユーザーの購入許可情報をproduct_idで検索する
     *
     * user_id, product_id, platformでunique制約されているため、ひとつだけ取得される
     *
     * @param string $userId
     * @param string $billingPlatform
     * @param string $storeProductId  ストアのプロダクトID
     * @return UsrStoreAllowance|null
     */
    public function findByUserIdAndProductId(
        string $userId,
        string $billingPlatform,
        string $storeProductId
    ): ?UsrStoreAllowance {
        return UsrStoreAllowance::query()
            ->where('usr_user_id', $userId)
            ->where('product_id', $storeProductId)
            ->where('billing_platform', $billingPlatform)
            ->first() ?? null;
    }

    /**
     * ユーザーの購入許可情報を完全削除する
     *
     * @param string $userId
     * @return void
     */
    public function forceDeleteByUserId(
        string $userId,
    ): void {
        UsrStoreAllowance::query()
            ->where('usr_user_id', $userId)
            ->forceDelete();
    }
}
