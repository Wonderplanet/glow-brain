<?php

declare(strict_types=1);

namespace App\Domain\Shop\Entities;

class UsrStoreInfoEntity
{
    public function __construct(
        private readonly string $usrUserId,
        private readonly int $beforeAge,
        private readonly int $age,
        private readonly ?string $beforeRenotifyAt,
        private readonly ?string $renotifyAt,
        private readonly int $beforePaidPrice,
        private readonly int $paidPrice,
    ) {
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
    }

    public function getBeforeAge(): int
    {
        return $this->beforeAge;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getBeforeRenotifyAt(): ?string
    {
        return $this->beforeRenotifyAt;
    }

    public function getRenotifyAt(): ?string
    {
        return $this->renotifyAt;
    }

    public function getBeforePaidPrice(): int
    {
        return $this->beforePaidPrice;
    }

    public function getPaidPrice(): int
    {
        return $this->paidPrice;
    }

    /**
     * 課金額がリセットされたかどうか
     * @return bool true: リセットされた, false: リセットされていない
     */
    public function isResetPaidPrice(): bool
    {
        return $this->paidPrice === 0 && $this->beforePaidPrice > 0;
    }

    public function isAgeChanged(): bool
    {
        return $this->beforeAge !== $this->age;
    }

    public function isRenotifyAtChanged(): bool
    {
        return $this->beforeRenotifyAt !== $this->renotifyAt;
    }
}
