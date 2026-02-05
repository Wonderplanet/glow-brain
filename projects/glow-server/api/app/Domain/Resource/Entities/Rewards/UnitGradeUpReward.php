<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class UnitGradeUpReward extends BaseReward
{
    private string $mstUnitGradeUpRewardId;

    public function __construct(
        string $type,
        ?string $resourceId,
        int $amount,
        string $mstUnitGradeUpRewardId,
    ) {
        parent::__construct(
            $type,
            $resourceId,
            $amount,
            new LogTriggerDto(
                LogResourceTriggerSource::UNIT_GRADE_UP_REWARD->value,
                $mstUnitGradeUpRewardId,
            ),
        );

        $this->mstUnitGradeUpRewardId = $mstUnitGradeUpRewardId;
    }

    public function getMstUnitGradeUpRewardId(): string
    {
        return $this->mstUnitGradeUpRewardId;
    }
}
