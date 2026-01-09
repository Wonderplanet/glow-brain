<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use WonderPlanet\Domain\Currency\Traits\Mst\MstStoreProductTrait;

/**
 * マスタになる商品データモデル
 *
 * プロダクトの商品と、ストアのプロダクトIDが紐付けられている
 *
 * @property string $id
 * @property int $release_key
 * @property string $product_id_ios
 * @property string $product_id_android
 */
class MstStoreProduct extends BaseOprModel
{
    use MstStoreProductTrait;

    public function getId(): string
    {
        return $this->id;
    }
    public function getReleaseKey(): int
    {
        return $this->release_key;
    }
    public function getProductIdIos(): string
    {
        return $this->product_id_ios;
    }
    public function getProductIdAndroid(): string
    {
        return $this->product_id_android;
    }
}
