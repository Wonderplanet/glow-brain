<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrTradePackInterface extends UsrModelInterface
{
    public function getMstPackId(): string;

    public function getDailyTradeCount(): int;

    public function getLastResetAt(): string;

    public function reset(CarbonImmutable $now): void;

    public function incrementTradeCount(): void;
}
