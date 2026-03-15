<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrStoreProduct;
use App\Domain\Shop\Models\UsrStoreProductInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrStoreProductRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrStoreProduct::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrStoreProductInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'product_sub_id' => $model->getProductSubId(),
                'purchase_count' => $model->getPurchaseCount(),
                'purchase_total_count' => $model->getPurchaseTotalCount(),
                'last_reset_at' => $model->getLastResetAt(),
            ];
        })->toArray();

        UsrStoreProduct::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'product_sub_id'],
            ['purchase_count', 'purchase_total_count', 'last_reset_at'],
        );
    }

    public function create(string $userId, string $productSubId, CarbonImmutable $now): UsrStoreProductInterface
    {
        $usrStoreProduct = new UsrStoreProduct();

        $usrStoreProduct->usr_user_id = $userId;
        $usrStoreProduct->product_sub_id = $productSubId;
        $usrStoreProduct->purchase_count = 0;
        $usrStoreProduct->purchase_total_count = 0;
        $usrStoreProduct->last_reset_at = $now->format('Y-m-d H:i:s');

        $this->syncModel($usrStoreProduct);

        return $usrStoreProduct;
    }

    public function get(string $usrUserId, string $oprProductId): ?UsrStoreProduct
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'product_sub_id',
            $oprProductId,
            function () use ($usrUserId, $oprProductId) {
                return UsrStoreProduct::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('product_sub_id', $oprProductId)
                    ->first();
            },
        );
    }

    public function getOrCreateByOprProductId(
        string $usrUserId,
        string $oprProductId,
        CarbonImmutable $now,
    ): UsrStoreProductInterface {
        $usrStoreProduct = $this->get($usrUserId, $oprProductId);
        if (is_null($usrStoreProduct)) {
            $usrStoreProduct = $this->create($usrUserId, $oprProductId, $now);
        }
        return $usrStoreProduct;
    }

    /**
     * @return Collection<\App\Domain\Shop\Models\UsrStoreProductInterface>
     */
    public function getList(string $userId): Collection
    {
        return $this->cachedGetAll($userId);
    }
}
