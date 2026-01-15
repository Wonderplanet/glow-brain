<?php

declare(strict_types=1);

namespace App\Domain\Item\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrItemTradeInterface extends UsrModelInterface
{
    public function getMstItemId(): string;

    public function getTradeAmount(): int;

    public function getResetTradeAmount(): int;

    public function getTradeAmountResetAt(): string;

    public function reset(CarbonImmutable $now): void;

    public function addTradeAmount(int $amount): void;
}
