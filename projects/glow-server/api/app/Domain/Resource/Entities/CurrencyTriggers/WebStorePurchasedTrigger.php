<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\CurrencyTriggers;

/**
 * WebStore（Xsolla）で課金した時のトリガー
 */
class WebStorePurchasedTrigger extends CurrencyBaseTrigger
{
    public const TYPE = 'webstore_purchased';

    public function __construct(
        readonly string $oprProductId,
        readonly string $oprProductName,
        readonly int $orderId,
        readonly ?string $invoiceId,
        readonly string $transactionId,
        readonly string $platformProductId,
        readonly string $mstStoreProductId,
        readonly string $currencyCode,
        readonly string $purchasePrice,
    ) {
        parent::__construct(
            $oprProductId,
            $oprProductName,
            [
                "order_id" => $orderId,
                "invoice_id" => $invoiceId,
                "transaction_id" => $transactionId,
                "platform_product_id" => $platformProductId,
                "mst_store_product_id" => $mstStoreProductId,
                "currency_code" => $currencyCode,
                "purchase_price" => $purchasePrice,
            ],
        );
    }
}
