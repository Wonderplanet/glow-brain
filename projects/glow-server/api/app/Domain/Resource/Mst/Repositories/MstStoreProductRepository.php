<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class MstStoreProductRepository
{
    public function findById(string $id): ?MstStoreProduct
    {
        return MstStoreProduct::query()->find($id);
    }

    public function getById(string $id): ?MstStoreProductEntity
    {
        $model = MstStoreProduct::query()->find($id);
        if (!$model) {
            return null;
        }

        return new MstStoreProductEntity(
            $model->id,
            $model->release_key,
            $model->product_id_ios,
            $model->product_id_android,
            $model->product_id_webstore,
        );
    }

    public function findByProductId(string $productId, string $billingPlatform): ?MstStoreProductEntity
    {
        // $platformによって検索するカラムを変える
        $column = '';
        switch ($billingPlatform) {
            case CurrencyConstants::PLATFORM_APPSTORE:
                $column = 'product_id_ios';
                break;
            case CurrencyConstants::PLATFORM_GOOGLEPLAY:
                $column = 'product_id_android';
                break;
        }

        $model = MstStoreProduct::query()
            ->where($column, $productId)
            ->first();

        if (!$model) {
            return null;
        }

        return new MstStoreProductEntity(
            $model->id,
            $model->release_key,
            $model->product_id_ios,
            $model->product_id_android,
            $model->product_id_webstore,
        );
    }

    /**
     * WebStore商品IDから商品を検索
     *
     * @param string $productIdWebstore WebStoreの商品ID
     * @return MstStoreProductEntity|null
     */
    public function findByProductIdWebstore(string $productIdWebstore): ?MstStoreProductEntity
    {
        $model = MstStoreProduct::query()
            ->where('product_id_webstore', $productIdWebstore)
            ->first();

        if (!$model) {
            return null;
        }

        return new MstStoreProductEntity(
            $model->id,
            $model->release_key,
            $model->product_id_ios,
            $model->product_id_android,
            $model->product_id_webstore,
        );
    }

    /**
     * WebStore商品IDから商品を一括取得
     *
     * @param array<string> $productIdWebstores WebStoreの商品ID配列
     * @return Collection<string, MstStoreProductEntity> product_id_webstoreをキーとしたMstStoreProductEntityのCollection
     */
    public function getByProductIdWebstores(array $productIdWebstores): Collection
    {
        $models = MstStoreProduct::query()
            ->whereIn('product_id_webstore', $productIdWebstores)
            ->get();

        return $models->map(function (MstStoreProduct $model) {
            return new MstStoreProductEntity(
                $model->id,
                $model->release_key,
                $model->product_id_ios,
                $model->product_id_android,
                $model->product_id_webstore,
            );
        })->keyBy(fn(MstStoreProductEntity $entity): string => $entity->getProductIdWebstore());
    }
}
