<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrStoreProductInterface extends UsrModelInterface
{
    public function getProductSubId(): string;

    public function getPurchaseCount(): int;

    public function getPurchaseTotalCount(): int;

    public function incrementPurchaseCount(): void;

    public function getLastResetAt(): string;
}
