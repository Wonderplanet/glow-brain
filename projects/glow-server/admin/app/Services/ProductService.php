<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\ProductType;
use App\Entities\Product\ProductDiamondInfo;
use App\Entities\Product\ProductList;
use App\Entities\Product\ProductPackInfo;
use App\Entities\Product\ProductPassInfo;
use App\Models\Mst\MstPack;
use App\Models\Mst\MstShopPass;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProduct;
use App\Models\Usr\UsrStoreProductHistory;
use Illuminate\Support\Collection;

/**
 * 製品に関するサービス
 */
class ProductService
{
    /**
     * @param string $billingPlatform
     * @param Collection<string> $receiptUniqueIds
     * @return ProductList
     */
    public function getProductListByReceiptUniqueIds(
        string $billingPlatform,
        Collection $receiptUniqueIds
    ): ProductList {

        $productList = new ProductList();
        $usrStoreProductHistories = UsrStoreProductHistory::query()
            ->whereIn('receipt_unique_id', $receiptUniqueIds)
            ->where('billing_platform', $billingPlatform)
            ->get();
        $usrStoreProductHistories->each(function (UsrStoreProductHistory $usrStoreProductHistory) use ($productList) {
            $productList->putReceiptToOprProductId(
                $usrStoreProductHistory->receipt_unique_id,
                $usrStoreProductHistory->product_sub_id
            );
        });

        $oprProductIds = $usrStoreProductHistories->pluck('product_sub_id');
        $oprProducts = OprProduct::query()
            ->whereIn('id', $oprProductIds)
            ->get();
        $mstStoreProducts = MstStoreProduct::query()
            ->whereIn('id', $oprProducts->pluck('mst_store_product_id'))
            ->get()
            ->keyBy('id');

        $oprProductsDiamond = $oprProducts->where('product_type', ProductType::DIAMOND->value)->keyBy('id');
        $oprProductsPack = $oprProducts->where('product_type', ProductType::PACK->value)->keyBy('id');
        $oprProductsPass = $oprProducts->where('product_type', ProductType::PASS->value)->keyBy('id');

        if ($oprProductsDiamond->isNotEmpty()) {
            $oprProductsDiamond->each(function (OprProduct $oprProduct) use (
                $productList, $mstStoreProducts
            ) {
                $productList->putProductDiamondInfo(new ProductDiamondInfo(
                    $oprProduct,
                    $mstStoreProducts->get($oprProduct->mst_store_product_id)
                ));
            });
        }

        if ($oprProductsPack->isNotEmpty()) {
            MstPack::query()
                ->whereIn('product_sub_id', $oprProductsPack->keys())
                ->get()
                ->each(function (MstPack $mstPack) use (
                    $productList, $oprProductsPack, $mstStoreProducts
                ) {
                    $oprProduct = $oprProductsPack->get($mstPack->product_sub_id);
                    if ($oprProduct === null) {
                        return;
                    }
                    $productList->putProductPackInfo(new ProductPackInfo(
                        $oprProduct,
                        $mstStoreProducts->get($oprProduct->mst_store_product_id),
                        $mstPack
                    ));
                });
        }

        if ($oprProductsPass->isNotEmpty()) {
            MstShopPass::query()
                ->whereIn('opr_product_id', $oprProductsPass->keys())
                ->get()
                ->each(function (MstShopPass $mstShopPass) use (
                    $productList, $oprProductsPass, $mstStoreProducts
                ) {
                    $oprProduct = $oprProductsPass->get($mstShopPass->opr_product_id);
                    if ($oprProduct === null) {
                        return;
                    }
                    $productList->putProductPassInfo(new ProductPassInfo(
                        $oprProduct,
                        $mstStoreProducts->get($oprProduct->mst_store_product_id),
                        $mstShopPass
                    ));
                });
        }

        return $productList;
    }

}
