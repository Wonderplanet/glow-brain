<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Models;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $mst_emblem_id
 * @property int $amount
 * @property string $trigger_source
 * @property string $trigger_value
 * @property string $trigger_option
 */
class LogEmblem extends LogModel
{
    use HasFactory;

    public function setMstEmblemId(string $mstEmblemId): void
    {
        $this->mst_emblem_id = $mstEmblemId;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function setLogTriggerData(LogTriggerDto $logTriggerData): void
    {
        $this->trigger_source = $logTriggerData->getTriggerSource();
        $this->trigger_value = $logTriggerData->getTriggerValue();
        $this->trigger_option = $logTriggerData->getTriggerOption();
    }
}
