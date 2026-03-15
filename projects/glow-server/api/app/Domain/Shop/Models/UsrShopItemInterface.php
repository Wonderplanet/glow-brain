<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrShopItemInterface extends UsrModelInterface
{
    public function getMstShopItemId(): string;

    public function getTradeCount(): int;

    public function getTradeTotalCount(): int;

    public function getLastResetAt(): string;

    public function reset(CarbonImmutable $now): void;

    public function incrementTradeCount(): void;
}
