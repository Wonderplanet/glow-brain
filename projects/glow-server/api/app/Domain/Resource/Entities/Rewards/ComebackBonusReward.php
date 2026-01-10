<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class ComebackBonusReward extends BaseReward
{
    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        private string $mstComebackBonusScheduleId,
        private string $mstComebackBonusId,
        private int $loginDayCount,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::COMEBACK_BONUS_REWARD->value,
                $mstComebackBonusId,
            ),
        );
    }

    public function getMstComebackBonusScheduleId(): string
    {
        return $this->mstComebackBonusScheduleId;
    }

    public function getMstComebackBonusId(): string
    {
        return $this->mstComebackBonusId;
    }

    public function getLoginDayCount(): int
    {
        return $this->loginDayCount;
    }
}
