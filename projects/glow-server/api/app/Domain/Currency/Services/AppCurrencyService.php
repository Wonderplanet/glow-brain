<?php

declare(strict_types=1);

namespace App\Domain\Currency\Services;

use App\Domain\Currency\Utils\CurrencyUtility;
use App\Domain\Resource\Mst\Entities\MstStoreProductEntity;
use App\Domain\Resource\Mst\Entities\OprProductEntity;
use App\Domain\Resource\Mst\Repositories\MstStoreProductRepository;
use App\Domain\Resource\Mst\Repositories\OprProductRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * 通貨周りの処理を行う
 */
readonly class AppCurrencyService
{
    public function __construct(
        private readonly MstStoreProductRepository $mstStoreProductRepository,
        private readonly OprProductRepository $oprProductRepository,
    ) {
    }

    /**
     * MstStoreProductを取得する
     *
     * @param string $id
     * @return MstStoreProductEntity|null
     */
    public function getMstStoreProductById(string $id): ?MstStoreProductEntity
    {
        return $this->mstStoreProductRepository->getById($id);
    }

    /**
     * 課金プラットフォームとストアのプロダクトIDからMstStoreProductを取得する
     *
     * @param string $productId
     * @param string $billingPlatform
     * @return MstStoreProductEntity|null
     */
    public function getMstStoreProductByProductId(string $productId, string $billingPlatform): ?MstStoreProductEntity
    {
        return $this->mstStoreProductRepository->findByProductId($productId, $billingPlatform);
    }

    /**
     * OprProductを取得する
     *
     * @param string $id
     * @return OprProductEntity|null
     */
    public function getOprProductById(string $id): ?OprProductEntity
    {
        return $this->oprProductRepository->findById($id);
    }

    /**
     * mst_store_product_idからOprProductを取得する
     *
     * @param string $mstProductId
     * @return OprProductEntity|null
     */
    public function getOprProductByMstProductId(string $mstProductId): ?OprProductEntity
    {
        return $this->oprProductRepository->findByMstProductId($mstProductId);
    }

    /**
     * mst_store_product_idの配列からOprProductを一括取得（販売期間内のもののみ）
     *
     * @param array<string> $mstProductIds
     * @param CarbonImmutable $now 販売期間チェック用の日時
     * @return Collection<string, OprProductEntity>
     */
    public function getOprProductsByMstProductIds(array $mstProductIds, CarbonImmutable $now): Collection
    {
        return $this->oprProductRepository->getByMstProductIds($mstProductIds, $now);
    }

    /**
     * mst_store_product_idと日時情報からOprProductを取得する
     *
     * @param string $mstProductId
     * @param CarbonImmutable $targetAt
     * @return OprProductEntity|null
     */
    public function getOprProductByMstProductIdAndTargetAt(
        string $mstProductId,
        CarbonImmutable $targetAt
    ): ?OprProductEntity {
        return $this->oprProductRepository->findByMstProductIdAndTargetAt($mstProductId, $targetAt);
    }

    /**
     * mst_store_product_idに紐づいたmst_store_productのデータも一緒に取得する
     *
     * @return Collection
     */
    public function getAllOprProductsByIdWithMstStoreProduct()
    {
        return $this->oprProductRepository->getAllWithMstStoreProduct();
    }

    /**
     * billing/currencyライブラリで使用する購入プラットフォーム名を取得する
     *
     * @param string $platform
     * @return string
     */
    public function getBillingPlatform(string $platform): string
    {
        return CurrencyUtility::getBillingPlatform($platform);
    }

    /**
     * プラットフォーム名から課金基盤向けのOSプラットフォームを取得
     *
     *  基盤ライブラリ側で定義されている値に変換する
     *
     * @param string $platform
     * @return string
     */
    public function getOsPlatform(string $platform): string
    {
        return CurrencyUtility::getOsPlatform($platform);
    }
}
