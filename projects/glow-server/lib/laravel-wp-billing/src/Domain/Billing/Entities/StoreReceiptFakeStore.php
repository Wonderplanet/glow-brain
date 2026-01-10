<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Entities;

use Carbon\CarbonImmutable;

/**
 * Unityのフェイクストアのレシート
 */
class StoreReceiptFakeStore extends StoreReceipt
{
    protected string $receiptType = self::TYPE_FAKESTORE;

    public function getUnitqueId(): string
    {
        return $this->verifiedResponse['TransactionID'] ?? '';
    }

    public function getBundleId(): string
    {
        return 'fakestore';
    }

    public function getPurchaseToken(): string
    {
        return 'faketoken';
    }

    public function isSandboxReceipt(): bool
    {
        return true;
    }

    public function getPlatformReceiptString(): string
    {
        return $this->getPayloadString();
    }

    public function getProductIds(): array
    {
        return ['edmo_pack_160_1_framework'];
    }

    public function getPurchaseDate(): ?CarbonImmutable
    {
        return CarbonImmutable::now();
    }
}
