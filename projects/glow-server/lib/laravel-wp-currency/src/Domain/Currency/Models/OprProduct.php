<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * ユーザーに販売する実際の商品データモデル
 *
 * 内部的には、product_sub_idの実態として使用されている。
 * そのため実質的にはproduct_sub_id = opr_products.idになる。
 *
 * システム全体の名称としてproduct_sub_idが使用されているため、
 * プログラム内では主にproduct_sub_idと命名している。
 *
 * ただ、opr_productsテーブルそのものを指定している場合は、opr_product_idを使用する。
 *
 * @property string $id
 * @property int $release_key
 * @property string $mst_store_product_id
 * @property int $paid_amount
 */
class OprProduct extends BaseOprModel
{
    public function getId(): string
    {
        return $this->id;
    }
    public function getMstStoreProductId(): string
    {
        return $this->mst_store_product_id;
    }
    public function getPaidAmount(): int
    {
        return $this->paid_amount;
    }

    /**
     * product_sub_idを取得する
     *
     * 実態はopr_products.id
     *
     * @return string
     */
    public function getProductSubId(): string
    {
        return $this->id;
    }
}
