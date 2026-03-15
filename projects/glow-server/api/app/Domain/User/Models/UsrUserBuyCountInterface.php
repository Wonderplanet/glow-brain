<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrUserBuyCountInterface extends UsrModelInterface
{
    public function getDailyBuyStaminaAdCount(): int;

    public function getDailyBuyStaminaAdAt(): ?string;

    public function setDailyBuyStaminaAd(int $count, string $at): void;

    public function setDailyBuyStaminaAdCount(int $count): void;
}
