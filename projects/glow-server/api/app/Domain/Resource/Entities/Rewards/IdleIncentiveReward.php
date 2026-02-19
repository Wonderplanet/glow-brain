<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class IdleIncentiveReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        IdleIncentiveExecMethod $idleIncentiveExecMethod,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::IDLE_INCENTIVE_REWARD->value,
                $idleIncentiveExecMethod->value,
            ),
        );
    }
}
