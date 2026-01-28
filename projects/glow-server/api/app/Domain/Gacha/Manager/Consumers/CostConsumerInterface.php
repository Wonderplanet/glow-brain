<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Manager\Consumers;

use App\Domain\Gacha\Models\ILogGachaAction;
use App\Domain\Resource\Entities\CurrencyTriggers\GachaTrigger;

interface CostConsumerInterface
{
    public function setConsumeResource(
        string $usrUserId,
        ?string $costId,
        int $costNum,
        int $platform,
        string $billingPlatform,
        bool $checkedAd,
        ?GachaTrigger $gachaTrigger
    ): void;

    public function execConsumeResource(ILogGachaAction $logGachaAction): void;
}
