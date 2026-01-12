<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrExchangeLineupInterface extends UsrModelInterface
{
    public function getMstExchangeLineupId(): string;

    public function getMstExchangeId(): string;

    public function getTradeCount(): int;

    public function incrementTradeCount(int $count = 1): void;

    public function canTrade(?int $tradableCount, int $tradeCount = 1): bool;

    public function getResetAt(): string;

    public function resetTradeCount(CarbonImmutable $now): void;
}
