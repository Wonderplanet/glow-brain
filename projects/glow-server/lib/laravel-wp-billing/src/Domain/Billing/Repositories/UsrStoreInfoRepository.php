<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Repositories;

use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;

/**
 * ユーザーのショップ登録情報を扱うRepository
 *
 * $paidPriceについて、decimal(20,6)の固定小数点数で扱われるので、
 * PHP上は文字列としている
 *
 */
class UsrStoreInfoRepository
{
    /**
     * ショップ登録情報を取得する
     *
     * @param string $userId
     * @return UsrStoreInfo|null
     */
    public function findByUserId(string $userId): ?UsrStoreInfo
    {
        return UsrStoreInfo::query()
            ->where('usr_user_id', $userId)
            ->first();
    }

    /**
     * 指定されたパラメータで、ショップ情報を登録または更新する
     *
     * $paidPriceはJPYの累計のみ想定しているため、intで扱う
     *
     * @param string $userId
     * @param integer $age
     * @param int|null $paidPrice
     * @param string|null $renotifyAt
     * @return void
     */
    private function upsertStoreInfoInternal(string $userId, int $age, ?int $paidPrice, ?string $renotifyAt): void
    {
        $usrStoreInfo = UsrStoreInfo::query()
            ->where('usr_user_id', $userId)
            ->first();

        if ($usrStoreInfo === null) {
            $usrStoreInfo = new UsrStoreInfo();
            $usrStoreInfo->usr_user_id = $userId;
        }

        $usrStoreInfo->age = $age;
        // paid_priceの定義はdecimal(20,6)のため、stringにする
        if (!is_null($paidPrice)) {
            $usrStoreInfo->paid_price = (string)$paidPrice;
        }
        $usrStoreInfo->renotify_at = $renotifyAt;
        $usrStoreInfo->save();
    }

    /**
     * 指定されたパラメータで、ショップ情報を登録または更新する
     *
     * @param string $userId
     * @param int $age
     * @param int $paidPrice
     * @param string|null $renotifyAt
     * @return void
     */
    public function upsertStoreInfo(string $userId, int $age, int $paidPrice, ?string $renotifyAt): void
    {
        $this->upsertStoreInfoInternal($userId, $age, $paidPrice, $renotifyAt);
    }

    /**
     * 年齢と再通知日時を更新する
     *
     * @param string $userId
     * @param int $age
     * @param string|null $renotifyAt
     * @return void
     */
    public function upsertStoreInfoAge(string $userId, int $age, ?string $renotifyAt): void
    {
        $this->upsertStoreInfoInternal($userId, $age, null, $renotifyAt);
    }

    /**
     * 購入額を加算する
     *
     * 累積の元となるpurchase_priceがdecimal(20,6)の固定小数点数で扱われるので、文字列で渡される
     * それを小数点以下切り上げを行なった値を加算する
     *
     * @param string $userId
     * @param string $amount
     * @return void
     */
    public function incrementPaidPrice(string $userId, string $amount)
    {
        UsrStoreInfo::query()
            ->where('usr_user_id', $userId)
            ->increment('paid_price', (int)$amount);
    }

    /**
     * 購入額を減算する
     *
     * 累積の元となるpurchase_priceがdecimal(20,6)の固定小数点数で扱われるので、文字列で渡される
     * それを小数点以下切り上げを行なった値を減算する
     *
     * @param string $userId
     * @param string $amount
     * @return void
     */
    public function decrementPaidPrice(string $userId, string $amount): void
    {
        UsrStoreInfo::query()
            ->where('usr_user_id', $userId)
            ->decrement('paid_price', (int)$amount);
    }

    /**
     * VIPポイントの合計を更新する
     *
     * @param string $userId
     * @param integer $totalVipPoint
     * @return void
     */
    public function updateTotalVipPoint(string $userId, int $totalVipPoint): void
    {
        UsrStoreInfo::query()
            ->where('usr_user_id', $userId)
            ->update(['total_vip_point' => $totalVipPoint]);
    }

    /**
     * ユーザーのショップ情報を論理削除する
     *
     * @param string $userId
     * @return void
     */
    public function softDeleteByUserId(string $userId): void
    {
        UsrStoreInfo::query()
            ->where('usr_user_id', $userId)
            ->delete();
    }
}
