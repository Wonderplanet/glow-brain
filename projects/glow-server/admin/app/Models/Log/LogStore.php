<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use WonderPlanet\Domain\Billing\Models\LogStore as BaseLogStore;

class LogStore extends BaseLogStore
{
    protected $connection = Database::TIDB_CONNECTION;

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getDeviceId(): ?string
    {
        return $this->device_id;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getPlatformProductId(): string
    {
        return $this->platform_product_id;
    }

    public function getMstStoreProductId(): string
    {
        return $this->mst_store_product_id;
    }

    public function getProductSubId(): string
    {
        return $this->product_sub_id;
    }

    public function getProductSubName(): string
    {
        return $this->product_sub_name;
    }

    public function getRawReceipt(): string
    {
        return $this->raw_receipt;
    }

    public function getRawPriceString(): string
    {
        return $this->raw_price_string;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getReceiptUniqueId(): string
    {
        return $this->receipt_unique_id;
    }

    public function getReceiptBundleId(): string
    {
        return $this->receipt_bundle_id;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function getBillingPlatform(): string
    {
        return $this->billing_platform;
    }

    public function getPaidAmount(): int
    {
        return $this->paid_amount;
    }

    public function getFreeAmount(): int
    {
        return $this->free_amount;
    }

    public function getPurchasePrice(): string
    {
        return $this->purchase_price;
    }

    public function getPricePerAmount(): string
    {
        return $this->price_per_amount;
    }

    public function getVipPoint(): int
    {
        return $this->vip_point;
    }

    public function getIsSandbox(): int
    {
        return $this->is_sandbox;
    }

    public function getTriggerType(): string
    {
        return $this->trigger_type;
    }

    public function getTriggerId(): string
    {
        return $this->trigger_id;
    }

    public function getTriggerName(): string
    {
        return $this->trigger_name;
    }

    public function getTriggerDetail(): string
    {
        return $this->trigger_detail;
    }

    public function getRequestIdType(): string
    {
        return $this->request_id_type;
    }

    public function getRequestId(): string
    {
        return $this->request_id;
    }

    public function getNginxRequestId(): string
    {
        return $this->nginx_request_id;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at->toDateTimeString();
    }
}
