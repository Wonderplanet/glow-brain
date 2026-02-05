<?php

declare(strict_types=1);

namespace App\Models\Adm;

/**
 * @property string $app_id
 * @property string $platform_id
 * @property int $date
 * @property float $total_sales
 * @property string $data
 * @property float $direct_total_sales
 * @property string $direct_data
 * @property float $subscription_total_sales
 * @property string $subscription_data
 */
class AdmBankF003 extends AdmModel
{
    protected $table = 'adm_bank_f003';

    public function setAppId(string $appId): void
    {
        $this->app_id = $appId;
    }

    public function getAppId(): string
    {
        return $this->app_id;
    }

    public function setPlatformId(string $platformId): void
    {
        $this->platform_id = $platformId;
    }

    public function getPlatFormId(): string
    {
        return $this->platform_id;
    }

    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setTotalSales(float $totalSales): void
    {
        $this->total_sales = $totalSales;
    }

    public function getTotalSales(): float
    {
        return $this->total_sales;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setDirectTotalSales(float $directTotalSales): void
    {
        $this->direct_total_sales = $directTotalSales;
    }

    public function getDirectTotalSales(): float
    {
        return $this->direct_total_sales;
    }

    public function setDirectData(string $directData): void
    {
        $this->direct_data = $directData;
    }

    public function getDirectData(): string
    {
        return $this->direct_data;
    }

    public function setSubscriptionTotalSales(float $subscriptionTotalSales): void
    {
        $this->subscription_total_sales = $subscriptionTotalSales;
    }

    public function getSubscriptionTotalSales(): float
    {
        return $this->subscription_total_sales;
    }

    public function setSubscriptionData(string $subscriptionData): void
    {
        $this->subscription_data = $subscriptionData;
    }

    public function getSubscriptionData(): string
    {
        return $this->subscription_data;
    }
}
