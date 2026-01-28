<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

class PvpChallengeLogTrigger extends LogTrigger
{
    private string $sysPvpSeasonId;

    public function __construct(string $sysPvpSeasonId)
    {
        $this->sysPvpSeasonId = $sysPvpSeasonId;
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::PVP_CHALLENGE_COST->value,
            $this->sysPvpSeasonId,
        );
    }
}
