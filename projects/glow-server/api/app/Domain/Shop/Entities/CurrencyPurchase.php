<?php

declare(strict_types=1);

namespace App\Domain\Shop\Entities;

use App\Domain\Common\Utils\StringUtil;

class CurrencyPurchase
{
    /**
     * @param string $purchasePrice
     * @param int $purchaseAmount
     * @param string $currencyCode
     * @param string $purchaseAt
     */
    public function __construct(
        private string $purchasePrice,
        private int $purchaseAmount,
        private string $currencyCode,
        private string $purchaseAt,
    ) {
    }

    public function getPurchasePrice(): string
    {
        return $this->purchasePrice;
    }

    public function getPurchaseAmount(): int
    {
        return $this->purchaseAmount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getPurchaseAt(): string
    {
        return $this->purchaseAt;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'purchasePrice' => $this->purchasePrice,
            'purchaseAmount' => $this->purchaseAmount,
            'currencyCode' => $this->currencyCode,
            'purchaseAt' => StringUtil::convertToISO8601($this->purchaseAt),
        ];
    }
}
