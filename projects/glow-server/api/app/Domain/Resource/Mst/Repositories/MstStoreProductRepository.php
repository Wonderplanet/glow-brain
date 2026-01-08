<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Mst\Models\MstStoreProduct;
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
        );
    }
}
