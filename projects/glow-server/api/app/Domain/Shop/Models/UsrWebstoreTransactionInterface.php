<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrWebstoreTransactionInterface extends UsrModelInterface
{
    public function getTransactionId(): string;

    public function getOrderId(): ?int;

    public function setOrderId(int $orderId): void;

    public function getStatus(): string;

    public function setStatus(string $status): void;

    public function setAdjustStatus(?string $adjustStatus): void;

    public function isSandbox(): bool;
}
