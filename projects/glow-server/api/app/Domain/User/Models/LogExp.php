<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Enums\LogResourceActionType;
use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property int $before_amount
 * @property int $after_amount
 * @property string $action_type
 * @property string $trigger_source
 * @property string $trigger_value
 * @property string $trigger_option
 */
class LogExp extends LogModel
{
    use HasFactory;

    public function setActionType(LogResourceActionType $actionType): void
    {
        $this->action_type = $actionType->value;
    }

    public function setBeforeAmount(int $beforeAmount): void
    {
        $this->before_amount = $beforeAmount;
    }

    public function setAfterAmount(int $afterAmount): void
    {
        $this->after_amount = $afterAmount;
    }

    public function setLogTriggerData(LogTriggerDto $logTriggerData): void
    {
        $this->trigger_source = $logTriggerData->getTriggerSource();
        $this->trigger_value = $logTriggerData->getTriggerValue();
        $this->trigger_option = $logTriggerData->getTriggerOption();
    }
}
