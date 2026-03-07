<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Traits\Domain\Currency;

use WonderPlanet\Domain\Currency\Models\MstStoreProduct;
use WonderPlanet\Domain\Currency\Models\OprProduct;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

/**
 * データ操作を行うメソッドを集めたTrait
 *
 * テスト用マスタデータの作成などを行う
 *
 * マスタデータの操作はテスト向けのみとなるため、Factoryメソッドには含めない
 *
 * TODO: ファイルからマスタデータをセットアップするfixtureのような仕組みはいずれ用意したい…
 */
trait DataFixtureTrait
{
    /**
     * mst_store_productのマスタデータを作る
     *
     * @param string $id
     * @param int $releaseKey
     * @param string $productIdIos
     * @param string $productIdAndroid
     * @return void
     */
    private function insertMstStoreProduct(
        string $id,
        int $releaseKey,
        string $productIdIos,
        string $productIdAndroid,
    ) {
        MstStoreProduct::factory()->create([
            'id' => $id,
            'release_key' => $releaseKey,
            'product_id_ios' => $productIdIos,
            'product_id_android' => $productIdAndroid,
        ]);
    }

    /**
     * opr_productのマスタデータを作る
     *
     * @param string $id
     * @param integer $releaseKey
     * @param string $mstProductId
     * @param integer $paidAmount
     * @return void
     */
    private function insertOptProduct(
        string $id,
        int $releaseKey,
        string $mstProductId,
        int $paidAmount
    ) {
        OprProduct::factory()->create([
            'id' => $id,
            'release_key' => $releaseKey,
            'mst_store_product_id' => $mstProductId,
            'paid_amount' => $paidAmount,
        ]);
    }

    /**
     * usr_currency_summaryのユーザーデータを作る
     *
     * @param string  $userId
     * @param integer $paidAmountApple
     * @param integer $paidAmountGoogle
     * @param integer $paidAmountShare
     * @param integer $freeAmount
     * @return void
     */
    private function createUsrCurrencySummary(string $userId, int $paidAmountApple, int $paidAmountGoogle, int $paidAmountShare, int $freeAmount)
    {
        $model = new UsrCurrencySummary();

        $model->usr_user_id = $userId;
        $model->paid_amount_apple = $paidAmountApple;
        $model->paid_amount_google = $paidAmountGoogle;
        $model->paid_amount_share = $paidAmountShare;
        $model->free_amount = $freeAmount;
        $model->save();
    }
}
