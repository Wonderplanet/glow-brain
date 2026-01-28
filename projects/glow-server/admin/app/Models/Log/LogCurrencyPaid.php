<?php

declare(strict_types=1);

namespace App\Models\Log;

use App\Constants\Database;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid as BaseLogCurrencyPaid;

class LogCurrencyPaid extends BaseLogCurrencyPaid
{
    protected $connection = Database::TIDB_CONNECTION;

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getCurrencyPaidId(): string
    {
        return $this->currency_paid_id;
    }

    public function getReceiptUniqueId(): string
    {
        return $this->receipt_unique_id;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function getPurchasePrice(): string
    {
        return $this->purchase_price;
    }

    public function getPurchaseAmount(): int
    {
        return $this->purchase_amount;
    }

    public function getPricePerAmount(): string
    {
        return $this->price_per_amount;
    }

    public function getBeforeAmount(): int
    {
        return $this->before_amount;
    }

    public function getChangeAmount(): int
    {
        return $this->change_amount;
    }

    public function getCurrentAmount(): int
    {
        return $this->current_amount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getBillingPlatform(): string
    {
        return $this->billing_platform;
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

    public function getCreatedAt(): string
    {
        return $this->created_at->toDateTimeString();
    }

    /**
     * Factoryクラスの取得 (デフォルトに戻す)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }
}
