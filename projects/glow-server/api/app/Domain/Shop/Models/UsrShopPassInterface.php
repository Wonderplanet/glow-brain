<?php

declare(strict_types=1);

namespace App\Domain\Shop\Models;

use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;
use Carbon\CarbonImmutable;

interface UsrShopPassInterface extends UsrModelInterface
{
    public function getMstShopPassId(): string;

    public function getDailyRewardReceivedCount(): int;

    public function getDailyLatestReceivedAt(): string;

    public function getStartAt(): string;

    public function getEndAt(): string;

    public function rewardReceived(CarbonImmutable $now): void;
}
