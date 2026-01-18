<?php

declare(strict_types=1);

namespace App\Models\Adm;

/**
 * @property string $fluentd_tag
 * @property string $version
 * @property string $app_id
 * @property string $app_user_id
 * @property string $app_system_prefix
 * @property string $platform_id
 * @property int $buy_coin
 * @property float $buy_amount
 * @property int $pay_coin
 * @property float $pay_amount
 * @property float $direct_amount
 * @property float $subscription_amount
 * @property string $item_id
 * @property string $insert_time
 * @property string $country_code
 * @property string $currency_code
 */
class AdmBankF002 extends AdmModel
{
    protected $table = 'adm_bank_f002';

    public function setFluentdTag(string $fluentdTag): void
    {
        $this->fluentd_tag = $fluentdTag;
    }

    public function getFluentdTag(): string
    {
        return $this->fluentd_tag;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setAppId(string $appId): void
    {
        $this->app_id = $appId;
    }

    public function getAppId(): string
    {
        return $this->app_id;
    }

    public function setAppUserId(string $appUserId): void
    {
        $this->app_user_id = $appUserId;
    }

    public function getAppUserId(): string
    {
        return $this->app_user_id;
    }

    public function setAppSystemPrefix(string $appSystemPrefix): void
    {
        $this->app_system_prefix = $appSystemPrefix;
    }

    public function getAppSystemPrefix(): string
    {
        return $this->app_system_prefix;
    }

    public function setPlatformId(string $platformId): void
    {
        $this->platform_id = $platformId;
    }

    public function getPlatformId(): string
    {
        return $this->platform_id;
    }

    public function setBuyCoin(int $buyCoin): void
    {
        $this->buy_coin = $buyCoin;
    }

    public function getBuyCoin(): int
    {
        return $this->buy_coin;
    }

    public function setBuyAmount(float $buyAmount): void
    {
        $this->buy_amount = $buyAmount;
    }

    public function getBuyAmount(): float
    {
        return $this->buy_amount;
    }

    public function setPayCoin(int $payCoin): void
    {
        $this->pay_coin = $payCoin;
    }

    public function getPayCoin(): int
    {
        return $this->pay_coin;
    }

    public function setPayAmount(float $payAmount): void
    {
        $this->pay_amount = $payAmount;
    }

    public function getPayAmount(): float
    {
        return $this->pay_amount;
    }

    public function setDirectAmount(float $directAmount): void
    {
        $this->direct_amount = $directAmount;
    }

    public function getDirectAmount(): float
    {
        return $this->direct_amount;
    }

    public function setSubscriptionAmount(float $subscriptionAmount): void
    {
        $this->subscription_amount = $subscriptionAmount;
    }

    public function getSubscriptionAmount(): float
    {
        return $this->subscription_amount;
    }

    public function setItemId(string $itemId): void
    {
        $this->item_id = $itemId;
    }

    public function getItemId(): string
    {
        return $this->item_id;
    }

    public function setInsertTime(string $insertTime): void
    {
        $this->insert_time = $insertTime;
    }

    public function getInsertTime(): string
    {
        return $this->insert_time;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->country_code = $countryCode;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currency_code = $currencyCode;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }
}
