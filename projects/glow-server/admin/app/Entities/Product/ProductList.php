<?php

declare(strict_types=1);

namespace App\Entities\Product;

use Illuminate\Support\Collection;

class ProductList
{
    /** @var Collection<ProductDiamondInfo> $productDiamondInfoList */
    private Collection $productDiamondInfoList;

    /** @var Collection<ProductPackInfo> $productPackInfoList */
    private Collection $productPackInfoList;

    /** @var Collection<ProductPassInfo> $productPassInfoList */
    private Collection $productPassInfoList;

    /** @var Collection<string> $receiptToOprProductIdMap */
    private Collection $receiptToOprProductIdMap;

    public function __construct()
    {
        $this->productDiamondInfoList = collect();
        $this->productPackInfoList = collect();
        $this->productPassInfoList = collect();
        $this->receiptToOprProductIdMap = collect();
    }

    /**
     * @param string $oprProductId
     * @return ProductDiamondInfo|null
     */
    public function getProductDiamondInfo(string $oprProductId): ProductDiamondInfo|null
    {
        return $this->productDiamondInfoList->get($oprProductId);
    }

    /**
     * @param ProductDiamondInfo $productDiamondInfo
     */
    public function putProductDiamondInfo(ProductDiamondInfo $productDiamondInfo): void
    {
        $this->productDiamondInfoList->put($productDiamondInfo->getId(), $productDiamondInfo);
    }

    /**
     * @param string $oprProductId
     * @return ProductPackInfo|null
     */
    public function getProductPackInfo(string $oprProductId): ProductPackInfo|null
    {
        return $this->productPackInfoList->get($oprProductId);
    }

    /**
     * @param ProductPackInfo $productPackInfo
     */
    public function putProductPackInfo(ProductPackInfo $productPackInfo): void
    {
        $this->productPackInfoList->put($productPackInfo->getId(), $productPackInfo);
    }

    /**
     * @param string $oprProductId
     * @return ProductPassInfo|null
     */
    public function getProductPassInfo(string $oprProductId): ProductPassInfo|null
    {
        return $this->productPassInfoList->get($oprProductId);
    }

    /**
     * @param ProductPassInfo $productPassInfo
     */
    public function putProductPassInfo(ProductPassInfo $productPassInfo): void
    {
        $this->productPassInfoList->put($productPassInfo->getId(), $productPassInfo);
    }

    /**
     * @param string $oprProductId
     * @return ProductInfo|null
     */
    public function getProductInfo(string $oprProductId): ProductInfo|null
    {
        return $this->getProductDiamondInfo($oprProductId)
            ?? $this->getProductPackInfo($oprProductId)
            ?? $this->getProductPassInfo($oprProductId);
    }

    /**
     * @return Collection<string>
     */
    public function getReceiptToOprProductId(string $receiptUniqueId): string
    {
        return $this->receiptToOprProductIdMap->get($receiptUniqueId, '');
    }

    /**
     * @param string $receiptUniqueId
     */
    public function putReceiptToOprProductId(string $receiptUniqueId, string $oprProductId): void
    {
        $this->receiptToOprProductIdMap->put($receiptUniqueId, $oprProductId);
    }
}
