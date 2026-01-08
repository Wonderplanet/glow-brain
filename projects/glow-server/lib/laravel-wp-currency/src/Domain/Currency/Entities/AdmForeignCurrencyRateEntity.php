<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Entities;

use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;

class AdmForeignCurrencyRateEntity extends BaseModelEntity
{
    private int $year;
    private int $month;
    private string $currencyCode;
    private string $currency;
    private string $currencyName;
    private string $tts;
    private string $ttb;
    private string $ttm;

    public function __construct(AdmForeignCurrencyRate $model)
    {
        parent::__construct($model);

        $this->year = $model->year;
        $this->month = $model->month;
        $this->currencyCode = $model->currency_code;
        $this->currency = $model->currency;
        $this->currencyName = $model->currency_name;
        $this->tts = $model->tts;
        $this->ttb = $model->ttb;
        $this->ttm = $model->ttm;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCurrencyName(): string
    {
        return $this->currencyName;
    }

    public function getTts(): string
    {
        return $this->tts;
    }

    public function getTtb(): string
    {
        return $this->ttb;
    }

    public function getTtm(): string
    {
        return $this->ttm;
    }
}
