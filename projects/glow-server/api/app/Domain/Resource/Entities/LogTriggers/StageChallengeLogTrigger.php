<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class StageChallengeLogTrigger extends LogTrigger
{
    private string $mstStageId;
    private int $lapCount;

    public function __construct(string $mstStageId, int $lapCount)
    {
        $this->mstStageId = $mstStageId;
        $this->lapCount = $lapCount;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::STAGE_CHALLENGE_COST->value,
            $this->mstStageId,
            (string)$this->lapCount,
        );
    }
}
