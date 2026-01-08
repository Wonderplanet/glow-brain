<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * 課金した時のトリガー
 */
class ShopPurchasedTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'shop_purchased';

    public function __construct(
        readonly string $productSubId,
        readonly string $oprProductName,
        readonly string $productId,
        readonly string $billingPlatform,
        readonly string $mstProductId,
        readonly string $language,
        readonly string $currencyCode,
        readonly string $purchasePrice,
    ) {
        parent::__construct(
            $productSubId,
            $oprProductName,
            [
                "product_id" => $productId,
                "billing_platform" => $billingPlatform,
                "mst_product_id" => $mstProductId,
                "language" => $language,
                "currency_code" => $currencyCode,
                "price" => $purchasePrice,
            ],
        );
    }
}
