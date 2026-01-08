<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceTriggerSource;

/**
 * スタミナ回復のログトリガー
 */
class StaminaRecoveryLogTrigger extends LogTrigger
{
    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            LogResourceTriggerSource::USER_STAMINA_RECOVERY_COST->value,
            '',
        );
    }
}
