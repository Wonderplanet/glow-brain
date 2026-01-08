<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\LogTriggers;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Log\Models\Contracts\LogModelInterface;

class JoinLogTrigger extends LogTrigger
{
    private string $triggerSource;
    private string $triggerValue;

    public function __construct(LogModelInterface $logModel)
    {
        $this->triggerSource = $logModel->getLogTableName();
        $this->triggerValue = $logModel->getId();
    }

    public function getLogTriggerData(): LogTriggerDto
    {
        return new LogTriggerDto(
            $this->triggerSource,
            $this->triggerValue,
        );
    }
}
